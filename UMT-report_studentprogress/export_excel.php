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
require_once($CFG->libdir . '/excellib.class.php');
require_once('classes/report.php');

$semesterid = optional_param('semesterid', 0, PARAM_INT);
$schoolid = optional_param('schoolid', 0, PARAM_INT);
require_login();
$context = context_system::instance();
require_capability('report/studentprogress:view', $context);

set_time_limit(0);

$report = new \report_studentprogress\report($semesterid, $schoolid);
$headers = $report->get_headers();

$column_widths = [15, 30, 40, 30, 20, 15, 15];
$filename = 'student_progress_report_' . date('Y-m-d') . '.xlsx';
$workbook = new \MoodleExcelWorkbook($filename);
$worksheet = $workbook->add_worksheet('Student Progress Report');

$headerformat = $workbook->add_format(['bold' => 1, 'align' => 'center', 'valign' => 'vcenter']);
$greenformat = $workbook->add_format(['bold' => 1, 'align' => 'center', 'valign' => 'vcenter', 'bg_color' => '#eaf7e9', 'color' => 'black']);
$yellowformat = $workbook->add_format(['bold' => 1, 'align' => 'center', 'valign' => 'vcenter', 'bg_color' => '#fef2cb']);
$cell_format = $workbook->add_format(['valign' => 'vcenter']);

$worksheet->merge_cells(0, 0, 0, 4);
$worksheet->write_string(0, 0, get_string('header_studentinfo', 'report_studentprogress'), $greenformat);
$worksheet->merge_cells(0, 5, 0, 6);
$worksheet->write_string(0, 5, get_string('header_engagement', 'report_studentprogress'), $yellowformat);

foreach ($headers as $index => $header) {
    $worksheet->write_string(1, $index, $header, $headerformat);
}

foreach ($column_widths as $index => $width) {
    $worksheet->set_column($index, $index, $width);
}

$row_num = 2;
$recordset = $report->get_full_recordset();
foreach ($recordset as $row) {
    $fullname = $row->fullname_agg ?? '';
    $parts = explode(' - ', $fullname);
    $coursetitle = (count($parts) >= 2) ? trim($parts[1]) : '';
    $section = (count($parts) >= 4) ? trim($parts[count($parts) - 2]) . ' - ' . trim($parts[count($parts) - 1]) : 'N/A';
    
    $worksheet->write($row_num, 0, $row->studentid ?? '', $cell_format);
    $worksheet->write($row_num, 1, fullname($row), $cell_format);
    $worksheet->write($row_num, 2, $coursetitle, $cell_format);
    $worksheet->write($row_num, 4, $section, $cell_format);
    $worksheet->write($row_num, 3, $row->faculty ?? '', $cell_format);
    $worksheet->write($row_num, 5, $report->format_time($row->total_time_spent), $cell_format);
    $worksheet->write($row_num, 6, $row->number_of_accesses ?? 0, $cell_format);
    $row_num++;
}
$recordset->close();

$workbook->close();
exit;