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
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2013 Remote-Learner.net Inc (http://www.remote-learner.net)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    local_elegacy
 * @author     Syed Zonair
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2008-2013 Remote Learner.net Inc http://www.remote-learner.net
 *
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Run the script.
 *
 * @param int $courseid
 *
 * @return int
 *
 */
function local_elegacy_run_script(int $courseid) :int {
    // Validate if courseid is not related to a course in the site.
    if (!local_elegacy_validate_course($courseid)) {
        return 1;
    }

    // Get course object.
    $course = get_course($courseid);

    // Check if the course completion setting is not set.
    if (!local_elegacy_is_course_completion_set($course)) {
        return 2;
    }

    // Get enrolled students in course.
    $students = local_elegacy_fetch_enrolled_students($courseid);

    foreach ($students as $student) {
        // Get the course completion date of the student in the course.
        $moodlecoursecompletiondate = local_elegacy_check_student_course_completion_in_moodle($courseid, $student->id);

        // Check if the date is empty.
        if (!$moodlecoursecompletiondate) {
            continue;
        }

        // Get the course completion date of the student both in ELIS.
        try {
            $eliscoursecompletiondate = local_elegacy_get_course_completion_date_in_elis($course, $student->id);
        } catch (\Throwable $th) {
            continue;
        }

        // Check if the date is empty.
        if (!$eliscoursecompletiondate) {
            continue;
        }

        // Compare both course completion dates.
        if (local_elegacy_compare_dates($moodlecoursecompletiondate, $eliscoursecompletiondate)) {
            continue;
        }

        // Update the course completion date in MOODLE.
        local_elegacy_update_course_completion_date_in_moodle($courseid, $student->id, $eliscoursecompletiondate);
    }

    return 0;
}

/**
 * Return true if courseid exist in the database.
 *
 * @param int $courseid
 *
 * @return bool
 *
 */
function local_elegacy_validate_course(int $courseid) :bool {
    global $DB;
    return $DB->record_exists('course', ['id' => $courseid]);
}

/**
 * Return true if course completion setting is set.
 *
 * @param object $course
 *
 * @return bool
 *
 */
function local_elegacy_is_course_completion_set(object $course) :bool {
    // Get course completion info class.
    $completioninfo = new completion_info($course);

    // Course completion is enabled from the course and site.
    if (!$completioninfo->is_enabled()) {
        return false;
    }

    // Return true if course has any criteria of completion is set.
    return $completioninfo->has_criteria();
}

/**
 * Fetch the enrolled students in the course.
 *
 * @param int $courseid
 *
 * @return array
 *
 */
function local_elegacy_fetch_enrolled_students(int $courseid) :array {
    $roleid = 5;
	$context = context_course::instance($courseid);
    return get_role_users($roleid, $context, false, 'u.id', 'u.id', false);
}

/**
 * Get the course completion timestamp of the student in a course.
 *
 * @param int $courseid
 * @param int $studentid
 *
 * @return int
 *
 */
function local_elegacy_check_student_course_completion_in_moodle(int $courseid, int $studentid) {
    global $DB;
    return $DB->get_field('course_completions', 'timecompleted', [
        'userid'    => $studentid,
        'course'    => $courseid,
    ]);
}

/**
 * Get the course completion timestamp in class instance of the ELIS.
 *
 * @param object $course
 * @param int $studentid
 *
 * @return int
 *
 */
function local_elegacy_get_course_completion_date_in_elis(object $course, int $studentid) {
    global $DB, $CFG;

    // Get the classid of the course.
    require_once($CFG->dirroot.'/local/elisprogram/lib/data/pmclass.class.php');
    $pmclass = pmclass::get_by_idnumber($course->idnumber);
    $classid = $pmclass->id;

    // Get the ELIS user id.
    $elisuserid = $DB->get_field('local_elisprogram_usr_mdl', 'cuserid', ['muserid' => $studentid]);

    // Get the student class enrollment record.
    require_once($CFG->dirroot.'/local/elisprogram/lib/data/student.class.php');
    $student = student::get_userclass($elisuserid, $classid);

    // Course completion date.
    return $student->completetime;
}

/**
 * Compare the equality of two timestamps.
 *
 * @param int $timestamp1
 * @param int $timestamp2
 *
 * @return bool
 *
 */
function local_elegacy_compare_dates(int $timestamp1, int $timestamp2) :bool {
    $date1 = date('Y-m-d', $timestamp1);
    $date2 = date('Y-m-d', $timestamp2);

    return ($date1 === $date2) ? true : false;
}

/**
 * Update course completion date of the student in a course in MOODLE.
 *
 * @param int $courseid
 * @param int $studentid
 * @param int $coursecompletiondate
 *
 */
function local_elegacy_update_course_completion_date_in_moodle(int $courseid, int $studentid, int $coursecompletiondate) {
    global $DB;
    $DB->set_field('course_completions', 'timecompleted', $coursecompletiondate, [
        'userid'    => $studentid,
        'course'    => $courseid,
    ]);
}

/**
 * Format and show message as notification.
 *
 * @param mixed $code
 *
 */
function local_elegacy_show_message($code) {
    $message = get_string('sync_message_' . $code, 'local_elegacy');

    // Set the level of the notification.
    if ($code == 0) {
        $level = \core\output\notification::NOTIFY_SUCCESS;
    } else {
        \core\output\notification::NOTIFY_ERROR;
    }

    \core\notification::add($message, $level);
}
