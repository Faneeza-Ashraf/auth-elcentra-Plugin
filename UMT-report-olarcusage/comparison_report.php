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
// --- THIS IS THE CRITICAL FIX ---
// This line ensures that all our custom functions are loaded and available.
require_once('lib.php');

// Set up the page.
require_login();
$context = context_system::instance();
require_capability('report/olarcusage:view', $context);

$PAGE->set_context($context);
$PAGE->set_url('/report/olarcusage/comparison_report.php');
$PAGE->set_title(get_string('comparisonreporttitle', 'report_olarcusage'));
$PAGE->set_heading(get_string('comparisonreporttitle', 'report_olarcusage'));

// Get the data for the chart.
$chartdata = report_olarcusage_get_semester_comparison_data();

// Display the page header.
echo $OUTPUT->header();

// Get the plugin's renderer.
$output = $PAGE->get_renderer('report_olarcusage');

// Let the renderer display the page.
echo $output->render_comparison_report_page($chartdata);

// Call the JavaScript to draw the chart.
if (!empty($chartdata)) {
    $PAGE->requires->js_init_call('M.report_olarcusage.init_comparison_chart', [$chartdata]);
}

// Display the page footer.
echo $OUTPUT->footer();