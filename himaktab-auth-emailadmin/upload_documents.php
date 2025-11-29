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

define('NO_UPGRADE_CHECK', true);
require('../../config.php');
require_once($CFG->libdir.'/authlib.php');

if (isloggedin() && !isguestuser()) {
    redirect(new moodle_url('/'));
}

$uploaddata = required_param('uploaddata', PARAM_RAW);
$dataelements = explode('/', $uploaddata, 2);
if (count($dataelements) != 2) { throw new moodle_exception('invalidrequest'); }
$usersecret = $dataelements[0];
$userid     = (int)$dataelements[1];
if (empty($userid)) { throw new moodle_exception('invalidrequest'); }
$user = get_complete_user_data('id', $userid);
if (!$user || $user->secret !== $usersecret) { throw new moodle_exception('invalidconfirmdata'); }

$pageurl = new moodle_url('/auth/emailadmin/upload_documents.php', ['uploaddata' => $uploaddata]);
$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('uploaddocuments', 'auth_emailadmin'));
$PAGE->set_heading(get_string('uploaddocuments_heading', 'auth_emailadmin', fullname($user, true)));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (required_param('uploaddata', PARAM_RAW) !== $uploaddata) { throw new moodle_exception('invalidrequest'); }

    $usercontext = context_user::instance($user->id);
    $fs = get_file_storage();
    $fs->delete_area_files($usercontext->id, 'auth_emailadmin', 'user_documents', 0);
    $fileuploaded = false;
    foreach (['userfile1', 'userfile2', 'userfile3'] as $inputname) {
        if (!empty($_FILES[$inputname]['name']) && is_uploaded_file($_FILES[$inputname]['tmp_name'])) {
            $fileinfo = new \stdClass();
            $fileinfo->contextid = $usercontext->id;
            $fileinfo->component = 'auth_emailadmin';
            $fileinfo->filearea  = 'user_documents';
            $fileinfo->itemid    = 0;
            $fileinfo->filepath  = '/';
            $fileinfo->filename  = $_FILES[$inputname]['name'];
            if ($fs->create_file_from_pathname($fileinfo, $_FILES[$inputname]['tmp_name'])) {
                $fileuploaded = true;
            }
        }
    }
    if (!$fileuploaded) { throw new moodle_exception('uploaderror', 'auth_emailadmin'); }

    $signupdata = $DB->get_record('local_signup_data', ['userid' => $user->id]);
    if ($signupdata && !empty($signupdata->desiredgrade)) {
        $program = $DB->get_record('enrol_programs_programs', ['fullname' => $signupdata->desiredgrade]);
        if ($program) {
            $payable = \core_payment\helper::get_payable('auth_emailadmin', 'program_enrolment', $program->id, $user);
            if ($payable && $payable->get_amount() > 0) {
                $paymentrecord = new \stdClass();
                $paymentrecord->userid = $user->id;
                $paymentrecord->status = 'pending';
                $paymentrecord->pending_since = time(); 
                $paymentrecord->timecreated = time();
                $paymentrecord->timemodified = time();
                if ($existing = $DB->get_record('auth_emailadmin_payment', ['userid' => $user->id])) {
                    $paymentrecord->id = $existing->id;
                    $DB->update_record('auth_emailadmin_payment', $paymentrecord);
                } else {
                    $DB->insert_record('auth_emailadmin_payment', $paymentrecord);
                
                }
                
                $payableid = method_exists($payable, 'get_id') ? $payable->get_id() : ($payable->id ?? null);
                if ($payableid) {
                    $params = [
                        'id'          => $payableid,
                        'component'   => 'auth_emailadmin',
                        'paymentarea' => 'program_enrolment',
                        'itemid'      => $program->id
                    ];
                    $paymenturl = new moodle_url('/payment/gateway/stripe/pay.php', $params);
                    redirect($paymenturl);
                }
            }
        }
    }
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('uploadsuccess', 'auth_emailadmin'), 'notifysuccess');
    $loginurl = new moodle_url('/login/index.php');
    echo '<div class="text-center" style="margin-top: 20px;">';
    echo $OUTPUT->single_button($loginurl, get_string('login'), 'get', ['class' => 'btn-primary btn-lg']);
    echo '</div>';
    echo $OUTPUT->footer();
    exit();
}

echo $OUTPUT->header();
echo $OUTPUT->notification(get_string('uploaddocuments_intro_multi', 'auth_emailadmin', 3), 'notifysuccess');
?>
<form action="<?php echo $pageurl->out(false); ?>" method="post" enctype="multipart/form-data" id="upload-documents-form" class="mform" style="margin-top: 30px;">
    <input type="hidden" name="uploaddata" value="<?php echo s($uploaddata); ?>">
    <div class="form-group row fitem required">
        <div class="col-md-3 text-md-right"><label for="userfile1"><?php echo get_string('document1', 'auth_emailadmin') . ' (' . get_string('required') . ')'; ?></label></div>
        <div class="col-md-9"><input type="file" name="userfile1" id="userfile1" class="form-control" required></div>
    </div>
    <div class="form-group row fitem">
        <div class="col-md-3 text-md-right"><label for="userfile2"><?php echo get_string('document2', 'auth_emailadmin') . ' (' . get_string('optional') . ')'; ?></label></div>
        <div class="col-md-9"><input type="file" name="userfile2" id="userfile2" class="form-control"></div>
    </div>
    <div class="form-group row fitem">
        <div class="col-md-3 text-md-right"><label for="userfile3"><?php echo get_string('document3', 'auth_emailadmin') . ' (' . get_string('optional') . ')'; ?></label></div>
        <div class="col-md-9"><input type="file" name="userfile3" id="userfile3" class="form-control"></div>
    </div>
    <div class="form-group row fitem">
        <div class="col-md-3"></div>
        <div class="col-md-9"><button type="submit" class="btn btn-primary"><?php echo get_string('submit_documents', 'auth_emailadmin'); ?></button></div>
    </div>
</form>
<?php
echo $OUTPUT->footer();