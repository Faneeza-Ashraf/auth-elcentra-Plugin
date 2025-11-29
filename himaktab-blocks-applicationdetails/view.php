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
 * @package    block
 * @subpackage applicationdetails
 *@copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT); // User ID

global $DB, $PAGE, $OUTPUT;

$user = $DB->get_record('user', ['id' => $id], '*', MUST_EXIST);
$context = context_user::instance($user->id);

require_login();
require_capability('moodle/user:viewdetails', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/blocks/applicationdetails/view.php', ['id' => $user->id]));
$PAGE->set_title(fullname($user) . ' - ' . get_string('applicationdetails', 'block_applicationdetails'));
$PAGE->set_heading(fullname($user));
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('applicationdetails', 'block_applicationdetails'));
$signupdata = $DB->get_record('local_signup_data', ['userid' => $user->id]);

if ($signupdata) {
    $fields_to_display = [
        'dateofbirth', 'homeaddress', 'parentname', 'parentemail', 'emergencycontactname',
        'emergencyphone', 'phone2', 'desiredgrade', 'healthinfo', 'specialneeds',
        'academichistory', 'timecreated', 'timemodified'
    ];

    echo html_writer::start_tag('div', ['class' => 'user-profile-data']);
    echo $OUTPUT->box_start('generalbox'); 

    foreach ($fields_to_display as $field) {
        if (isset($signupdata->$field) && $signupdata->$field !== '' && $signupdata->$field !== null) {
            $label = get_string($field, 'block_applicationdetails');
            $value = $signupdata->$field;
            switch ($field) {
                case 'dateofbirth':
                case 'timecreated':
                case 'timemodified':
                    $value = userdate($value, get_string('strftimedate', 'langconfig'));
                    break;
                default:
                    $value = s($value);
                    break;
            }

            echo html_writer::div('<strong>' . $label . ':</strong> ' . $value, 'profile-field');
        }
    }
    echo $OUTPUT->box_end();
    echo html_writer::end_tag('div');

} else {
    echo $OUTPUT->notification('No signup data found for this user.');
}

echo $OUTPUT->heading(get_string('documents_heading', 'block_applicationdetails'), 3);

$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'auth_emailadmin', 'user_documents', 0, 'sortorder', false);

if (!empty($files)) {
    $listitems = [];
    foreach ($files as $file) {
        if ($file->is_directory() || $file->get_filename() === '.') {
            continue;
        }
        $fileurl = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            'block_applicationdetails', 
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        );
        $listitems[] = html_writer::link($fileurl, $file->get_filename(), ['target' => '_blank']);
    }

    if (!empty($listitems)) {
        echo html_writer::alist($listitems);
    } else {
        echo $OUTPUT->notification('No documents were uploaded.');
    }

} else {
    echo $OUTPUT->notification('No documents were uploaded.');
}

echo $OUTPUT->footer();