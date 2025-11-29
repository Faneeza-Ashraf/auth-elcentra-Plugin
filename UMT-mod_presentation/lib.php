<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Library of interface functions and constants for the Presentation module.
 *
 * @package     mod_presentation
 * @copyright   2025 Endush Fairy <endush.fairy@paktaleem.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/lib/moodlelib.php');

function presentation_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_OTHER;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

function presentation_add_instance(stdClass $presentation) {
    global $DB;
    $presentation->timecreated = time();
    $presentation->timemodified = time();

    // Determine grade type based on the grade value.
    if (!isset($presentation->grade)) {
        $presentation->grade = 0;
    }

    if ($presentation->grade == 0) {
         $presentation->gradepass = 0;
         $presentation->scale = 0;
    } else if ($presentation->grade < 0) {
        $presentation->scale = $presentation->grade;
        $presentation->gradepass = 0;
    } else {
        $presentation->scale = 0;
    }

    $presentation->id = $DB->insert_record('presentation', $presentation);
    presentation_grade_item_update($presentation);
    return $presentation->id;
}

function presentation_update_instance(stdClass $presentation) {
    global $DB;
    $presentation->id = $presentation->instance;
    $presentation->timemodified = time();

    if (!isset($presentation->grade)) {
        $presentation->grade = 0;
    }

    if ($presentation->grade == 0) {
         $presentation->gradepass = 0;
         $presentation->scale = 0;
    } else if ($presentation->grade < 0) {
        $presentation->scale = $presentation->grade;
        $presentation->gradepass = 0;
    } else {
        $presentation->scale = 0;
    }

    $DB->update_record('presentation', $presentation);
    presentation_grade_item_update($presentation);
    return true;
}

function presentation_delete_instance($id) {
    global $DB;
    if (!$presentation = $DB->get_record('presentation', ['id' => $id])) {
        return false;
    }
    $cm = get_coursemodule_from_instance('presentation', $presentation->id, $presentation->course, 0, MUST_EXIST);
    $context = \context_module::instance($cm->id);
    $DB->delete_records('presentation_submissions', ['presentationid' => $presentation->id]);
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_presentation');
    $DB->delete_records('presentation', ['id' => $presentation->id]);
    grade_update('mod/presentation', $presentation->course, 'mod', 'presentation', $presentation->id, 0, null, array('deleted' => 1));
    return true;
}

function presentation_grade_item_update($presentation, $grades = null) {
    global $DB, $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    if (!isset($presentation->id)) { 
        return; 
    }
    
    $freshpresentation = $DB->get_record('presentation', ['id' => $presentation->id]);
    if (!$freshpresentation) {
        return;
    }
    
    if (isset($presentation->grade)) $freshpresentation->grade = $presentation->grade;
    if (isset($presentation->gradepass)) $freshpresentation->gradepass = $presentation->gradepass;
    if (isset($presentation->cmidnumber)) $freshpresentation->cmidnumber = $presentation->cmidnumber;

    $params = [
        'itemname' => $freshpresentation->name,
        'idnumber' => $freshpresentation->cmidnumber ?? '',
    ];

    if ($freshpresentation->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $freshpresentation->grade;
        $params['grademin']  = 0;
        $params['gradepass'] = $freshpresentation->gradepass; 
    } else if ($freshpresentation->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$freshpresentation->grade;
        $params['gradepass'] = 0;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    grade_update('mod/presentation', $freshpresentation->course, 'mod', 'presentation', $freshpresentation->id, 0, $grades, $params);
}

function presentation_get_user_submission($presentationid, $userid) {
    global $DB;
    return $DB->get_record('presentation_submissions', ['presentationid' => $presentationid, 'userid' => $userid]);
}

function presentation_get_completion_state($course, $cm, $userid, $modetracking) {
    global $DB;

    $presentation = $DB->get_record('presentation', ['id' => $cm->instance], '*', MUST_EXIST);
    $submission = presentation_get_user_submission($presentation->id, $userid);

    if (!$submission || !isset($submission->grade) || $submission->grade === null) {
        return COMPLETION_INCOMPLETE;
    }

    if ($presentation->grade == 0) { 
        return COMPLETION_COMPLETE;
    }

    if ($presentation->grade > 0) {
        if ($submission->grade >= ($presentation->gradepass - 0.00001)) {
            return COMPLETION_COMPLETE_PASS;
        } else {
            return COMPLETION_COMPLETE_FAIL;
        }
    }

    return COMPLETION_INCOMPLETE;
}

/**
 * Serves the submission files.
 * THIS FUNCTION WAS MISSING, CAUSING THE 404 ERROR.
 */
function presentation_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    if ($filearea !== 'submission_files') {
        return false;
    }

    $itemid = (int)array_shift($args); // This is the submission ID

    // Retrieve the submission record to check ownership
    $submission = $DB->get_record('presentation_submissions', ['id' => $itemid]);
    if (!$submission) {
        return false;
    }

    // Permissions check:
    // Teachers/Admins can view all. Students can only view their own.
    $canview = false;
    if (has_capability('mod/presentation:viewsubmissions', $context)) {
        $canview = true;
    } elseif ($submission->userid == $USER->id && has_capability('mod/presentation:submit', $context)) {
        $canview = true;
    }

    if (!$canview) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_presentation/$filearea/$itemid/$relativepath";

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, true, $options);
}  //Updated by Faneeza Muskan