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
require_once('../../config.php');
require_once($CFG->libdir . '/tcpdf/tcpdf.php'); 
require_once('classes/report.php');

$semesterid = optional_param('semesterid', 0, PARAM_INT);
$schoolid = optional_param('schoolid', 0, PARAM_INT);
require_login();
$context = context_system::instance();
require_capability('report/studentprogress:view', $context);
@set_time_limit(0); 
@ini_set('memory_limit', '512M'); 

try {
    $filename = 'student_progress_report_' . date('Y-m-d') . '.pdf';
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Name/Organization');
    $pdf->SetTitle(get_string('reporttitle', 'report_studentprogress'));
    $pdf->SetSubject('Student Progress Report');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(TRUE, 10);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->AddPage();
    $report = new \report_studentprogress\report($semesterid, $schoolid);
    $headers = $report->get_headers();
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, get_string('reporttitle', 'report_studentprogress'), 0, 1, 'C');
    $pdf->Ln(5);
    $headerHtml = '<style>
        th { font-weight: bold; background-color: #f2f2f2; border: 1px solid #ccc; padding: 4px; text-align: center; }
        .header1 { background-color: #eaf7e9; }
        .header2 { background-color: #fef2cb; }
    </style>
    <table border="1" cellpadding="4" cellspacing="0">
        <thead>
            <tr>
                <th colspan="5" class="header1">' . get_string('header_studentinfo', 'report_studentprogress') . '</th>
                <th colspan="2" class="header2">' . get_string('header_engagement', 'report_studentprogress') . '</th>
            </tr>
            <tr>';
    foreach ($headers as $header) {
        $headerHtml .= '<th>' . htmlspecialchars($header) . '</th>';
    }
    $headerHtml .= '</tr></thead></table>'; 
    $pdf->SetFont('helvetica', '', 8);
    $pdf->writeHTML($headerHtml, true, false, true, false, '');
    $recordset = $report->get_full_recordset();
    $html = ''; 
    $rowCount = 0;
    $rowsPerChunk = 100;

    foreach ($recordset as $row) {
        $fullname = $row->fullname_agg ?? '';
        $parts = explode(' - ', $fullname);
        $coursetitle = (count($parts) >= 2) ? trim($parts[1]) : 'N/A';
        $section = (count($parts) >= 4) ? trim($parts[count($parts) - 2]) . ' - ' . trim($parts[count($parts) - 1]) : 'N/A';

        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row->studentid ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars(fullname($row)) . '</td>';
        $html .= '<td>' . htmlspecialchars($coursetitle) . '</td>';
        $html .= '<td>' . htmlspecialchars($section) . '</td>';
        $html .= '<td>' . htmlspecialchars($row->faculty ?? '') . '</td>';
        $html .= '<td>' . $report->format_time($row->total_time_spent) . '</td>';
        $html .= '<td>' . htmlspecialchars($row->number_of_accesses ?? 0) . '</td>';
        $html .= '</tr>';

        $rowCount++;
        if ($rowCount % $rowsPerChunk == 0) {
            $tableChunk = '<style>td { border: 1px solid #ccc; padding: 4px; text-align: center; }</style>
                           <table border="1" cellpadding="4" cellspacing="0">' . $html . '</table>';
            $pdf->writeHTML($tableChunk, true, false, true, false, '');
            $html = ''; 
        }
    }
    $recordset->close();
    if (!empty($html)) {
        $tableChunk = '<style>td { border: 1px solid #ccc; padding: 4px; text-align: center; }</style>
                       <table border="1" cellpadding="4" cellspacing="0">' . $html . '</table>';
        $pdf->writeHTML($tableChunk, true, false, true, false, '');
    }

    $pdf->Output($filename, 'D');
    exit;

} catch (Exception $e) {
    echo 'An error occurred while generating the PDF: ',  $e->getMessage();
}