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
 * Excel export functionality for the Custom Report plugin.
 *
 * @package    report_olarcusage
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/report/olarcusage/classes/report.php');

/**
 * Export report data as Excel
 *
 * @param int $courseid Course ID (0 for site-wide report)
 */
function export_excel_report($courseid = 0) {
    global $CFG;
    if (class_exists('\MoodleExcelWorkbook')) {
        export_excel_with_moodle_writer($courseid);
    } else {
        export_excel_xml_format($courseid);
    }
}

/**
 * Export using Moodle's built-in Excel writer
 *
 * @param int $courseid Course ID
 */
function export_excel_with_moodle_writer($courseid) {
    global $CFG;

    require_once($CFG->libdir . '/excellib.class.php');
    $report = new \report_olarcusage\report($courseid);
    $headers = $report->get_headers();
    $data = $report->get_data();
    $filename = 'course_analytics_report_' . date('Y-m-d_H-i-s') . '.xls';
    $workbook = new \MoodleExcelWorkbook($filename);
    $worksheet = $workbook->add_worksheet('Course Analytics');
    $header_format = $workbook->add_format();
    $header_format->set_bold(1);
    $header_format->set_bg_color('silver');
    $header_format->set_align('center');
    $cell_format = $workbook->add_format();
    $cell_format->set_text_wrap();
    $col = 0;
    
    foreach ($headers as $header) {
        $worksheet->write(0, $col, $header, $header_format);
        $worksheet->set_column($col, $col, 15); 
        $col++;
    }

    $row = 1;
    foreach ($data as $datarow) {
        $col = 0;
        $excel_row = [
            $datarow['semester'],
            $datarow['school'],
            $datarow['program'],
            $datarow['course_code'],
            $datarow['course_title'],
            $datarow['faculty'],
            $datarow['email'],
            $datarow['section'],
            $datarow['assignments_given'],
            $datarow['quizzes_taken'],
            $datarow['chats'],
            $datarow['learning_material_uploaded'],
            $datarow['course_outline_added'],
            $datarow['students_on_lms'],
            format_usage_status_for_export($datarow['lms_usage_status']),
            $datarow['recording_link'],
            $datarow['course_link'],
            $datarow['template_status'],
            $datarow['usage_activity_percent'] . '%',
            $datarow['total_teacher_time'], //Added by Faneeza Muskan to add the total teacher time
            $datarow['teacher_access_count'] //Added by Faneeza Muskan to add the total teacher access count
        ];

        foreach ($excel_row as $cell_value) {
            $worksheet->write($row, $col, $cell_value, $cell_format);
            $col++;
        }
        $row++;
    }

    $workbook->close();
}

/**
 * Export using Excel XML format (fallback)
 *
 * @param int $courseid Course ID
 */
function export_excel_xml_format($courseid) {
    // Create report instance
    $report = new \report_olarcusage\report($courseid);
    $headers = $report->get_headers();
    $data = $report->get_data();
    $filename = 'course_analytics_report_' . date('Y-m-d_H-i-s') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
    echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
    echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
    echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
    echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
    echo '<Styles>' . "\n";
    echo '<Style ss:ID="Header">' . "\n";
    echo '<Font ss:Bold="1"/>' . "\n";
    echo '<Interior ss:Color="#C0C0C0" ss:Pattern="Solid"/>' . "\n";
    echo '<Alignment ss:Horizontal="Center"/>' . "\n";
    echo '</Style>' . "\n";
    echo '</Styles>' . "\n";
    echo '<Worksheet ss:Name="Course Analytics">' . "\n";
    echo '<Table>' . "\n";
    echo '<Row>' . "\n";
    foreach ($headers as $header) {
        echo '<Cell ss:StyleID="Header"><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>' . "\n";
    }
    echo '</Row>' . "\n";

    foreach ($data as $datarow) {
        echo '<Row>' . "\n";

        $excel_row = [
            $datarow['semester'],
            $datarow['school'],
            $datarow['program'],
            $datarow['course_code'],
            $datarow['course_title'],
            $datarow['faculty'],
            $datarow['email'],
            $datarow['section'],
            $datarow['assignments_given'],
            $datarow['quizzes_taken'],
            $datarow['chats'],
            $datarow['learning_material_uploaded'],
            $datarow['course_outline_added'],
            $datarow['students_on_lms'],
            format_usage_status_for_export($datarow['lms_usage_status']),
            $datarow['recording_link'],
            $datarow['course_link'],
            $datarow['template_status'],
            $datarow['usage_activity_percent'] . '%',
            $datarow['total_teacher_time'],  //Added by Faneeza Muskan to add the total teacher time
            $datarow['teacher_access_count'] //Added by Faneeza Muskan to add the total teacher access count
        ];

        foreach ($excel_row as $cell_value) {
            if (is_numeric($cell_value) && strpos($cell_value, '%') === false) {
                echo '<Cell><Data ss:Type="Number">' . htmlspecialchars($cell_value) . '</Data></Cell>' . "\n";
            } else {
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($cell_value) . '</Data></Cell>' . "\n";
            }
        }

        echo '</Row>' . "\n";
    }

    echo '</Table>' . "\n";
    echo '</Worksheet>' . "\n";
    echo '</Workbook>' . "\n";
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
export_excel_report($id);

