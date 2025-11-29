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
 * @package    local    
 * @subpackage signup
 *@copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->libdir . '/authlib.php');
require_once('classes/form/signup_form.php');

global $PAGE, $OUTPUT, $SITE, $CFG;
if (empty($CFG->registerauth) || !is_enabled_auth($CFG->registerauth)) {
     throw new moodle_exception('registerdisabled', 'auth');
}
$authplugin = get_auth_plugin($CFG->registerauth);
if (!$authplugin->can_signup()) {
    throw new moodle_exception('registerdisabled', 'auth'); print_error('registerdisabled', 'auth');
}

$PAGE->set_url('/local/signup/signup.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login'); 
$PAGE->set_title(get_string('signuppage', 'local_signup'));
$PAGE->set_heading($SITE->fullname);
$mform = new local_signup_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/'));
} else if ($data = $mform->get_data()) {
    $authplugin->user_signup($data, true);

} else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}