<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines the scheduled task for checking user subscriptions.
 *
 * @package    auth
 * @subpackage emailadmin
 *@copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->libdir.'/accesslib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once('classes/message.class.php');
require_once(__DIR__ . '/classes/message.class.php');
class auth_plugin_emailadmin extends auth_plugin_base {

    public function __construct() {
        $this->authtype = 'emailadmin';
        $this->config = get_config('auth_'.$this->authtype);
    }

      public function user_login ($username, $password) {
        global $CFG, $DB;

        if ($user = $DB->get_record('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id])) {
            $validated = validate_internal_user_password($user, $password);
            if ($validated) {
                if (empty($user->confirmed)) {
                    $failurereason = AUTH_LOGIN_UNAUTHORISED;
                    $event = \core\event\user_login_failed::create(['userid' => $user->id,
                        'other' => ['username' => $user->username, 'reason' => $failurereason]]);
                    $event->trigger();
                    redirect(new moodle_url('/login/index.php'), get_string('auth_emailadminawaitingapproval', 'auth_emailadmin'));
                
                } else {
                    if ($paymentrecord = $DB->get_record('auth_emailadmin_payment', ['userid' => $user->id])) {
                        $is_pending = ($paymentrecord->status === 'pending');
                        $grace_period_active = (time() - $paymentrecord->pending_since < 600); 
                    }
                    return $validated;
                }
            }
        }
        return false;
    }

    public function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        return update_internal_user_password($user, $newpassword);
    }

    public function can_signup() {
        return true;
    }

    /**
     * Creates a new user account, saves custom data, and sends admin notification.
     * This function is called from your custom signup page.
     *
     * @param stdClass $data The raw data from the signup form.
     * @param bool $notify True if the user should be redirected to a success page.
     * @return bool|void
     * @throws moodle_exception
     */
    public function user_signup($data, $notify = true) {
        global $CFG, $DB;

        $user = new stdClass();
        $user->username     = $data->username;
        $user->password     = $data->password;
        $user->firstname    = $data->firstname;
        $user->lastname     = $data->lastname;
        $user->email        = $data->email;
        $user->auth         = $this->authtype;
        $user->confirmed    = 0; 
        $user->mnethostid   = $CFG->mnet_localhost_id;
        $user->lang         = current_language();
        $user->secret       = random_string(15);
        $user->timecreated  = time();
        $user->timemodified = $user->timecreated;
        $user->password     = hash_internal_user_password($user->password);

        $user->id = $DB->insert_record('user', $user);
        if (!$user->id) {
            throw new moodle_exception('errorcreatinguser', 'auth_emailadmin');
        }

        $signupdata = new stdClass();
        $signupdata->userid               = $user->id;
        $signupdata->dateofbirth          = !empty($data->dateofbirth) ? $data->dateofbirth : null;
        $signupdata->homeaddress          = !empty($data->homeaddress) ? $data->homeaddress : '';
        $signupdata->parentname           = !empty($data->parentname) ? $data->parentname : '';
        $signupdata->emergencycontactname = !empty($data->emergencycontactname) ? $data->emergencycontactname : '';
        $signupdata->emergencyphone       = !empty($data->emergencyphone) ? $data->emergencyphone : '';
        $signupdata->parentemail          = !empty($data->parentemail) ? $data->parentemail : '';
        $signupdata->phone2               = !empty($data->phone2) ? $data->phone2 : '';
        $signupdata->healthinfo           = !empty($data->healthinfo) ? $data->healthinfo : '';
        $signupdata->specialneeds         = !empty($data->specialneeds) ? $data->specialneeds : '';
        $signupdata->desiredgrade         = !empty($data->desiredgrade) ? $data->desiredgrade : '';
        $signupdata->timecreated          = time();
        $signupdata->timemodified         = $signupdata->timecreated;
        $DB->insert_record('local_signup_data', $signupdata);
        $completeuser = get_complete_user_data('id', $user->id);
        $usercontext = context_user::instance($completeuser->id);
        $event = \core\event\user_created::create([
            'objectid' => $completeuser->id,
            'relateduserid' => $completeuser->id,
            'context' => $usercontext
        ]);
        $event->trigger();

        if (!$this->send_confirmation_email_support($completeuser)) {
            throw new moodle_exception('auth_emailadminnoemail', 'auth_emailadmin');
        }
        if ($notify) {
            global $PAGE, $OUTPUT;
            $emailconfirm = get_string('emailconfirm');
            $PAGE->navbar->add($emailconfirm);
            $PAGE->set_title($emailconfirm);
            $site = get_site();
            $PAGE->set_heading($site->fullname);
            echo $OUTPUT->header();
            notice(get_string('auth_emailadminconfirmsent', 'auth_emailadmin'), new moodle_url('/login/index.php'));
        } else {
            return true;
        }
    }

    public function can_confirm() {
        return true;
    }

    public function user_confirm($username, $confirmsecret) {
        global $DB;
        $user = get_complete_user_data('username', $username);
        if (!empty($user)) {
            require_login();
            require_capability('moodle/user:update', context_system::instance());
            if ($user->confirmed) {
                return AUTH_CONFIRM_ALREADY;
            } else if ($user->auth != $this->authtype) {
                return AUTH_CONFIRM_ERROR;
            } else if ($user->secret == $confirmsecret) {
                $DB->set_field("user", "confirmed", 1, array("id" => $user->id));
                if ($user->firstaccess == 0) {
                    $DB->set_field("user", "firstaccess", time(), array("id" => $user->id));
                }
                $confirmeduser = get_complete_user_data('id', $user->id);
                if ($confirmeduser) {
                    \auth\emailadmin\message::send_confirmation_email_user($confirmeduser);
                    if (!$this->create_and_notify_parent($confirmeduser)) {
                        debugging("Parent creation/notification failed. Check other error messages on this page.", DEBUG_DEVELOPER);
                    }
                    if (!$this->enroll_student_in_program($confirmeduser)) {
                        debugging("Program enrollment failed. Check other error messages on this page.", DEBUG_DEVELOPER);
                    }
                }
                return AUTH_CONFIRM_OK;
            }
        }
        return AUTH_CONFIRM_ERROR;
    }

    public function send_confirmation_email_support($user) {
        global $CFG, $DB;

        $config = $this->config;
        $site = get_site();
        $supportuser = core_user::get_support_user();
        
        $data = new stdClass();
        $data->email = $user->email;
        $username = urlencode($user->username);
        $username = str_replace('.', '%2E', $username);
        $data->link = $CFG->wwwroot .'/auth/emailadmin/review.php?data='. $user->secret .'/'. $username;

        $data->grade = 'N/A';
        if ($signupdata = $DB->get_record('local_signup_data', ['userid' => $user->id])) {
            if (!empty($signupdata->desiredgrade)) {
                $data->grade = $signupdata->desiredgrade;
            }
        }

        $user->mailformat = 1;
        $admins = get_admins();
        $recipientlist = [];
        if (!isset($config->notif_strategy)) {
            $config->notif_strategy = -1;
        }
        $strategy = (int)$config->notif_strategy;
        if ($strategy === -1) {
            if (!empty($admins)) {
                $recipientlist[reset($admins)->id] = reset($admins);
            }
        } else if ($strategy === -2) {
            foreach ($admins as $admin) {
                $recipientlist[$admin->id] = $admin;
            }
        } else if ($strategy === -3) {
            $allrecipients = array_merge($admins, get_users_by_capability(context_system::instance(), 'moodle/user:update'));
            foreach ($allrecipients as $recipient) {
                $recipientlist[$recipient->id] = $recipient;
            }
        } else if ($strategy >= 0) {
            if ($specificadmin = core_user::get_user($strategy)) {
                $recipientlist[$specificadmin->id] = $specificadmin;
            }
        }
        $sentok = false;
        if (empty($recipientlist)) {
            return false;
        }
        foreach ($recipientlist as $recipient) {
            $use_lang = \auth\emailadmin\message::get_user_language($recipient);
            $subject = get_string_manager()->get_string('auth_emailadminconfirmationsubject', 'auth_emailadmin', null, $use_lang);
            $message = get_string_manager()->get_string('auth_emailadminconfirmation', 'auth_emailadmin', $data, $use_lang);
            $messagehtml = text_to_html($message, false, false, true);
            if (email_to_user($recipient, $supportuser, $subject, $message, $messagehtml)) {
                $sentok = true;
            }
        }
        return $sentok;
    }

   /**
 * Creates a parent user account, assigns the parent role, and sends a notification email
 * using strings defined in the language file.
 *
 * @param stdClass $studentuser The student user object.
 * @return bool True if the email was sent successfully, false otherwise.
 */
public function create_and_notify_parent($studentuser) {
    global $CFG, $DB;
    $signupdata = $DB->get_record('local_signup_data', ['userid' => $studentuser->id]);
    if (!$signupdata) {
        debugging("Parent creation failed: No custom signup data found for student ID {$studentuser->id}.", DEBUG_DEVELOPER);
        return false;
    }

    if (empty($signupdata->parentemail) || !validate_email($signupdata->parentemail)) {
        debugging("Parent creation failed: The 'parentemail' field is missing or invalid for student ID {$studentuser->id}.", DEBUG_DEVELOPER);
        return false;
    }
    $parentemail = $signupdata->parentemail;
    if ($DB->record_exists('user', ['email' => $parentemail, 'deleted' => 0])) {
        return true;
    }
    $parentname = !empty($signupdata->parentname) ? $signupdata->parentname : 'Parent of ' . fullname($studentuser, true);
    $parent = new stdClass();
    $parent->auth = 'manual';
    $parent->confirmed = 1;
    $parent->mnethostid = $CFG->mnet_localhost_id;
    $parent->email = $parentemail;
    $usernamebase = preg_replace('/@.*/', '', $parentemail);
    $usernamebase = preg_replace('/[^a-zA-Z0-9]/', '', $usernamebase);
    if (empty($usernamebase)) {
        $usernamebase = 'parent';
    }
    $username_to_try = $usernamebase;
    $counter = 2;
    while ($DB->record_exists('user', ['username' => $username_to_try, 'mnethostid' => $CFG->mnet_localhost_id])) {
        $username_to_try = $usernamebase . $counter++;
    }
    $parent->username = $username_to_try;

    $nameparts = explode(' ', $parentname, 2);
    $parent->firstname = $nameparts[0];
    $parent->lastname = (isset($nameparts[1]) && !empty(trim($nameparts[1]))) ? $nameparts[1] : '.';
    $temppassword = generate_password(8, 1, 1, 1, 1);
    $parent->password = $temppassword;
    $parent->passwordneedschange = 1;

    $parent->id = user_create_user($parent, true, false);

    if (!$parent->id) {
        debugging("Parent creation failed: user_create_user() returned a falsey ID.", DEBUG_DEVELOPER);
        return false;
    }
    $createdparent = get_complete_user_data('id', $parent->id);
    if (!$createdparent) {
        debugging("Parent creation failed: get_complete_user_data() could not retrieve the new parent user.", DEBUG_DEVELOPER);
        return false;
    }
    $parentrole = $DB->get_record('role', ['shortname' => 'parent']);
    if ($parentrole) {
        $studentcontext = context_user::instance($studentuser->id);
        role_assign($parentrole->id, $createdparent->id, $studentcontext->id);
    } else {
        debugging("Parent role assignment failed: Role with shortname 'parent' not found.", DEBUG_DEVELOPER);
    }
    $emaildata = new stdClass();
    $emaildata->parentname = fullname($createdparent, true);
    $emaildata->studentname = fullname($studentuser, true);
    $emaildata->username = $createdparent->username;
    $emaildata->password = $temppassword; 
    $emaildata->link = new moodle_url('/login/index.php');
    $emaildata->grade = !empty($signupdata->desiredgrade) ? $signupdata->desiredgrade : 'N/A';
    $emaildata->schoolname = 'HiMaktab';
    $emaildata->office = 'Admissions Office';
    $emaildata->startdate = userdate($studentuser->timecreated, '%B %d, %Y');

    $supportuser = core_user::get_support_user();
    $subject = get_string('parent_notification_subject', 'auth_emailadmin', $emaildata);
    $messagehtml = get_string('parent_notification_html', 'auth_emailadmin', $emaildata);
    $messagetext = html_to_text($messagehtml);
    $sent = email_to_user($createdparent, $supportuser, $subject, $messagetext, $messagehtml);
    if (!$sent) {
        debugging("Parent user was created, but notification FAILED. Check Moodle's Outgoing Mail Configuration (SMTP).", DEBUG_DEVELOPER);
    }

    return $sent;
}
    private function enroll_student_in_program($studentuser) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/enrol/programs/lib.php');

        $programname = null;
        if ($signupdata = $DB->get_record('local_signup_data', ['userid' => $studentuser->id])) {
            if (!empty($signupdata->desiredgrade)) {
                $programname = trim($signupdata->desiredgrade);
            }
        }

        if (is_null($programname)) {
            debugging("Program enrollment failed: Could not find desired grade for user ID {$studentuser->id}.", DEBUG_DEVELOPER);
            return false;
        }

        $program = $DB->get_record('enrol_programs_programs', ['fullname' => $programname], '*', IGNORE_MISSING);
        if (!$program) {
            debugging("Program enrollment failed: Program '{$programname}' does not exist.", DEBUG_DEVELOPER);
            return false;
        }

        if ($DB->record_exists('enrol_programs_allocations', ['programid' => $program->id, 'userid' => $studentuser->id])) {
            return true;
        }

        $sourcetype = \enrol_programs\local\source\manual::get_type();
        $source = $DB->get_record('enrol_programs_sources', ['programid' => $program->id, 'type' => $sourcetype]);
        if (!$source) {
            $source = new \stdClass();
            $source->programid = $program->id;
            $source->type = $sourcetype;
            $source->datajson = '{}';
            $source->id = $DB->insert_record('enrol_programs_sources', $source);
        }

        $newallocation = new \stdClass();
        $newallocation->programid       = $program->id;
        $newallocation->userid          = $studentuser->id;
        $newallocation->sourceid        = $source->id;
        $newallocation->archived        = 0;
        $newallocation->timeallocated   = time();
        $newallocation->timestart       = \enrol_programs\local\allocation::get_default_timestart($program, $newallocation->timeallocated);
        $newallocation->timedue         = \enrol_programs\local\allocation::get_default_timedue($program, $newallocation->timeallocated, $newallocation->timestart);
        $newallocation->timeend         = null; 
        $newallocation->timecreated     = $newallocation->timeallocated;
        $newallocation->id = $DB->insert_record('enrol_programs_allocations', $newallocation);

        if ($newallocation->id) {
            \enrol_programs\local\allocation::fix_user_enrolments($program->id, $studentuser->id);
            \enrol_programs\local\allocation::make_snapshot($newallocation->id, 'user_confirmed');
            return true;
        }

        return false;
    }

    public function is_internal() { return true; }
    public function can_change_password() { return true; }
    public function change_password_url() { return null; }
    public function can_reset_password() { return true; }
    public function config_form($config, $err, $user_fields) { include("config.html"); }
    public function is_captcha_enabled() { return get_config("auth_{$this->authtype}", 'recaptcha'); }
}