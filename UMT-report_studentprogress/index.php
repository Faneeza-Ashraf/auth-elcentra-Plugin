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
require_once('lib.php');
require_once('filter_form.php');

require_login();
$context = context_system::instance();
require_capability('report/studentprogress:view', $context);

$PAGE->set_context($context);
$PAGE->set_url('/report/studentprogress/index.php');
$PAGE->set_title(get_string('pluginname', 'report_studentprogress'));
$PAGE->set_heading(get_string('reporttitle', 'report_studentprogress'));

$PAGE->requires->css(new moodle_url('/report/studentprogress/style.css'));

$page = optional_param('page', 0, PARAM_INT);
$perpage = 20;
$format = optional_param('format', 'html', PARAM_ALPHA);

$filterform = new filter_form();

if ($fromform = $filterform->get_data()) {
    $semesterid = $fromform->semesterid;
    $schoolid = $fromform->schoolid;
} else {
    $semesterid = optional_param('semesterid', 0, PARAM_INT);
    $schoolid = optional_param('schoolid', 0, PARAM_INT);
}

$showreport = $filterform->is_submitted() || !empty($semesterid) || !empty($schoolid);
$filterform->set_data(compact('semesterid', 'schoolid'));
$baseurl = new moodle_url('/report/studentprogress/index.php', compact('semesterid', 'schoolid'));

if ($format !== 'html' && $showreport) {
    switch ($format) {
        case 'csv':
            require_once('export_csv.php');
            break;
        case 'excel':
            require_once('export_excel.php');
            break;
        case 'pdf':
            require_once('export_pdf.php');
            break;
    }
    exit;
}

echo $OUTPUT->header();
require_once('view.php');
echo $OUTPUT->footer();