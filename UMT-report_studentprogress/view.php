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

echo html_writer::start_div('report-container');
echo html_writer::start_div('report-filters');
$filterform->display();
echo html_writer::end_div();

if ($showreport) {
    $report = new \report_studentprogress\report($semesterid, $schoolid, $page, $perpage);
    $headers = $report->get_headers();
    $data = $report->get_formatted_data();
    $totalcount = $report->get_total_count();

    echo html_writer::start_div('report-action-bar');
    echo html_writer::start_div('export-buttons');
    
    $export_params = ['semesterid' => $semesterid, 'schoolid' => $schoolid];
    $csv_url = new moodle_url('/report/studentprogress/index.php', array_merge($export_params, ['format' => 'csv']));
    $excel_url = new moodle_url('/report/studentprogress/index.php', array_merge($export_params, ['format' => 'excel']));
    $pdf_url = new moodle_url('/report/studentprogress/index.php', array_merge($export_params, ['format' => 'pdf']));

    echo html_writer::link($csv_url, get_string('exportcsv', 'report_studentprogress'), ['class' => 'btn btn-secondary']);
    echo html_writer::link($excel_url, get_string('exportexcel', 'report_studentprogress'), ['class' => 'btn btn-secondary']);
    echo html_writer::link($pdf_url, get_string('exportpdf', 'report_studentprogress'), ['class' => 'btn btn-secondary']);
    
    echo html_writer::end_div();
    echo html_writer::end_div();

    if (empty($data)) {
        echo $OUTPUT->notification(get_string('nodata', 'report_studentprogress'));
    } else {
        echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
        echo html_writer::start_div('table-responsive');
        $tableclasses = 'generaltable table table-striped table-bordered olarcusage-table';
        echo html_writer::start_tag('table', ['class' => $tableclasses]);
        echo html_writer::start_tag('thead');
        echo html_writer::start_tag('tr');
        echo html_writer::tag('th', get_string('header_studentinfo', 'report_studentprogress'), ['colspan' => 5, 'class' => 'category-header1']);
        echo html_writer::tag('th', get_string('header_engagement', 'report_studentprogress'), ['colspan' => 2, 'class' => 'category-header2']);
        echo html_writer::end_tag('tr');
        echo html_writer::start_tag('tr');
        foreach ($headers as $header) {
            echo html_writer::tag('th', $header);
        }
        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('thead');
        echo html_writer::start_tag('tbody');
        foreach ($data as $datarow) {
            echo html_writer::start_tag('tr');
            foreach ($datarow as $cell) {
                echo html_writer::tag('td', $cell);
            }
            echo html_writer::end_tag('tr');
        }
        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');
        echo html_writer::end_div();
        echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
    }
}


echo html_writer::end_div();