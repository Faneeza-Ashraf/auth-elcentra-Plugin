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
require_once('classes/report.php');

$semesterid = optional_param('semesterid', 0, PARAM_INT);
$schoolid = optional_param('schoolid', 0, PARAM_INT);
require_login();
$context = context_system::instance();
require_capability('report/studentprogress:view', $context);

set_time_limit(0); 

$report = new \report_studentprogress\report($semesterid, $schoolid);
$headers = $report->get_headers();

$category_row = [
    get_string('header_studentinfo', 'report_studentprogress'), '', '', '', '',
    get_string('header_engagement', 'report_studentprogress'), ''
];

$filename = 'student_progress_report_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($output, $category_row);
fputcsv($output, $headers);

$recordset = $report->get_full_recordset();
foreach ($recordset as $row) {
    $fullname = $row->fullname_agg ?? '';
    $parts = explode(' - ', $fullname);
    $coursetitle = (count($parts) >= 2) ? trim($parts[1]) : 'N/A';
    $section = (count($parts) >= 4) ? trim($parts[count($parts) - 2]) . ' - ' . trim($parts[count($parts) - 1]) : 'N/A';

    $csv_row = [
        $row->studentid ?? '',
        fullname($row),
        $coursetitle,
        $section,
        $row->faculty ?? '',
        $report->format_time($row->total_time_spent),
        $row->number_of_accesses ?? 0
    ];
    fputcsv($output, $csv_row);
}
$recordset->close();

fclose($output);
exit;