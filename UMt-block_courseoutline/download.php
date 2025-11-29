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
 * Download page for the Course Outline block.
 *
 * @package    block_courseoutline
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

// Get parameters
$courseid = required_param('courseid', PARAM_INT);
$fileid = optional_param('fileid', 0, PARAM_INT);

// Verify course exists
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

// Set up page context
require_login($course);
$context = context_course::instance($courseid);

// Check capabilities
require_capability('block/courseoutline:view', $context);

// Get course outline record
$outline = $DB->get_record('block_courseoutline', ['courseid' => $courseid]);
if (!$outline) {
    print_error('error_file_not_found', 'block_courseoutline');
}

// If fileid is specified, verify it matches the outline
if ($fileid && $fileid != $outline->fileid) {
    print_error('error_file_not_found', 'block_courseoutline');
}

// Get the file
$fs = get_file_storage();
$file = $fs->get_file_by_id($outline->fileid);

if (!$file || $file->is_directory()) {
    print_error('error_file_not_found', 'block_courseoutline');
}

// Serve the file
send_stored_file($file, 0, 0, true);

