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
require_once('graphical_filter_form.php');

require_login();
$context = context_system::instance();
require_capability('report/olarcusage:view', $context);

$PAGE->set_context($context);
$PAGE->set_url('/report/olarcusage/graphical_report.php');
$PAGE->set_title(get_string('graphicalreporttitle', 'report_olarcusage'));
$PAGE->set_heading(get_string('graphicalreporttitle', 'report_olarcusage'));

$filterform = new graphical_filter_form();
$showchart = false;
$semesterid = 0;

if ($fromform = $filterform->get_data()) {
    $semesterid = $fromform->semesterid;
    if ($semesterid > 0) {
        $showchart = true;
    }
}
$filterform->set_data(['semesterid' => $semesterid]);

$output = $PAGE->get_renderer('report_olarcusage');
echo $OUTPUT->header();
echo $output->render_graphical_report_page($filterform, $showchart, $semesterid);

if ($showchart && $semesterid > 0) {
    $PAGE->requires->js_init_call('M.report_olarcusage.init_bar_chart', [[
        'semesterid' => $semesterid,
        'sesskey' => sesskey()
    ]]);
}

echo $OUTPUT->footer();