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
 * Denies and deletes a self registered user.
 *
 * @package    auth_emailadmin
 * @copyright  2023 You
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir.'/authlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once(__DIR__ . '/classes/message.class.php'); 
require_login();
require_capability('moodle/user:delete', context_system::instance());

$data = required_param('data', PARAM_RAW); 
$PAGE->set_url('/auth/emailadmin/deny.php', ['data' => $data]);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('accountdenied', 'auth_emailadmin'));
$PAGE->set_heading(get_string('accountdenied', 'auth_emailadmin'));

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

if ($user->confirmed) {
    throw new moodle_exception('useralredyconfirmed', 'auth_emailadmin');
}

\auth\emailadmin\message::send_denial_email_user($user);

if (delete_user($user)) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('accountdeniedanddeleted', 'auth_emailadmin', fullname($user, true)), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();
} else {
    throw new moodle_exception('errordeletinguser', 'auth_emailadmin');
}