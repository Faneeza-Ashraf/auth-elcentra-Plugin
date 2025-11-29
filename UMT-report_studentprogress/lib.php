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

defined('MOODLE_INTERNAL') || die();

/**
 * This function is called by Moodle to add items to the primary navigation.
 * This is the standard way to make a report visible to non-admins like Managers.
 *
 * @param \core_navigation\output\primary $navigation
 */
function report_studentprogress_extend_navigation(\core_navigation\output\primary $navigation) {
    if (has_capability('report/studentprogress:view', context_system::instance())) {
        $url = new moodle_url('/report/studentprogress/index.php');
        $navigation->add(
            get_string('pluginname', 'report_studentprogress'),
            $url,
            \core_navigation\output\primary::KEY_REPORT, // Puts the link under the "Reports" menu if one exists
            'studentprogressreport'
        );
    }
}

function report_studentprogress_get_semesters() {
    global $DB;
    $semesters = $DB->get_records_menu('course_categories', ['parent' => 0], 'name', 'id, name');
    return [0 => get_string('allsemesters', 'report_studentprogress')] + $semesters;
}

function report_studentprogress_get_schools() {
    global $DB;
    $sql = "SELECT cc.id, cc.name
            FROM {course_categories} cc
            JOIN {course_categories} p ON p.id = cc.parent
            WHERE p.parent = 0
            ORDER BY cc.name";
    $schools = $DB->get_records_sql_menu($sql);
    return [0 => get_string('allschools', 'report_studentprogress')] + $schools;
}
/**
 * Get all second-level categories (Schools) for a specific Semester.
 * If no semester is provided, it returns all schools.
 *
 * @param int $semesterid The ID of the parent semester category.
 * @return array Suitable for a moodleform select element.
 */
function report_studentprogress_get_schools_for_semester($semesterid) {
    global $DB;
    if (empty($semesterid)) {
        return report_studentprogress_get_schools();
    }
    $schools = $DB->get_records_menu('course_categories', ['parent' => $semesterid], 'name', 'id, name');
    return [0 => get_string('allschools', 'report_studentprogress')] + $schools;
}