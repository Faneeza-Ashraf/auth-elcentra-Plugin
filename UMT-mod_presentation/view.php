<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
/**
* Prints an instance of mod_presentation.
*
* @package mod_presentation
* @copyright 2025 Endush Fairy <endush.fairy@paktaleem.net>
* @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

$id = required_param('id', PARAM_INT); // Course module ID.

$cm = get_coursemodule_from_id('presentation', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$presentation = $DB->get_record('presentation', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Trigger the course module viewed event.
$event = \mod_presentation\event\course_module_viewed::create([
    'objectid' => $presentation->id,
    'context' => $context,
]);
$event->trigger();

//  LOGIC BLOCK (Submission Form Handling) - Updated by Faneeza Muskan
$process_submission_form = false;
$mform = null;

if (!has_capability('mod/presentation:viewsubmissions', $context) && has_capability('mod/presentation:submit', $context)) {
    
    $submission = presentation_get_user_submission($presentation->id, $USER->id);

    // Only init form if not already submitted
    if (!$submission) {
        $formactionurl = new moodle_url('/mod/presentation/view.php', ['id' => $id]);
        $submissionrecord = new stdClass();
        $draftitemid = file_get_submitted_draft_itemid('submission_files');
        file_prepare_draft_area($draftitemid, $context->id, 'mod_presentation', 'submission_files', null);
        $submissionrecord->submission_files = $draftitemid;

        $fileoptions = [ 'subdirs' => 0, 'maxfiles' => $presentation->maxfiles ?? 1, 'maxbytes' => $presentation->maxsize ?? 0 ];
        $mform = new \mod_presentation\form\submission_form($formactionurl, ['fileoptions' => $fileoptions]);
        $mform->set_data($submissionrecord);

        if ($mform->is_cancelled()) {
            redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
            exit();
        } else if ($fromform = $mform->get_data()) {
            // Process Submission
            $newsubmission = new \stdClass();
            $newsubmission->presentationid = $presentation->id;
            $newsubmission->userid = $USER->id;
            $newsubmission->timecreated = time();
            $newsubmission->timemodified = time();
            $newsubmission->id = $DB->insert_record('presentation_submissions', $newsubmission);
            
            file_save_draft_area_files( $fromform->submission_files, $context->id, 'mod_presentation', 'submission_files', $newsubmission->id, $fileoptions );
            
            // Email notification
            $teachers = get_users_by_capability($context, 'mod/presentation:viewsubmissions', 'u.*');
            if (!empty($teachers)) {
                $submissionurl = new moodle_url('/mod/presentation/view.php', ['id' => $cm->id]);
                foreach ($teachers as $teacher) {
                    $data = new \stdClass();
                    $data->teacher = fullname($teacher);
                    $data->student = fullname($USER);
                    $data->course = format_string($course->fullname, true, ['context' => $context]);
                    $data->presentation = format_string($presentation->name, true, ['context' => $context]);
                    $data->link = $submissionurl->out(false);

                    $subject = get_string('emailsubject', 'mod_presentation', $data);
                    $bodyhtml = get_string('emailbody', 'mod_presentation', $data);
                    $bodytext = html_to_text($bodyhtml);

                    email_to_user($teacher, $USER, $subject, $bodytext, $bodyhtml);
                }
            }
            
            redirect(new moodle_url('/mod/presentation/view.php', ['id' => $cm->id]));
            exit();
        }
        $process_submission_form = true;
    }
}


// Helper function to format grade with Pass/Fail badge
$get_grade_html = function($grade, $gradepass) {
    if ($grade === null || $grade === '') {
        return '-';
    }
    
    $html = format_float($grade, 2);
    
    // Only show badges if a passing grade is defined (greater than 0)
    if ($gradepass > 0.0001) { 
        // Use a tiny epsilon for float comparison safety
        if ($grade >= ($gradepass - 0.00001)) { 
            // PASSED
            $html .= ' <span class="badge badge-success bg-success text-white">' . get_string('pass', 'grades') . '</span>';
        } else {
            // FAILED
            $html .= ' <span class="badge badge-danger bg-danger text-white">' . get_string('fail', 'grades') . '</span>';
        }
    }
    return $html;
};

// Setup the page.
$PAGE->set_url('/mod/presentation/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($presentation->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Start page output.
echo $OUTPUT->header();
echo $OUTPUT->box(format_module_intro('presentation', $presentation, $cm->id));

// Teacher View Logic
if (has_capability('mod/presentation:viewsubmissions', $context)) {
    echo $OUTPUT->heading(get_string('viewsubmissions', 'mod_presentation'));

    $userfieldsapi = \core_user\fields::for_name();
    $userfields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

    $sql = "SELECT ps.*, $userfields
              FROM {presentation_submissions} ps
              JOIN {user} u ON ps.userid = u.id
             WHERE ps.presentationid = :presentationid
          ORDER BY u.lastname, u.firstname";
    $submissions = $DB->get_records_sql($sql, ['presentationid' => $presentation->id]);

    if (empty($submissions)) {
        echo $OUTPUT->notification(get_string('nosubmissionsfound', 'mod_presentation'));
    } else {
        $table = new html_table();
        $table->attributes['class'] = 'generaltable';
        $table->head = [
            get_string('student', 'mod_presentation'),
            get_string('lastmodified', 'mod_presentation'),
            get_string('filesubmission', 'mod_presentation'),
            get_string('grade', 'mod_presentation'),
            get_string('comment', 'mod_presentation'),
            get_string('actions', 'mod_presentation')
        ];

        $fs = get_file_storage();

        foreach ($submissions as $submission) {
            $files = $fs->get_area_files($context->id, 'mod_presentation', 'submission_files', $submission->id, 'timemodified DESC', false);
            $filelist = '';
            foreach ($files as $file) {
                $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
                $filelist .= html_writer::link($fileurl, $file->get_filename()) . '<br />';
            }

           $gradeurl = new moodle_url('/mod/presentation/grade.php', ['cmid' => $cm->id, 'userid' => $submission->userid]);
           $gradebutton = $OUTPUT->action_link($gradeurl, get_string('gradeaction', 'mod_presentation'));

            $comment = isset($submission->teachercomment) ? nl2br($submission->teachercomment) : '';

            $table->data[] = [
                fullname($submission),
                userdate($submission->timemodified),
                $filelist,
                $get_grade_html($submission->grade, $presentation->gradepass), // Use Helper
                $comment,
                $gradebutton
            ];
        }
        echo html_writer::table($table);
    }

// Student Submission Logic
} else if (has_capability('mod/presentation:submit', $context)) {

    $submission = presentation_get_user_submission($presentation->id, $USER->id);

    // If student has already submitted, show the status list.
    if ($submission) {
        echo $OUTPUT->heading(get_string('submissionstatus', 'mod_presentation'));

        $table = new \html_table();
        $table->attributes['class'] = 'generaltable';
        $table->head = [
            get_string('lastmodified', 'mod_presentation'),
            get_string('filesubmission', 'mod_presentation'),
            get_string('grade', 'mod_presentation'),
            get_string('comment', 'mod_presentation'),
        ];

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_presentation', 'submission_files', $submission->id, 'timemodified DESC', false);
        $filelist = '';
        foreach ($files as $file) {
            $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
            $filelist .= html_writer::link($fileurl, $file->get_filename()) . '<br />';
        }

        $comment = isset($submission->teachercomment) ? nl2br($submission->teachercomment) : '';

        $table->data[] = [
            userdate($submission->timemodified),
            $filelist,
            $get_grade_html($submission->grade, $presentation->gradepass), // Use Helper
            $comment,
        ];
        echo html_writer::table($table);

    } else {
        // No submission yet, show the form prepared at the top
        echo $OUTPUT->heading(get_string('submissionstatus', 'mod_presentation'));
        if ($mform) {
            $mform->display();
        }
    }

} else {
    // User cannot submit or view submissions.
    echo $OUTPUT->notification('You do not have permission to view this activity.');
}

echo $OUTPUT->footer();