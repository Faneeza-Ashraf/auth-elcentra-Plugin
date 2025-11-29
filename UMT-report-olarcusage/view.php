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
 * View file for displaying the Custom Report.
 *
 * @package    report_olarcusage
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/report/olarcusage/classes/report.php');
echo html_writer::start_div('report-filters');
$filterform->display();
echo html_writer::end_div();
echo html_writer::start_tag('style');
echo "
.report-filters {
    background-color: #f8f9fa;
    padding: 20px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 30px;
}
.report-filters h2 {
    margin-top: 0;
    font-size: 1.5em;
    font-weight: 600;
}
.report-filters .mform {
    display: flex;
    align-items: flex-end;
    gap: 15px;
    flex-wrap: wrap;
}
.report-filters .fitem { margin-bottom: 0 !important; }
.report-filters .felement { margin-bottom: 50px !important; }
.report-filters .fitem .form-label { font-weight: 500; }
.report-filters #fitem_id_submitbutton { margin-left: 5px; }
";
echo html_writer::end_tag('style');
if ($showreport) {
    $report = new \report_olarcusage\report($id, $semesterid, $schoolid, $reporttype, $page, $perpage);
    $headers = $report->get_headers();
    $data = $report->get_formatted_data();
    $totalcount = $report->get_total_count();
    echo html_writer::start_div('export-buttons', ['style' => 'margin-bottom: 20px;']);
    echo html_writer::tag('h3', get_string('reporttitle', 'report_olarcusage'));

    $export_url_csv     = new moodle_url('/report/olarcusage/index.php', ['id' => $id, 'format' => 'csv', 'semesterid' => $semesterid, 'schoolid' => $schoolid, 'reporttype' => $reporttype]);
    $export_url_excel   = new moodle_url('/report/olarcusage/index.php', ['id' => $id, 'format' => 'excel', 'semesterid' => $semesterid, 'schoolid' => $schoolid, 'reporttype' => $reporttype]);
    $export_url_pdf     = new moodle_url('/report/olarcusage/index.php', ['id' => $id, 'format' => 'pdf', 'semesterid' => $semesterid, 'schoolid' => $schoolid, 'reporttype' => $reporttype]);

    echo html_writer::link($export_url_csv, get_string('exportcsv', 'report_olarcusage'),
        ['class' => 'btn btn-secondary', 'style' => 'margin-right: 10px;']);
    echo html_writer::link($export_url_excel, get_string('exportexcel', 'report_olarcusage'),
        ['class' => 'btn btn-secondary', 'style' => 'margin-right: 10px;']);
    echo html_writer::link($export_url_pdf, get_string('exportpdf', 'report_olarcusage'),
        ['class' => 'btn btn-secondary']);

    echo html_writer::end_div();
    if ($totalcount > 0) {
        echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
    }
    if (empty($data)) {
        echo html_writer::tag('p', get_string('nodata', 'report_olarcusage'), ['class' => 'alert alert-info']);
    } else {
        $table = new html_table();
        $table->head = $headers;
        $table->data = $data;
        $table->attributes['class'] = 'generaltable table table-striped';
        $table->attributes['id'] = 'olarcusage-table';
        echo html_writer::start_div('table-responsive');
        echo html_writer::table($table);
        echo html_writer::end_div();
    }
    if ($totalcount > 0) {
        echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
    }
    echo html_writer::start_tag('script');
    echo "
    document.addEventListener('DOMContentLoaded', function() {
        var table = document.getElementById('olarcusage-table');
        if (table) {
            console.log('Custom report table loaded with ' + table.rows.length + ' rows');
        }
    });
    ";
    echo html_writer::end_tag('script');

    echo html_writer::start_tag('style');
    echo "
    .export-buttons { padding: 15px 0; border-bottom: 1px solid #ddd; }
    #olarcusage-table { font-size: 0.9em; }
    #olarcusage-table th { background-color: #f8f9fa; font-weight: bold; text-align: center; vertical-align: middle; }
    #olarcusage-table td { vertical-align: middle; word-wrap: break-word; max-width: 200px; }
    .table-responsive { margin-top: 20px; }
    @media (max-width: 768px) {
        #olarcusage-table { font-size: 0.8em; }
        #olarcusage-table td { max-width: 150px; }
    }
    ";
    echo html_writer::end_tag('style');

}