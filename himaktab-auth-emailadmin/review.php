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

require('../../config.php');
require_once($CFG->libdir.'/authlib.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
require_capability('moodle/user:update', context_system::instance());

$data = required_param('data', PARAM_RAW);

$PAGE->set_url('/auth/emailadmin/review.php', ['data' => $data]);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('reviewaccount', 'auth_emailadmin'));

$dataelements = explode('/', $data, 2);
if (count($dataelements) != 2) {
    throw new moodle_exception('invalidrequest');
}
$usersecret = $dataelements[0];
$username   = $dataelements[1];

$user = get_complete_user_data('username', $username);
if (!$user) {
    throw new moodle_exception('cannotfinduser', '', '', s($username));
}
if ($user->secret !== $usersecret) {
    throw new moodle_exception('invalidconfirmdata');
}

$PAGE->set_heading(get_string('reviewaccount', 'auth_emailadmin'));

if ($user->confirmed) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string("alreadyconfirmed"), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reviewdetails', 'auth_emailadmin', fullname($user, true)));
echo $OUTPUT->box_start('generalbox user-details');
echo '<h3>' . s(get_string('accountdetails', 'local_signup')) . '</h3>';
echo '<ul>';
echo '<li><strong>' . get_string('username') . ':</strong> ' . s($user->username) . '</li>';
echo '<li><strong>' . get_string('firstname') . ':</strong> ' . s($user->firstname) . '</li>';
echo '<li><strong>' . get_string('lastname') . ':</strong> ' . s($user->lastname) . '</li>';
echo '<li><strong>' . get_string('email') . ':</strong> ' . s($user->email) . '</li>';
echo '</ul>';
$signupdata = $DB->get_record('local_signup_data', ['userid' => $user->id]);

if ($signupdata) {
    $fieldmap = [
        'dateofbirth'        => 'dateofbirth',
        'homeaddress'        => 'homeaddress',
        'parentname'         => 'parentname',
        'parentemail'        => 'parentemail',
        'phone2'             => 'phone2',
        'emergencycontactname' => 'emergencycontactname',
        'emergencyphone'     => 'emergencyphone',
        'healthinfo'         => 'healthinfo_details',
        'specialneeds'       => 'specialneeds_details',
        'desiredgrade'       => 'desiredgrade',
    ];

    echo '<h3>' . s(get_string('applicationdetails', 'local_signup')) . '</h3>';
    echo '<ul>';

    foreach ($fieldmap as $field => $stringkey) {
        if (!empty($signupdata->{$field})) {
            $value = $signupdata->{$field};
            $label = get_string($stringkey, 'local_signup');

            if ($field === 'dateofbirth' && is_numeric($value)) {
                $value = userdate($value, get_string('strftimedate', 'langconfig'));
            }
            
            if ($field === 'healthinfo' || $field === 'specialneeds') {
                echo '<li><strong>' . s($label) . ':</strong><br>' . format_text($value, FORMAT_PLAIN) . '</li>';
            } else {
                echo '<li><strong>' . s($label) . ':</strong> ' . s($value) . '</li>';
            }
        }
    }
    echo '</ul>';
} else {
    echo $OUTPUT->notification('Could not find custom signup data for this user.');
}

echo $OUTPUT->box_end();
$accepturl = new moodle_url('/auth/emailadmin/confirm.php', ['data' => $data]);
$denyurl = new moodle_url('/auth/emailadmin/deny.php', ['data' => $data]);
$messageurl = new moodle_url('/auth/emailadmin/message.php', ['data' => $data]);

echo '<div class="buttons text-center" style="margin-top: 20px;">';
echo $OUTPUT->single_button($accepturl, get_string('accept', 'auth_emailadmin'), 'post', ['class' => 'btn-primary btn-lg']);
echo $OUTPUT->single_button($denyurl, get_string('deny', 'auth_emailadmin'), 'post', ['class' => 'btn-danger btn-lg']);
echo $OUTPUT->single_button($messageurl, get_string('sendmessage', 'auth_emailadmin'), 'get', ['class' => 'btn-secondary btn-lg']);
echo '</div>';

echo $OUTPUT->footer();