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
 * Main report class for the Custom Report plugin.
 *
 * @package    report_olarcusage
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_olarcusage;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/report/olarcusage/lib.php');

/**
 * Main report class for generating course analytics data
 */
class report {
    private $courseid;
    private $semesterid;
    private $schoolid;
    private $reporttype;
    private $page;
    private $perpage;
    private $data;
    private $totalcount; 

    /**
     * Constructor - now with pagination parameters.
     */
    public function __construct($courseid = 0, $semesterid = 0, $schoolid = 0, $reporttype = 'all', $page = 0, $perpage = 20) {
        $this->courseid = $courseid;
        $this->semesterid = $semesterid;
        $this->schoolid = $schoolid;
        $this->reporttype = $reporttype;
        $this->page = $page;
        $this->perpage = $perpage;
        $this->data = [];
        $this->totalcount = null; 
    }

        public function generate_data() {
        global $DB;
        $sqlfrom = "FROM {course} c
                    JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)
                    JOIN {role_assignments} ra ON ra.contextid = ctx.id
                    JOIN {user} u ON u.id = ra.userid
                    JOIN {role} r ON r.id = ra.roleid
                    JOIN {course_categories} cat ON c.category = cat.id";
        
        $params = ['contextlevel' => CONTEXT_COURSE];
        $where = ['c.id <> :siteid', "(r.shortname = 'editingteacher' OR r.shortname = 'teacher')"];
        $params['siteid'] = SITEID;

        if ($this->courseid > 0) {
            $where[] = 'c.id = :courseid';
            $params['courseid'] = $this->courseid;
        }

        $categoryid = 0;
        if ($this->schoolid) {
            $categoryid = $this->schoolid;
        } else if ($this->semesterid) {
            $categoryid = $this->semesterid;
        }
        if ($categoryid) {
            $category = $DB->get_record('course_categories', ['id' => $categoryid], 'id, path');
            if ($category) {
                $where[] = '(cat.id = :categoryid OR cat.path LIKE :pathlike)';
                $params['categoryid'] = $categoryid;
                $params['pathlike'] = $category->path . '/%';
            }
        }

        if ($this->reporttype === 'online') {
            $where[] = "(" . $DB->sql_like('c.shortname', ':onlinelike1') . " OR " . $DB->sql_like('c.shortname', ':onlinelike2') . ")";
            $params['onlinelike1'] = '%-ONL%';
            $params['onlinelike2'] = '%Online%';
        } else if ($this->reporttype === 'onpremises') {
            $where[] = "(" . $DB->sql_like('c.shortname', ':onlinelike1', false, true) . " AND " . $DB->sql_like('c.shortname', ':onlinelike2', false, true) . ")";
            $params['onlinelike1'] = '%-ONL%';
            $params['onlinelike2'] = '%Online%';
        }

        $sqlwhere = " WHERE " . implode(" AND ", $where);

        $this->totalcount = $DB->count_records_sql("SELECT COUNT(ra.id) $sqlfrom $sqlwhere", $params);

        $selectfields = "ra.id AS enrollmentid, c.id AS courseid, c.fullname, c.category, u.*";
        
        $enrollments = $DB->get_records_sql("SELECT $selectfields $sqlfrom $sqlwhere ORDER BY c.fullname, u.lastname, u.firstname", $params, $this->page * $this->perpage, $this->perpage);
        
        $this->data = [];
        if ($enrollments) {
            foreach ($enrollments as $enrollment) {
                $coursedata = $this->process_course_data($enrollment);
                $this->data[] = $coursedata;
            }
        }
    }

    /**
     * Get the total count of all matching records.
     * @return int
     */
    public function get_total_count() {
        if (is_null($this->totalcount)) {
            $this->generate_data();
        }
        return $this->totalcount;
    }

    /**
     * Get report data for the current page.
     * @return array
     */
    public function get_data() {
        if (empty($this->data)) {
            $this->generate_data();
        }
        return $this->data;
    }

    /**
     * Process data for a single course and a single teacher.
     * @param stdClass $enrollment An object containing course and user details.
     * @return array Processed data for one report row.
     */
    private function process_course_data($enrollment) {
        global $CFG;

        $name_parts = report_olarcusage_parse_course_name($enrollment->fullname);
        $category_hierarchy = report_olarcusage_get_category_hierarchy($enrollment->category);
        $activity_counts = report_olarcusage_get_activity_counts($enrollment->courseid);
        $outline_status = report_olarcusage_get_course_outline_status($enrollment->courseid);
        $student_count = report_olarcusage_get_student_count($enrollment->courseid);
        $usage_status = report_olarcusage_get_lms_usage_status($enrollment->courseid);
        $template_status = report_olarcusage_get_template_status($enrollment->courseid);
        $usage_percentage = report_olarcusage_calculate_usage_percentage($activity_counts);
        $teacher_total_time = report_olarcusage_get_teacher_total_time($enrollment->courseid);
        $teacher_access_count = report_olarcusage_get_teacher_access_count($enrollment->courseid);

        return [
            'semester'                      => $category_hierarchy['semester'],
            'school'                        => $category_hierarchy['school'],
            'program'                       => $category_hierarchy['program'],
            'course_code'                   => $name_parts['course_code'],
            'course_title'                  => $name_parts['course_title'],
            'faculty'                       => fullname($enrollment),
            'email'                         => $enrollment->email,
            'section'                       => $name_parts['section'],
            'assignments_given'             => $activity_counts['assignments'],
            'quizzes_taken'                 => $activity_counts['quizzes'],
            'chats'                         => $activity_counts['chats'],
            'learning_material_uploaded'    => $activity_counts['learning_materials'],
            'course_outline_added'          => $outline_status,
            'students_on_lms'               => $student_count,
            'lms_usage_status'              => $usage_status,
            'recording_link'                => $activity_counts['recordings'],
            'course_link'                   => $CFG->wwwroot . '/course/view.php?id=' . $enrollment->courseid,
            'template_status'               => $template_status,
            'usage_activity_percent'        => round($usage_percentage, 2),
            'total_teacher_time'            => $teacher_total_time,
            'teacher_access_count'          => $teacher_access_count,
        ];
    }

    /**
     * Get report headers.
     * @return array Report headers
     */
    public function get_headers() {
        return [
            get_string('semester', 'report_olarcusage'),
            get_string('school', 'report_olarcusage'),
            get_string('program', 'report_olarcusage'),
            get_string('coursecode', 'report_olarcusage'),
            get_string('coursetitle', 'report_olarcusage'),
            get_string('faculty', 'report_olarcusage'),
            get_string('email', 'report_olarcusage'),
            get_string('section', 'report_olarcusage'),
            get_string('assignmentsgiven', 'report_olarcusage'),
            get_string('quizzestaken', 'report_olarcusage'),
            get_string('chats', 'report_olarcusage'),
            get_string('learningmaterialuploaded', 'report_olarcusage'),
            get_string('courseoutlineadded', 'report_olarcusage'),
            get_string('studentsonlms', 'report_olarcusage'),
            get_string('lmsusagestatus', 'report_olarcusage'),
            get_string('recordinglink', 'report_olarcusage'),
            get_string('courselink', 'report_olarcusage'),
            get_string('templatestatus', 'report_olarcusage'),
            get_string('usageactivitypercent', 'report_olarcusage'),
            get_string('totalteachertime', 'report_olarcusage'),
            get_string('teacheraccesscount', 'report_olarcusage'),
        ];
    }

    /**
     * Get formatted data for display.
     * @return array Formatted data
     */
    public function get_formatted_data() {
        $data = $this->get_data();
        $formatted_data = [];

        foreach ($data as $row) {
            $formatted_row = [
                $row['semester'],
                $row['school'],
                $row['program'],
                $row['course_code'],
                $row['course_title'],
                $row['faculty'],
                $row['email'],
                $row['section'],
                $row['assignments_given'],
                $row['quizzes_taken'],
                $row['chats'],
                $row['learning_material_uploaded'],
                $row['course_outline_added'],
                $row['students_on_lms'],
                $this->format_usage_status($row['lms_usage_status']),
                $row['recording_link'],
                $row['course_link'],
                $row['template_status'],
                $row['usage_activity_percent'] . '%',
                $row['total_teacher_time'],
                $row['teacher_access_count'],
            ];
            $formatted_data[] = $formatted_row;
        }

        return $formatted_data;
    }

    /**
     * Format usage status for display.
     * @param int $status Usage status code
     * @return string Formatted status
     */
    private function format_usage_status($status) {
        switch ($status) {
            case 1:
                return '1 - ' . get_string('usagestatus1', 'report_olarcusage');
            case 2:
                return '2 - ' . get_string('usagestatus2', 'report_olarcusage');
            case 3:
                return '3 - ' . get_string('usagestatus3', 'report_olarcusage');
            default:
                return $status;
        }
    }
}