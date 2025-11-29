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
 * The file display the report of the plugin.
 *
 * @package    local_elegacy
 * @copyright  2023 Syed Zonair, Syed {@link http://paktaleem.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

require_login();

// PARAM.
$u      = optional_param('u', 0, PARAM_RAW);
$userid = explode('-', $u)[0];

$url = new moodle_url('/local/elegacy/report/view.php');
$title = get_string('report_single_title', 'local_elegacy');
$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

// If the parameter is non-numeric.
if (!is_numeric($userid)) {
    notice('You need to select the user correctly.', $url);
}

$output = $OUTPUT->header();

if (is_siteadmin()) {
    // SEARCH BAR.
    $output .= $OUTPUT->render_from_template('local_elegacy/searchbar', [
        'users' => array_values($DB->get_records('user', [], '', 'id, email, CONCAT(firstname," ",lastname) AS fullname')),
    ]);

    $report = new local_elegacy\report($userid);
    // Display the report.
    if ($userid > 0) {
        $output .= $report->display();
    }
} else {
    $report = new local_elegacy\report();
    $output .= $report->display();
}

echo $output;

echo $OUTPUT->footer();
