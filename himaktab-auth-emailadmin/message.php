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
require_once(__DIR__ . '/message_form.php');
require_once($CFG->libdir.'/authlib.php');

require_login();
require_capability('moodle/user:update', context_system::instance());

$datafromurl = optional_param('data', null, PARAM_RAW);
if ($datafromurl) {
    $SESSION->auth_emailadmin_messagedata = $datafromurl;
    redirect(new moodle_url('/auth/emailadmin/message.php'));
}

if (empty($SESSION->auth_emailadmin_messagedata)) {
    throw new moodle_exception('invalidrequest');
}
$data = $SESSION->auth_emailadmin_messagedata;
$PAGE->set_url('/auth/emailadmin/message.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('sendmessage', 'auth_emailadmin'));
$dataelements = explode('/', $data, 2);
if (count($dataelements) != 2) {
    unset($SESSION->auth_emailadmin_messagedata); 
    throw new moodle_exception('invalidrequest');
}
$usersecret = $dataelements[0];
$username   = $dataelements[1];
$touser = get_complete_user_data('username', $username);
if (!$touser || $touser->secret !== $usersecret) {
    unset($SESSION->auth_emailadmin_messagedata); 
    throw new moodle_exception('invalidrequest');
}

$PAGE->set_heading(get_string('sendmessagetouser', 'auth_emailadmin', fullname($touser, true)));
$mform = new auth_emailadmin_message_form();
$reviewurl = new moodle_url('/auth/emailadmin/review.php', ['data' => $data]);

if ($mform->is_cancelled()) {
    unset($SESSION->auth_emailadmin_messagedata); 
    redirect($reviewurl);
}

if ($fromform = $mform->get_data()) {
    $subject = $fromform->subject;
    $message = $fromform->messagebody['text'];
    $fromuser = $USER;

    if (email_to_user($touser, $fromuser, $subject, $message, $message)) {
        unset($SESSION->auth_emailadmin_messagedata); 

        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('messagesent', 'auth_emailadmin'), 'notifysuccess');
        echo $OUTPUT->continue_button($reviewurl);
        echo $OUTPUT->footer();
        exit;
    } else {
        throw new moodle_exception('erroremail', 'error');
    }

} else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}