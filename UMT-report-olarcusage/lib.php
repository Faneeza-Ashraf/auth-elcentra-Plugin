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
 * Library functions for the Custom Report plugin.
 *
 * @package    report_olarcusage
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * This function extends the navigation with the report items.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_olarcusage_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/olarcusage:view', $context)) {
        $url = new moodle_url('/report/olarcusage/index.php', ['id' => $course->id]);
        $navigation->add(get_string('pluginname', 'report_olarcusage'), $url,
                navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Get all data for the OLARC Usage report in a single, efficient query.
 * This function replaces the need to call multiple individual functions for each course.
 *
 * @param int $semesterid Optional semester category ID to filter by.
 * @param int $schoolid Optional school category ID to filter by.
 * @return array An array of objects, where each object represents a course with all required data fields.
 */
function report_olarcusage_get_report_data($semesterid = 0, $schoolid = 0) {
    global $DB;

    $sqlparams = [];
    $whereclauses = [];
    $sql = "
        SELECT
            c.id AS courseid,
            c.fullname,
            c.shortname,
            -- Course Name Parsing directly in SQL
            SUBSTRING_INDEX(c.fullname, ' - ', 1) AS course_code,
            SUBSTRING_INDEX(SUBSTRING_INDEX(c.fullname, ' - ', 2), ' - ', -1) AS course_title,
            -- Category Hierarchy
            COALESCE(cat_semester.name, 'N/A') AS semester,
            COALESCE(cat_school.name, 'N/A') AS school,
            COALESCE(cat_program.name, 'N/A') AS program,
            -- Aggregated Faculty Information
            faculty.faculty_names AS faculty,
            faculty.faculty_emails AS email,
            -- Aggregated Student Count
            COALESCE(students.student_count, 0) AS student_count,
            -- Course Outline Status (handles both block and fallback)
            COALESCE(
                (SELECT FROM_UNIXTIME(MAX(timeuploaded)) FROM {block_courseoutline} WHERE courseid = c.id),
                'no'
            ) AS course_outline,
            -- Template Status
            CASE WHEN template_section.name IS NOT NULL AND TRIM(template_section.name) = 'Read This First!' THEN 'Yes' ELSE 'No' END AS template_status,
            -- Aggregated Activity Counts
            COALESCE(activities.assignments, 0) AS assignments,
            COALESCE(activities.quizzes, 0) AS quizzes,
            COALESCE(activities.chats, 0) AS chats,
            COALESCE(activities.learning_materials, 0) AS learning_materials,
            COALESCE(activities.recordings, 0) AS recordings,
            -- Calculated LMS Usage Status
            CASE
                WHEN COALESCE(activities.total_activities, 0) = 0 THEN 1
                WHEN COALESCE(sections_with_no_activity.empty_sections, 0) > 0 THEN 2
                ELSE 3
            END AS lms_usage,
            -- Calculated Teacher Time and Access
            COALESCE(teacher_time.total_time, '00:00:00') AS teacher_time,
            COALESCE(teacher_access.total_accesses, 0) AS teacher_access_count

        FROM {course} c

        -- Join for Category Hierarchy (assumes a 3-level structure: Semester -> School -> Program)
        JOIN {course_categories} cat_program ON c.category = cat_program.id
        LEFT JOIN {course_categories} cat_school ON cat_program.parent = cat_school.id
        LEFT JOIN {course_categories} cat_semester ON cat_school.parent = cat_semester.id AND cat_semester.parent = 0

        -- Subquery to get Faculty Information
        LEFT JOIN (
            SELECT
                ctx.instanceid AS courseid,
                GROUP_CONCAT(DISTINCT CONCAT(u.firstname, ' ', u.lastname) ORDER BY u.lastname, u.firstname SEPARATOR ', ') AS faculty_names,
                GROUP_CONCAT(DISTINCT u.email ORDER BY u.lastname, u.firstname SEPARATOR ', ') AS faculty_emails
            FROM {role_assignments} ra
            JOIN {user} u ON ra.userid = u.id
            JOIN {role} r ON ra.roleid = r.id
            JOIN {context} ctx ON ra.contextid = ctx.id AND ctx.contextlevel = 50 -- CONTEXT_COURSE
            WHERE r.archetype IN ('teacher', 'editingteacher')
            GROUP BY ctx.instanceid
        ) AS faculty ON faculty.courseid = c.id

        -- Subquery to get Student Count
        LEFT JOIN (
            SELECT
                ctx.instanceid AS courseid,
                COUNT(DISTINCT ra.userid) AS student_count
            FROM {role_assignments} ra
            JOIN {role} r ON ra.roleid = r.id
            JOIN {context} ctx ON ra.contextid = ctx.id AND ctx.contextlevel = 50 -- CONTEXT_COURSE
            WHERE r.archetype = 'student'
            GROUP BY ctx.instanceid
        ) AS students ON students.courseid = c.id

        -- Join to get the name of the first topic section for Template Status
        LEFT JOIN {course_sections} template_section ON template_section.course = c.id AND template_section.section = 1

        -- Subquery to get all Activity Counts in one pass
        LEFT JOIN (
            SELECT
                cm.course,
                COUNT(cm.id) as total_activities,
                SUM(CASE WHEN m.name = 'assign' THEN 1 ELSE 0 END) AS assignments,
                SUM(CASE WHEN m.name = 'quiz' THEN 1 ELSE 0 END) AS quizzes,
                SUM(CASE WHEN m.name IN ('chat', 'forum') THEN 1 ELSE 0 END) AS chats,
                SUM(CASE
                        WHEN m.name = 'resource' THEN 1
                        WHEN m.name = 'url' AND NOT (
                            EXISTS (SELECT 1 FROM {url} u WHERE u.id = cm.instance AND (
                                u.externalurl LIKE '%youtube.com%' OR u.externalurl LIKE '%youtu.be%' OR
                                u.externalurl LIKE '%drive.google.com%' OR u.externalurl LIKE '%zoom.us%'))
                        ) THEN 1
                        ELSE 0
                    END) AS learning_materials,
                SUM(CASE
                        WHEN m.name = 'zoom' THEN 1
                        WHEN m.name = 'url' AND (
                            EXISTS (SELECT 1 FROM {url} u WHERE u.id = cm.instance AND (
                                u.externalurl LIKE '%youtube.com%' OR u.externalurl LIKE '%youtu.be%' OR
                                u.externalurl LIKE '%drive.google.com%' OR u.externalurl LIKE '%zoom.us%'))
                        ) THEN 1
                        ELSE 0
                    END) AS recordings
            FROM {course_modules} cm
            JOIN {modules} m ON cm.module = m.id
             WHERE cm.deletioninprogress = 0
            GROUP BY cm.course
        ) AS activities ON activities.course = c.id

        -- Subquery to check for empty sections (for LMS Usage Status)
        LEFT JOIN (
            SELECT course, COUNT(id) AS empty_sections
            FROM {course_sections}
            WHERE section > 0 AND (sequence IS NULL OR sequence = '')
            GROUP BY course
        ) AS sections_with_no_activity ON sections_with_no_activity.course = c.id

        -- Subquery for Teacher Total Time (using window functions)
        LEFT JOIN (
            SELECT l.courseid, SEC_TO_TIME(SUM(t.session_duration_seconds)) AS total_time
            FROM (
                SELECT
                    l.courseid,
                    (l.timecreated - LAG(l.timecreated, 1, l.timecreated) OVER (PARTITION BY l.userid, l.courseid ORDER BY l.timecreated)) AS session_duration_seconds
                FROM {logstore_standard_log} l
                JOIN {context} ctx ON l.contextid = ctx.id AND ctx.contextlevel = 50
                JOIN {role_assignments} ra ON l.userid = ra.userid AND l.contextid = ra.contextid
                JOIN {role} r ON ra.roleid = r.id
                WHERE r.archetype IN ('teacher', 'editingteacher')
            ) AS t
            WHERE t.session_duration_seconds < 3600
            GROUP BY l.courseid
        ) AS teacher_time ON teacher_time.courseid = c.id

        -- Subquery for Teacher Access Count (using window functions)
        LEFT JOIN (
            SELECT t.courseid, SUM(t.is_new_access) AS total_accesses
            FROM (
                SELECT
                    l.courseid,
                    CASE WHEN l.courseid != LAG(l.courseid, 1, 0) OVER (PARTITION BY l.userid ORDER BY l.timecreated) THEN 1 ELSE 0 END AS is_new_access
                FROM {logstore_standard_log} l
                JOIN {context} ctx ON l.contextid = ctx.id AND ctx.contextlevel = 50
                JOIN {role_assignments} ra ON l.userid = ra.userid AND l.contextid = ra.contextid
                JOIN {role} r ON ra.roleid = r.id
                WHERE r.archetype IN ('teacher', 'editingteacher') AND l.courseid <> 0
            ) AS t
            WHERE t.courseid IS NOT NULL
            GROUP BY t.courseid
        ) AS teacher_access ON teacher_access.courseid = c.id
    ";

    if (!empty($semesterid)) {
        $whereclauses[] = 'cat_semester.id = :semesterid';
        $sqlparams['semesterid'] = $semesterid;
    }
    if (!empty($schoolid)) {
        $whereclauses[] = 'cat_school.id = :schoolid';
        $sqlparams['schoolid'] = $schoolid;
    }

    if (!empty($whereclauses)) {
        $sql .= ' WHERE ' . implode(' AND ', $whereclauses);
    }

    $sql .= " ORDER BY semester, school, program, c.fullname";

    return $DB->get_records_sql($sql, $sqlparams);
}


/**
 * Get all top-level categories to use as Semesters.
 *
 * @return array Suitable for a moodleform select element.
 */
function report_olarcusage_get_semesters() {
    global $DB;
    // Semesters are top-level categories, so their parent is 0.
    $semesters = $DB->get_records_menu('course_categories', ['parent' => 0], 'name', 'id, name');
    return [0 => get_string('allsemesters', 'report_olarcusage')] + $semesters;
}

/**
 * Get all second-level categories to use as Schools.
 *
 * @return array Suitable for a moodleform select element.
 */
function report_olarcusage_get_schools() {
    global $DB;
    // Schools are children of Semesters. We find them by checking that their parent is not 0.
    $sql = "SELECT cc.id, cc.name
            FROM {course_categories} cc
            JOIN {course_categories} p ON p.id = cc.parent
            WHERE p.parent = 0
            ORDER BY cc.name";
    $schools = $DB->get_records_sql_menu($sql);
    return [0 => get_string('allschools', 'report_olarcusage')] + $schools;
}

/**
 * Prepares data for the semester-wise usage bar chart.
 *
 * @param int $semesterid The ID of the parent category (semester).
 * @return array An array of objects, each with a name and usage percentage.
 */
function report_olarcusage_get_chart_data($semesterid) {
    global $DB;

    if (empty($semesterid)) {
        return [];
    }

    $chartdata = [];
    // Get all categories that are direct children of the selected semester. These are the "schools".
    $schools = $DB->get_records('course_categories', ['parent' => $semesterid], 'name', 'id, name, path');

    if (empty($schools)) {
        // This block handles the edge case where a semester might contain courses directly
        // without any school categories in between.
        $semester = $DB->get_record('course_categories', ['id' => $semesterid], 'id, name');
        if ($semester) {
            $sql = "SELECT
                        COUNT(c.id) AS total_courses,
                        COUNT(DISTINCT used_courses.courseid) AS used_courses
                    FROM {course} c
                    LEFT JOIN (
                        -- CORRECTED LOGIC: A course is considered 'used' if it has one or more modules.
                        SELECT DISTINCT cm.course AS courseid
                        FROM {course_modules} cm
                        WHERE cm.deletioninprogress = 0
                    ) AS used_courses ON used_courses.courseid = c.id
                    WHERE c.category = :categoryid";
            $record = $DB->get_record_sql($sql, ['categoryid' => $semesterid]);
            if ($record && $record->total_courses > 0) {
                $percentage = round(((int)$record->used_courses / (int)$record->total_courses) * 100, 2);
                $chartdata[] = [
                    'school_name' => $semester->name,
                    'usage_percentage' => $percentage,
                    'used_courses' => (int)$record->used_courses,
                    'total_courses' => (int)$record->total_courses
                ];
            }
        }
    } else {
        // This is the main logic block that will run for your structure.
        foreach ($schools as $school) {
            $sql = "SELECT
                        COUNT(DISTINCT c.id) AS total_courses,
                        COUNT(DISTINCT used_courses.courseid) AS used_courses
                    FROM {course} c
                    JOIN {course_categories} cat ON c.category = cat.id
                    LEFT JOIN (
                        -- CORRECTED LOGIC: A course is considered 'used' if it has one or more modules.
                        -- This is more reliable than checking the course_sections.sequence column.
                        SELECT DISTINCT cm.course AS courseid
                        FROM {course_modules} cm
                        WHERE cm.deletioninprogress = 0
                    ) AS used_courses ON used_courses.courseid = c.id
                    WHERE cat.id = :schoolid OR cat.path LIKE :pathlike";
            $record = $DB->get_record_sql($sql, ['schoolid' => $school->id, 'pathlike' => $school->path . '/%']);
            if ($record && $record->total_courses > 0) {
                $percentage = round(((int)$record->used_courses / (int)$record->total_courses) * 100, 2);
                $chartdata[] = [
                    'school_name' => $school->name,
                    'usage_percentage' => $percentage,
                    'used_courses' => (int)$record->used_courses,
                    'total_courses' => (int)$record->total_courses
                ];
            }
        }
    }
    return $chartdata;
}

/**
 * Prepares data for the OLARC Pattern Usage BAR chart.
 *
 * @param int $semesterid The ID of the parent category (semester).
 * @return array An array of objects for the chart.
 */
function report_olarcusage_get_pattern_usage_data($semesterid) {
    global $DB;
    if (empty($semesterid)) {
        return [];
    }
    $patternformat = get_config('report_olarcusage', 'patternformat');
    if (empty($patternformat)) {
            $patternformat = 'onetopic';
    }
    
    $chartdata = [];
    $categories_to_check = $DB->get_records('course_categories', ['parent' => $semesterid], 'name', 'id, name, path');
    if (empty($categories_to_check)) {
        $categories_to_check = $DB->get_records('course_categories', ['id' => $semesterid]);
    }
    foreach ($categories_to_check as $category) {
        if (!$category) {
            continue;
        }
        $sql = "SELECT
                    COUNT(DISTINCT c.id) AS total_courses,
                    COUNT(DISTINCT CASE WHEN c.format = :patternformat THEN c.id ELSE NULL END) AS pattern_courses
                FROM {course} c
                JOIN {course_categories} cat ON c.category = cat.id
                WHERE (cat.id = :categoryid OR cat.path LIKE :pathlike)";
        $params = [
            'categoryid' => $category->id,
            'pathlike' => $category->path . '/%',
            'patternformat' => $patternformat
        ];
        $record = $DB->get_record_sql($sql, $params);

        if ($record && $record->total_courses > 0) {
            $percentage = round(((int)$record->pattern_courses / (int)$record->total_courses) * 100, 2);
            $chartdata[] = [
                'school_name' => $category->name,
                'percentage' => $percentage,
            ];
        }
    }
    return $chartdata;
}
/**
 * Prepares data for the School-wise Usage (by Program) bar chart.
 *
 * @param int $schoolid The ID of the parent category (school).
 * @return array An array of data objects for the chart.
 */
function report_olarcusage_get_program_usage_data($schoolid) {
    global $DB;
    if (empty($schoolid)) {
        return [];
    }
    $chartdata = [];
    $programs = $DB->get_records('course_categories', ['parent' => $schoolid], 'name', 'id, name, path');
    if (empty($programs)) {
        return [];
    }
    foreach ($programs as $program) {
        if (!$program) {
            continue;
        }
        $sql_courses = "SELECT c.id
                        FROM {course} c
                        JOIN {course_categories} cat ON c.category = cat.id
                        WHERE cat.id = :categoryid OR cat.path LIKE :pathlike";
        $courses = $DB->get_records_sql($sql_courses, ['categoryid' => $program->id, 'pathlike' => $program->path . '/%']);
        if (empty($courses)) {
            $chartdata[] = ['program_name' => $program->name, 'percentage' => 0];
            continue;
        }
        $total_courses = count($courses);
        $courseids = array_keys($courses);
        list($sql_in, $params_in) = $DB->get_in_or_equal($courseids);
        $sql_count_used = "SELECT COUNT(DISTINCT cm.course)
                           FROM {course_modules} cm
                           WHERE cm.course " . $sql_in . "
                             AND cm.deletioninprogress = 0";
        $used_courses = $DB->count_records_sql($sql_count_used, $params_in);
        $percentage = 0;
        if ($total_courses > 0) {
            $percentage = round(((int)$used_courses / (int)$total_courses) * 100, 2);
        }
        $chartdata[] = ['program_name' => $program->name, 'percentage' => $percentage];
    }
    return $chartdata;
}

/**
 * Prepares data for the Semester Comparison bar chart.
 *
 * @return array An array of data objects for the chart.
 */
function report_olarcusage_get_semester_comparison_data() {
    global $DB;
    $patternformat = get_config('report_olarcusage', 'patternformat');
    if (empty($patternformat)) {
        $patternformat = 'onetopic';
    }
    $sql = "SELECT
                semester.id,
                semester.name AS semester_name,
                COUNT(DISTINCT c.id) AS total_courses,
                COUNT(DISTINCT CASE WHEN c.format = :patternformat THEN c.id ELSE NULL END) AS pattern_courses
            FROM {course_categories} AS semester
            JOIN {course_categories} AS child_cat ON (child_cat.id = semester.id OR child_cat.path LIKE CONCAT(semester.path, '/%'))
            JOIN {course} c ON c.category = child_cat.id
            WHERE semester.parent = 0
            GROUP BY semester.id, semester.name
            HAVING COUNT(DISTINCT c.id) > 0
            ORDER BY semester.name DESC";
    $records = $DB->get_records_sql($sql, ['patternformat' => $patternformat]);
    $chartdata = [];
    if ($records) {
        foreach ($records as $record) {
            $percentage = 0;
            if ($record->total_courses > 0) {
                $percentage = round(((int)$record->pattern_courses / (int)$record->total_courses) * 100, 2);
            }
            $chartdata[] = ['semester_name' => $record->semester_name, 'percentage' => $percentage];
        }
    }
    return $chartdata;
}

/**
 * Parse course name to extract course code, title, and section.
 *
 * @deprecated Logic is now handled by SUBSTRING_INDEX in the main SQL query.
 * @param string $fullname The full course name
 * @return array Array containing course_code, course_title, and section
 */
function report_olarcusage_parse_course_name($fullname) {
    $parts = explode(' - ', $fullname);
    $result = ['course_code' => '', 'course_title' => '', 'section' => ''];
    if (count($parts) >= 2) {
        $result['course_code'] = trim($parts[0]);
        $result['course_title'] = trim($parts[1]);
        if (count($parts) >= 4) {
            $result['section'] = trim($parts[count($parts) - 2]) . ' - ' . trim($parts[count($parts) - 1]);
        }
    }
    return $result;
}

/**
 * Get category hierarchy for a course.
 *
 * @deprecated Logic is now handled by JOINs on the categories table in the main SQL query.
 * @param int $categoryid The category ID
 * @return array Array containing semester, school, and program
 */
function report_olarcusage_get_category_hierarchy($categoryid) {
    global $DB;
    $result = ['semester' => '', 'school' => '', 'program' => ''];
    if (!$categoryid) {
        return $result;
    }
    $category = $DB->get_record('course_categories', ['id' => $categoryid]);
    if (!$category) {
        return $result;
    }
    $parentids = explode('/', trim($category->path, '/'));
    $programid = array_pop($parentids);
    if ($programid && ($programcat = $DB->get_record('course_categories', ['id' => $programid]))) {
        $result['program'] = $programcat->name;
    }
    $schoolid = array_pop($parentids);
    if ($schoolid && ($schoolcat = $DB->get_record('course_categories', ['id' => $schoolid]))) {
        $result['school'] = $schoolcat->name;
    }
    $semesterid = array_pop($parentids);
    if ($semesterid && ($semestercat = $DB->get_record('course_categories', ['id' => $semesterid]))) {
        $result['semester'] = $semestercat->name;
    }
    if (empty($result['program']) && !empty($result['school'])) {
        $result['program'] = $result['school'];
    }
    return $result;
}

/**
 * Get faculty information for a course.
 *
 * @deprecated Logic is now handled by a GROUP_CONCAT subquery in the main SQL query.
 * @param int $courseid The course ID
 * @return array Array containing faculty names and emails
 */
function report_olarcusage_get_faculty_info($courseid) {
    global $DB;
    $context = context_course::instance($courseid);
    $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
       u.middlename, u.alternatename, u.email
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {role} r ON r.id = ra.roleid
            WHERE ra.contextid = :contextid
            AND (r.archetype = 'teacher' OR r.archetype = 'editingteacher')
            ORDER BY u.lastname, u.firstname";
    $teachers = $DB->get_records_sql($sql, ['contextid' => $context->id]);
    $names = [];
    $emails = [];
    foreach ($teachers as $teacher) {
        $names[] = fullname($teacher);
        $emails[] = $teacher->email;
    }
    return ['faculty' => implode(', ', $names), 'email' => implode(', ', $emails)];
}

/**
 * Get activity counts for a course.
 *
 * @deprecated Logic is now handled by a conditional SUM subquery in the main SQL query.
 * @param int $courseid The course ID
 * @return array Array containing various activity counts
 */
function report_olarcusage_get_activity_counts($courseid) {
    global $DB;
    $result = ['assignments' => 0, 'quizzes' => 0, 'chats' => 0, 'learning_materials' => 0, 'recordings' => 0];
    $sql = "SELECT cm.id, m.name as modulename, cm.instance
            FROM {course_modules} cm
            JOIN {modules} m ON m.id = cm.module
            WHERE cm.course = :courseid AND cm.deletioninprogress = 0";
    $modules = $DB->get_records_sql($sql, ['courseid' => $courseid]);
    foreach ($modules as $module) {
        switch ($module->modulename) {
            case 'assign': $result['assignments']++; break;
            case 'quiz': $result['quizzes']++; break;
            case 'chat': case 'forum': $result['chats']++; break;
            case 'resource': case 'url':
                if ($module->modulename == 'url') {
                    $url_record = $DB->get_record('url', ['id' => $module->instance]);
                    if ($url_record && report_olarcusage_is_recording_url($url_record->externalurl)) {
                        $result['recordings']++;
                    } else {
                        $result['learning_materials']++;
                    }
                } else {
                    $result['learning_materials']++;
                }
                break;
            case 'zoom': $result['recordings']++; break;
        }
    }
    return $result;
}

/**
 * Check if a URL is a recording link (YouTube, Google Drive, Zoom).
 *
 * @param string $url The URL to check
 * @return bool True if it's a recording URL
 */
function report_olarcusage_is_recording_url($url) {
    $recording_domains = ['youtube.com', 'youtu.be', 'drive.google.com', 'zoom.us'];
    foreach ($recording_domains as $domain) {
        if (strpos($url, $domain) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Check if course outline is added.
 *
 * @deprecated Logic is now handled by a subquery in the main SQL query.
 * @param int $courseid The course ID
 * @return string Date when outline was added or 'no'
 */
function report_olarcusage_get_course_outline_status($courseid) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($dbman->table_exists('block_courseoutline')) {
        $outline = $DB->get_record('block_courseoutline', ['courseid' => $courseid]);
        if ($outline) {
            return userdate($outline->timeuploaded, get_string('strftimedatefullshort'));
        }
    }
    return 'no';
}

/**
 * Get student count for a course.
 *
 * @deprecated Logic is now handled by a COUNT subquery in the main SQL query.
 * @param int $courseid The course ID
 * @return int Number of students
 */
function report_olarcusage_get_student_count($courseid) {
    global $DB;
    $context = context_course::instance($courseid);
    $sql = "SELECT COUNT(DISTINCT ra.userid)
            FROM {role_assignments} ra
            JOIN {role} r ON r.id = ra.roleid
            WHERE ra.contextid = :contextid
            AND r.archetype = 'student'";
    return $DB->count_records_sql($sql, ['contextid' => $context->id]);
}

/**
 * Get LMS usage status for a course.
 *
 * @deprecated Logic is now handled by CASE statements in the main SQL query.
 * @param int $courseid The course ID
 * @return int Usage status (1, 2, or 3)
 */
function report_olarcusage_get_lms_usage_status($courseid) {
    global $DB;
    $total_activities = $DB->count_records('course_modules', ['course' => $courseid, 'deletioninprogress' => 0]);
    if ($total_activities == 0) {
        return 1;
    }
    $sections = $DB->get_records('course_sections', ['course' => $courseid], 'section', 'id, section, sequence');
    foreach ($sections as $section) {
        if ($section->section == 0) {
            continue;
        }
        if (empty($section->sequence)) {
            return 2;
        }
    }
    return 3;
}

/**
 * Check template status for a course.
 *
 * @deprecated Logic is now handled by a LEFT JOIN and CASE statement in the main SQL query.
 * @param int $courseid The course ID
 * @return string 'Yes' or 'No'
 */
function report_olarcusage_get_template_status($courseid) {
    global $DB;
    $section = $DB->get_record('course_sections', ['course' => $courseid, 'section' => 1], 'name');
    if (!empty($section->name) && trim($section->name) === 'Read This First!') {
        return 'Yes';
    }
    return 'No';
}

/**
 * Calculate usage activity percentage.
 *
 * @deprecated This calculation should be performed in the presentation layer based on data from the main query.
 * @param array $activity_counts Array of activity counts
 * @return float Percentage of usage activity
 */
function report_olarcusage_calculate_usage_percentage($activity_counts) {
    $required_activities = ['assignments', 'quizzes', 'chats', 'learning_materials', 'recordings'];
    $used_activities = 0;
    foreach ($required_activities as $activity) {
        if (isset($activity_counts[$activity]) && $activity_counts[$activity] > 0) {
            $used_activities++;
        }
    }
    return ($used_activities / count($required_activities)) * 100;
}

/**
 * Get the total time spent by all teachers in a specific course.
 *
 * @deprecated Logic is now handled by a subquery in the main SQL query.
 * @param int $courseid The course ID
 * @return string Total time in HH:MM:SS format or '00:00:00'
 */
function report_olarcusage_get_teacher_total_time($courseid) {
    global $DB;
    $sql = "SELECT SEC_TO_TIME(SUM(t.session_duration_seconds)) AS totaltime FROM (
                SELECT (l.timecreated - LAG(l.timecreated, 1, l.timecreated) OVER (PARTITION BY l.userid, l.courseid ORDER BY l.timecreated)) AS session_duration_seconds
                FROM {logstore_standard_log} l
                JOIN {role_assignments} ra ON l.userid = ra.userid AND l.contextid = ra.contextid
                JOIN {role} r ON ra.roleid = r.id
                WHERE r.shortname IN ('editingteacher', 'teacher') AND l.courseid = :courseid
            ) AS t WHERE t.session_duration_seconds < 3600";
    $result = $DB->get_record_sql($sql, ['courseid' => $courseid]);
    if ($result && !is_null($result->totaltime)) {
        return $result->totaltime;
    }
    return '00:00:00';
}

/**
 * Get the total number of times teachers have accessed a specific course.
 *
 * @deprecated Logic is now handled by a subquery in the main SQL query.
 * @param int $courseid The course ID
 * @return int The total number of access sessions for ALL teachers in the course.
 */
function report_olarcusage_get_teacher_access_count($courseid) {
    global $DB;
    $sql = "SELECT SUM(t.is_new_access) AS total_accesses FROM (
                SELECT
                    l.courseid,
                    CASE WHEN l.courseid != LAG(l.courseid, 1, 0) OVER (PARTITION BY l.userid ORDER BY l.timecreated) THEN 1 ELSE 0 END AS is_new_access
                FROM {logstore_standard_log} l
                JOIN {role_assignments} ra ON l.userid = ra.userid AND l.contextid = ra.contextid
                JOIN {role} r ON ra.roleid = r.id
                WHERE r.shortname IN ('editingteacher', 'teacher')
            ) AS t WHERE t.courseid = :courseid";
    $result = $DB->get_record_sql($sql, ['courseid' => $courseid]);
    if ($result && !is_null($result->total_accesses)) {
        return (int)$result->total_accesses;
    }
    return 0;
}