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
 * Upload page for the Course Outline block.
 *
 * @package    block_courseoutline
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/filelib.php');

use block_courseoutline\form\upload_form;

// Get parameters
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

// Verify course exists
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

// Set up page
require_login($course);
$context = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_url('/blocks/courseoutline/upload.php', ['courseid' => $courseid, 'blockid' => $blockid]);
$PAGE->set_title(get_string('upload_outline', 'block_courseoutline'));
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('upload_outline', 'block_courseoutline'));

// Check capabilities
require_capability('block/courseoutline:upload', $context);

// Check if outline already exists
$existing = $DB->get_record('block_courseoutline', ['courseid' => $courseid]);
if ($existing) {
   // First, ensure the user has the basic permission to update.
require_capability('block/courseoutline:update', $context);

// --- Start of new server-side enforcement ---
$canoverride = has_capability('block/courseoutline:override', $context);

// Calculate the deadline (1 week after course start date).
$deadline = $course->startdate + (7 * DAYSECS);
$now = time();

// If the deadline has passed and the user does NOT have override permission, block access.
if ($now > $deadline && !$canoverride) {
    print_error('updateperiodexpired', 'block_courseoutline', new moodle_url('/course/view.php', ['id' => $courseid]));
}
// --- End of new server-side enforcement ---
} else {
    require_capability('block/courseoutline:upload', $context);
}

// Create form
$form = new upload_form(null, ['courseid' => $courseid, 'blockid' => $blockid, 'isupdating' => !empty($existing)]);
if ($existing) {
    $form->set_data($existing);
}
$form->set_data(['courseid' => $courseid, 'blockid' => $blockid]);

// Handle form submission
if ($form->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} else if ($data = $form->get_data()) {
    $record = new stdClass();
    $record->courseid = $data->courseid;
    $record->teacherid = $USER->id;
    $record->totalquizzes = $data->totalquizzes;
    $record->totalassignments = $data->totalassignments;
    $record->totalpresentations = $data->totalpresentations;
    $record->totalworkshops = $data->totalworkshops;
    $record->timeuploaded = time();

    $existing = $DB->get_record('block_courseoutline', ['courseid' => $data->courseid]);

    // Check if a new file has been uploaded.
    $draftid = $data->outline_file;
    if ($draftid) {
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftid, 'id DESC', false);

        if (!empty($files)) {
            $file = reset($files);

            // Delete old file if it exists.
            if ($existing && $existing->fileid) {
                $oldfile = $fs->get_file_by_id($existing->fileid);
                if ($oldfile) {
                    $oldfile->delete();
                }
            }

            $filerecord = [
                'contextid' => $context->id, 'component' => 'block_courseoutline',
                'filearea' => 'outline', 'itemid' => $data->courseid,
                'filepath' => '/', 'filename' => $file->get_filename(),
                'userid' => $USER->id
            ];

            $storedfile = $fs->create_file_from_storedfile($filerecord, $file);
            $record->fileid = $storedfile->get_id();
            $fs->delete_area_files($usercontext->id, 'user', 'draft', $draftid);
        }
    }

    if ($existing) {
        // Update existing record.
        $record->id = $existing->id;
        if (!isset($record->fileid) && isset($existing->fileid)) {
            $record->fileid = $existing->fileid; // Keep old fileid if no new file is uploaded.
        }
        $DB->update_record('block_courseoutline', $record);
        redirect(new moodle_url('/course/view.php', ['id' => $courseid]),
            get_string('update_success', 'block_courseoutline'),
            null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        // Insert new record.
        if (!isset($record->fileid)) {
             redirect($PAGE->url, get_string('upload_error_file_required', 'block_courseoutline'),
                null, \core\output\notification::NOTIFY_ERROR);
        }
        $DB->insert_record('block_courseoutline', $record);
        redirect(new moodle_url('/course/view.php', ['id' => $courseid]),
            get_string('upload_success', 'block_courseoutline'),
            null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

// Output page
echo $OUTPUT->header();

// Prepare template data
$renderer = $PAGE->get_renderer('block_courseoutline');
$templatedata = new stdClass();
$templatedata->course_name = $course->fullname;
$templatedata->back_url = new moodle_url('/course/view.php', ['id' => $courseid]);
$templatedata->formhtml = $form->render();

echo $renderer->render_upload_page($templatedata);

echo $OUTPUT->footer();

