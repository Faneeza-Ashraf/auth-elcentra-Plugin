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


namespace auth\emailadmin;

use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

class message {

    /**
     * Helper function to get a user's language for emails.
     */
    public static function get_user_language($user) {
        global $USER, $COURSE, $SESSION;
        $lang_hack = new \stdClass();
        $lang_hack->forcelang = $user->lang;
        $lang_hack->lang = $user->lang;
        $hack_backup = ['USER' => false, 'COURSE' => false, 'SESSION' => false];
        foreach ($hack_backup as $hack_backup_key => $hack_backup_value) {
            $hack_backup[$hack_backup_key] = $GLOBALS[$hack_backup_key];
            $GLOBALS[$hack_backup_key] = $lang_hack;
        }
        $use_lang = current_language();
        foreach ($hack_backup as $hack_backup_key => $hack_backup_value) {
            $GLOBALS[$hack_backup_key] = $hack_backup_value;
        }
        return $use_lang;
    }

        /**
     * Sends the "Admission Approved" email.
     * It will include a payment link ONLY if a fee is required and the payment
     * system is available and configured correctly.
     *
     * @param object $user The user object.
     * @return bool Email success or failure.
     */
    public static function send_confirmation_email_user($user) {
        global $CFG, $DB, $OUTPUT;
        $site = get_site();
        $supportuser = \core_user::get_support_user();
        $stringmanager = get_string_manager();

        $data = new \stdClass();
        $data->firstname = fullname($user, true);
        $data->sitename = format_string($site->fullname);     
        $uploaddata = $user->secret . '/' . $user->id;
        $data->link = $CFG->wwwroot . '/auth/emailadmin/upload_documents.php?uploaddata=' . urlencode($uploaddata);
        $data->paymentlink = null;
        $data->haspayment = false;

        $signupdata = $DB->get_record('local_signup_data', ['userid' => $user->id]);

        if ($signupdata && !empty($signupdata->desiredgrade)) {
            $desiredgrade = trim($signupdata->desiredgrade);
            $program = $DB->get_record('enrol_programs_programs', ['fullname' => $desiredgrade]);
            if ($program) {
                try {
                    $payable = \core_payment\helper::get_payable(
                        'auth_emailadmin',
                        'program_enrolment',
                        $program->id,
                        $user
                    );
                    
                   if ($payable->get_amount() > 0) {
                        $params = [
                            'id'          => $payable->id,
                            'description' => 'Program Enrolment Fee',
                            'component'   => 'auth_emailadmin',
                            'paymentarea' => 'program_enrolment',
                            'itemid'      => $program->id
                        ];
                        $paymenturl = new moodle_url('/payment/gateway/stripe/pay.php', $params);

                        $data->paymentlink = $paymenturl->out(false);
                        $data->haspayment = true;
                    }

                } catch (\Exception $e) {
                    debugging("auth_emailadmin: Payment link could not be generated. Sending email without payment info. Reason: " . $e->getMessage());
                    $data->haspayment = false;
                }
            }
        }
        $use_lang = self::get_user_language($user);
        $subject = $stringmanager->get_string('auth_emailadminuserconfirmationsubject_payment', 'auth_emailadmin', format_string($site->fullname), $use_lang);
        $messagehtml = $OUTPUT->render_from_template('auth_emailadmin/email_confirmation_payment', $data);
        $messagetext = $stringmanager->get_string('auth_emailadminuserconfirmationbody_payment', 'auth_emailadmin', $data, $use_lang);

        return email_to_user($user, $supportuser, $subject, $messagetext, $messagehtml);
    }
    /**
     * Sends the denial email to the user.
     */
    public static function send_denial_email_user($user) {
        global $CFG;

        $site = get_site();
        $supportuser = \core_user::get_support_user();
        $stringmanager = get_string_manager();

        $data = new \stdClass();
        $data->firstname = fullname($user, true);
        $data->sitename = format_string($site->fullname);
        $data->admin = "Warm regards,<br>Admissions Office<br>HiMaktab";
        $data->adminemail = $supportuser->email;

        $use_lang = self::get_user_language($user);
        $subject = $stringmanager->get_string('auth_emailadmindenialsubject', 'auth_emailadmin', format_string($site->fullname), $use_lang);
        $message = $stringmanager->get_string('auth_emailadmindenialbody', 'auth_emailadmin', $data, $use_lang);
        $messagehtml = text_to_html($message, false, false, true);

        return email_to_user($user, $supportuser, $subject, $message, $messagehtml);
    }
}