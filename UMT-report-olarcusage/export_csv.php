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
 * CSV export functionality for the Custom Report plugin.
 *
 * @package    report_olarcusage
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/report/olarcusage/classes/report.php');

/**
 * Export report data as CSV
 *
 * @param int $courseid Course ID (0 for site-wide report)
 */
function export_csv_report($courseid = 0) {
    // Create report instance
    $report = new \report_olarcusage\report($courseid);

    // Get report data
    $headers = $report->get_headers();
    $data = $report->get_data();

    // Generate filename
    $filename = 'course_analytics_report_' . date('Y-m-d_H-i-s') . '.csv';

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add BOM for UTF-8 (helps with Excel compatibility)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Write headers
    fputcsv($output, $headers);

    // Write data rows
    foreach ($data as $row) {
        $csv_row = [
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
            format_usage_status_for_export($row['lms_usage_status']),
            $row['recording_link'],
            $row['course_link'],
            $row['template_status'],
            $row['usage_activity_percent'] . '%',
            $row['total_teacher_time'],  //Added by Faneeza Muskan to add the total teacher time
            $row['teacher_access_count'] //Added by Faneeza Muskan to add the total teacher access count
        ];

        fputcsv($output, $csv_row);
    }

    fclose($output);
}

/**
 * Format usage status for export
 *
 * @param int $status Usage status code
 * @return string Formatted status
 */
function format_usage_status_for_export($status) {
    switch ($status) {
        case 1:
            return '1 - No activity in course';
        case 2:
            return '2 - Some topics have no activity';
        case 3:
            return '3 - All topics have activity';
        default:
            return $status;
    }
}

// Execute the export
export_csv_report($id);
