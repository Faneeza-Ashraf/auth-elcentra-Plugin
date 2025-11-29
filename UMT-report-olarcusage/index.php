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
 * Main entry point for the Custom Report plugin.
 *
 * @package    report_olarcusage
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
$id = optional_param('id', 0, PARAM_INT);
$format = optional_param('format', 'html', PARAM_ALPHA);
$page = optional_param('page', 0, PARAM_INT);
$perpage = 20; 
if ($id) {
    $course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
    require_login($course);
    $context = context_course::instance($course->id);
    require_capability('report/olarcusage:view', $context);
    $PAGE->set_context($context);
    $PAGE->set_course($course);
    $PAGE->set_url('/report/olarcusage/index.php', ['id' => $id]);
    $PAGE->set_title(get_string('reporttitle', 'report_olarcusage'));
    $PAGE->set_heading($course->fullname);
} else {
    require_login();
    $context = context_system::instance();
    require_capability('report/olarcusage:view', $context);
    $PAGE->set_context($context);
    $PAGE->set_url('/report/olarcusage/index.php');
    $PAGE->set_title(get_string('reporttitle', 'report_olarcusage'));
    $PAGE->set_heading(get_string('reporttitle', 'report_olarcusage'));
}
require_once('filter_form.php');
$filterform = new filter_form();

if ($fromform = $filterform->get_data()) {
    $semesterid = $fromform->semesterid;
    $schoolid = $fromform->schoolid;
    $reporttype = $fromform->reporttype;
} else {
    $semesterid = optional_param('semesterid', 0, PARAM_INT);
    $schoolid = optional_param('schoolid', 0, PARAM_INT);
    $reporttype = optional_param('reporttype', 'all', PARAM_ALPHA);
}
$filterform->set_data(compact('semesterid', 'schoolid', 'reporttype'));
$showreport = $filterform->is_submitted() || !empty($id) || isset($_REQUEST['semesterid']) || isset($_REQUEST['schoolid']);
$baseurl = new moodle_url('/report/olarcusage/index.php', [
    'semesterid' => $semesterid,
    'schoolid' => $schoolid,
    'reporttype' => $reporttype
]);
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
        default:
            print_error('invalidformat', 'report_olarcusage');
    }
    exit;
}
echo $OUTPUT->header();
require_once('view.php');
$PAGE->requires->js_init_call('M.report_olarcusage.init', [sesskey()]);
echo $OUTPUT->footer();