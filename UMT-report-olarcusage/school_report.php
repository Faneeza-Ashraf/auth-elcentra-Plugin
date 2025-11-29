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
 * @package    report_olarcusage
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../config.php');
require_once('school_filter_form.php'); 
require_login();
$context = context_system::instance();
require_capability('report/olarcusage:view', $context);

$PAGE->set_context($context);
$PAGE->set_url('/report/olarcusage/school_report.php');
$PAGE->set_title(get_string('schoolreporttitle', 'report_olarcusage'));
$PAGE->set_heading(get_string('schoolreporttitle', 'report_olarcusage'));
$currenturl = new moodle_url('/report/olarcusage/school_report.php');
$filterform = new school_filter_form($currenturl);

$chartdata = null;
$selectedschool = null;
$semesterid = $filterform->get_data() ? $filterform->get_data()->semesterid : 0;
$schoolid = $filterform->get_data() ? $filterform->get_data()->schoolid : 0;

if ($filterform->is_cancelled()) {
    redirect($currenturl);
} else if ($fromform = $filterform->get_data()) {
    if (!empty($fromform->schoolid)) {
        $selectedschool = $DB->get_field('course_categories', 'name', ['id' => $fromform->schoolid]);
        $chartdata = report_olarcusage_get_program_usage_data($fromform->schoolid);
    }
}
echo $OUTPUT->header();
$output = $PAGE->get_renderer('report_olarcusage');
echo $output->render_school_report_page($filterform, $chartdata, $selectedschool);
if (!empty($chartdata)) {
    $PAGE->requires->js_init_call('M.report_olarcusage.init_school_chart', [$chartdata]);
}
echo $OUTPUT->footer();