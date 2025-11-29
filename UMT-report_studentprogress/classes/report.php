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
 * Course data source for the Custom Report plugin.
 *
 * @package    report_studentprogress
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace report_studentprogress;

defined('MOODLE_INTERNAL') || die();

class report {
    protected $semesterid;
    protected $schoolid;
    protected $page;
    protected $perpage;

    public function __construct($semesterid = 0, $schoolid = 0, $page = 0, $perpage = 20) {
        $this->semesterid = $semesterid;
        $this->schoolid = $schoolid;
        $this->page = $page;
        $this->perpage = $perpage;
    }

    public function get_headers() {
        return [
            get_string('header_studentid', 'report_studentprogress'),
            get_string('header_studentname', 'report_studentprogress'),
            get_string('header_coursetitle', 'report_studentprogress'),
            get_string('header_section', 'report_studentprogress'),
            get_string('header_teachername', 'report_studentprogress'),
            get_string('header_timespent', 'report_studentprogress'),
            get_string('header_accesses', 'report_studentprogress'),
        ];
    }

    public function get_recordset() {
        global $DB;
        $query = $this->get_sql_and_params();
        return $DB->get_recordset_sql($query['sql'], $query['params'], $this->page * $this->perpage, $this->perpage);
    }

    public function get_full_recordset() {
        global $DB;
        $query = $this->get_sql_and_params();
        return $DB->get_recordset_sql($query['sql'], $query['params']);
    }

    public function get_total_count() {
        global $DB;
        $query = $this->get_sql_and_params(true);
        return $DB->count_records_sql($query['sql'], $query['params']);
    }

    private function get_sql_and_params($iscount = false) {
        $select = $iscount
            ? "SELECT COUNT(DISTINCT CONCAT(u.id, '-', c.id))"
            : "SELECT
                    u.*,
                    u.idnumber AS studentid,
                    c.id AS courseid,
                    c.fullname AS fullname_agg,
                    COALESCE(SUM(bd.timespent), 0) AS total_time_spent,
                    COALESCE(SUM(bd.totalaccesses), 0) AS number_of_accesses,
                    (
                        SELECT GROUP_CONCAT(CONCAT(u_teacher.firstname, ' ', u_teacher.lastname) SEPARATOR ', ')
                        FROM {role_assignments} ra_teacher
                        JOIN {user} u_teacher ON u_teacher.id = ra_teacher.userid
                        JOIN {role} r_teacher ON r_teacher.id = ra_teacher.roleid
                        JOIN {context} ctx_teacher ON ctx_teacher.id = ra_teacher.contextid
                        WHERE ctx_teacher.instanceid = c.id AND ctx_teacher.contextlevel = 50
                          AND r_teacher.archetype IN ('teacher', 'editingteacher')
                    ) AS faculty";

        $from = " FROM {user} u
                    JOIN {user_enrolments} ue ON ue.userid = u.id
                    JOIN {enrol} e ON e.id = ue.enrolid
                    JOIN {course} c ON c.id = e.courseid
                    JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = 50
                    JOIN {role_assignments} ra ON ra.userid = u.id AND ra.contextid = ctx.id
                    JOIN {role} r ON r.id = ra.roleid
                    LEFT JOIN {block_dedication} bd ON bd.userid = u.id AND bd.courseid = c.id";
        
        $where = " WHERE r.archetype = 'student'";
        $params = [];

        if ($this->schoolid) {
            $where .= " AND c.category IN (
                            SELECT cat.id FROM {course_categories} cat WHERE cat.parent = :schoolid
                       )";
            $params['schoolid'] = $this->schoolid;
        } else if ($this->semesterid) {
            $where .= " AND c.category IN (
                            SELECT cat.id FROM {course_categories} cat WHERE cat.parent IN (SELECT id FROM {course_categories} WHERE parent = :semesterid)
                       )";
            $params['semesterid'] = $this->semesterid;
        }

        $sql = $select . $from . $where;

        if (!$iscount) {
            $sql .= " GROUP BY u.id, c.id";
            $sql .= " ORDER BY u.lastname, u.firstname, c.fullname";
        }

        return ['sql' => $sql, 'params' => $params];
    }
    
    public function format_time($seconds) {
        if ($seconds === null || !is_numeric($seconds) || $seconds < 0) {
            return '00:00:00';
        }
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
    
    public function get_formatted_data() {
        $data = $this->get_recordset();
        $formatted = [];
        foreach ($data as $row) {
            $fullname = $row->fullname_agg;
            $parts = explode(' - ', $fullname);
            $coursetitle = (count($parts) >= 2) ? trim($parts[1]) : 'N/A';
            $section = (count($parts) >= 4) ? trim($parts[count($parts) - 2]) . ' - ' . trim($parts[count($parts) - 1]) : 'N/A';

            $formatted[] = [
                $row->studentid,
                fullname($row),
                $coursetitle,
                $section,
                $row->faculty,
                $this->format_time($row->total_time_spent),
                $row->number_of_accesses
            ];
        }
        $data->close(); 
        return $formatted;
    }
}