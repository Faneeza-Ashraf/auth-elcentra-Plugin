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
 * PDF export functionality for the Custom Report plugin.
 *
 * @package    report_olarcusage
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/report/olarcusage/classes/report.php');

/**
 * Export report data as PDF
 *
 * @param int $courseid Course ID (0 for site-wide report)
 */
function export_pdf_report($courseid = 0) {
    global $CFG;
    if (class_exists('\pdf')) {
        export_pdf_with_moodle_pdf($courseid);
    } else {
        if (file_exists($CFG->libdir . '/tcpdf/tcpdf.php')) {
            export_pdf_with_tcpdf($courseid);
        } else {
            export_pdf_html_fallback($courseid);
        }
    }
}

/**
 * Export using Moodle's built-in PDF class
 *
 * @param int $courseid Course ID
 */
function export_pdf_with_moodle_pdf($courseid) {
    global $CFG;

    require_once($CFG->libdir . '/pdflib.php');
    $report = new \report_olarcusage\report($courseid);
    $headers = $report->get_headers();
    $data = $report->get_data();
    $filename = 'course_analytics_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf = new \pdf('L', 'mm', 'A4'); 
    $pdf->SetCreator('Moodle Custom Report Plugin');
    $pdf->SetAuthor('Moodle');
    $pdf->SetTitle('Course Analytics Report');
    $pdf->SetSubject('Course Analytics Report');
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Course Analytics Report', 0, 1, 'C');
    $pdf->Ln(5);
    $html = generate_pdf_table_html($headers, $data);
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($filename, 'D');
}

/**
 * Export using TCPDF
 *
 * @param int $courseid Course ID
 */
function export_pdf_with_tcpdf($courseid) {
    global $CFG;

    require_once($CFG->libdir . '/tcpdf/tcpdf.php');
    $report = new \report_olarcusage\report($courseid);
    $headers = $report->get_headers();
    $data = $report->get_data();
    $filename = 'course_analytics_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Moodle Custom Report Plugin');
    $pdf->SetAuthor('Moodle');
    $pdf->SetTitle('Course Analytics Report');
    $pdf->SetSubject('Course Analytics Report');
    $pdf->SetMargins(10, 15, 10);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Course Analytics Report', 0, 1, 'C');
    $pdf->Ln(5);
    $html = generate_pdf_table_html($headers, $data);
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($filename, 'D');
}

/**
 * Fallback PDF export using HTML conversion
 *
 * @param int $courseid Course ID
 */
function export_pdf_html_fallback($courseid) {
    $report = new \report_olarcusage\report($courseid);
    $headers = $report->get_headers();
    $data = $report->get_data();
    $filename = 'course_analytics_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Course Analytics Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .rotate { writing-mode: vertical-lr; text-orientation: mixed; }
    </style>
</head>
<body>
    <h1>Course Analytics Report</h1>
    ' . generate_pdf_table_html($headers, $data) . '
</body>
</html>';

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    if (shell_exec('which wkhtmltopdf')) {
        $temp_html = tempnam(sys_get_temp_dir(), 'report_') . '.html';
        $temp_pdf = tempnam(sys_get_temp_dir(), 'report_') . '.pdf';

        file_put_contents($temp_html, $html);

        $command = "wkhtmltopdf --page-size A4 --orientation Landscape '$temp_html' '$temp_pdf'";
        shell_exec($command);

        if (file_exists($temp_pdf)) {
            readfile($temp_pdf);
            unlink($temp_html);
            unlink($temp_pdf);
            return;
        }
    }
    echo $html;
}

/**
 * Generate HTML table for PDF export
 *
 * @param array $headers Table headers
 * @param array $data Table data
 * @return string HTML table
 */
function generate_pdf_table_html($headers, $data) {
    $html = '<table border="1" cellpadding="3" cellspacing="0">';
    $html .= '<thead><tr>';
    foreach ($headers as $header) {
        $html .= '<th style="background-color: #f2f2f2; font-weight: bold; text-align: center;">' .
                 htmlspecialchars($header) . '</th>';
    }
    $html .= '</tr></thead>';
    $html .= '<tbody>';
    foreach ($data as $row) {
        $html .= '<tr>';

        $pdf_row = [
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
            $row['total_teacher_time'],   //Added by Faneeza Muskan to add the total teacher time
            $row['teacher_access_count']  //Added by Faneeza Muskan to add the total teacher access count
        ];

        foreach ($pdf_row as $cell) {
            $html .= '<td>' . htmlspecialchars($cell) . '</td>';
        }

        $html .= '</tr>';
    }
    $html .= '</tbody>';

    $html .= '</table>';

    return $html;
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

export_pdf_report($id);

