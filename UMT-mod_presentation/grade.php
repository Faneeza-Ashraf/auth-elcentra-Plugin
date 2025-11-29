<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
/**
 * Grade capabilities for presentation module
 *
 * @package     mod_presentation
 * @copyright   2025 Endush Fairy <endush.fairy@paktaleem.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once(__DIR__ . '/classes/form/grading_form.php'); 

$cmid = optional_param('cmid', 0, PARAM_INT) ?: required_param('id', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

// Fetch all required records.
$cm = get_coursemodule_from_id('presentation', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$presentation = $DB->get_record('presentation', ['id' => $cm->instance], '*', MUST_EXIST);
$student = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

// Get the submission record.
$submission = $DB->get_record('presentation_submissions', ['presentationid' => $presentation->id, 'userid' => $student->id]);
if (!$submission) {
    // If getting here without a submission, create a placeholder so we can grade it anyway.
    $submission = new stdClass();
    $submission->presentationid = $presentation->id;
    $submission->userid = $student->id;
    $submission->timecreated = time();
    $submission->timemodified = time();
    $submission->id = $DB->insert_record('presentation_submissions', $submission);
    $submission = $DB->get_record('presentation_submissions', ['id' => $submission->id], '*', MUST_EXIST);
}

// Setup page and context.
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/presentation:viewsubmissions', $context);

$PAGE->set_url('/mod/presentation/grade.php', ['cmid' => $cm->id, 'userid' => $userid]);
$PAGE->set_title(format_string($presentation->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Pass the presentation settings to the form.
$mform = new \mod_presentation\form\grading_form(null, ['presentation' => $presentation]);

// Handle form submission.
if ($fromform = $mform->get_data()) {
    $recordtoupdate = new stdClass();
    $recordtoupdate->id = $fromform->id;
    $recordtoupdate->grade = $fromform->grade;
    $recordtoupdate->teachercomment = $fromform->teachercomment;
    $recordtoupdate->timemodified = time();

    $DB->update_record('presentation_submissions', $recordtoupdate);

    // Send grade to gradebook.
    $grades = new stdClass();
    $grades->userid = $student->id;
    $grades->rawgrade = $fromform->grade;

    presentation_grade_item_update($presentation, $grades);

    redirect(new moodle_url('/mod/presentation/view.php', ['id' => $cm->id]), get_string('gradesandcommentssaved', 'mod_presentation'));

} else if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/presentation/view.php', ['id' => $cm->id]));
}

// Pre-fill the form.
$submission->cmid = $cm->id;
$mform->set_data($submission);

// Page Output
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('gradesubmissionfor', 'mod_presentation', fullname($student)));

$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_presentation', 'submission_files', $submission->id, 'timemodified DESC', false);

echo $OUTPUT->box_start('generalbox', 'files');
echo '<h5>' . get_string('filesubmission', 'mod_presentation') . '</h5>';

if ($files) {
    $filefound = false;
    foreach ($files as $file) {
        if ($file->is_directory()) continue;
        $filefound = true;
        // This generates the link that uses presentation_pluginfile in lib.php
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        echo html_writer::link($fileurl, $file->get_filename(), ['target' => '_blank', 'class' => 'btn btn-secondary']);
        echo '<br><br>';
    }
    if (!$filefound) {
        echo get_string('no$presentationinstances', 'mod_presentation') . " (No files attached)";
    }
} else {
    echo "No files submitted.";
}
echo $OUTPUT->box_end();


echo $OUTPUT->box_start('generalbox', 'gradingform');

// Check if grading is actually enabled in the settings
if ($presentation->grade == 0) {
    echo $OUTPUT->notification('Grading is set to "None" for this activity. Please go to "Edit Settings" and set a Maximum Grade or Scale to enable grading.', 'warning');
}

//Updated the file section for teacher and student by Faneeza Muskan
$mform->display();
echo $OUTPUT->box_end();

echo $OUTPUT->footer();