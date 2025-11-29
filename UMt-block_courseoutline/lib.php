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
 * File serving function for the Course Outline block.
 *
 * @package    block_courseoutline
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Serves the files from the courseoutline file area.
 *
 * @param stdClass $course The course object
 * @param stdClass $cm The course module object
 * @param stdClass $context The context
 * @param string $filearea The name of the file area
 * @param array $args The arguments
 * @param bool $forcedownload Whether to force a download
 * @param array $options Additional options
 * @return bool|void
 */
function block_courseoutline_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $DB, $USER;

    // Check that the context is a course context.
    if ($context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    // Check filearea.
    if ($filearea !== 'outline') {
        return false;
    }

    // Make sure the user is logged in and has permission to view the outline.
    require_login($course, true, $cm);
    if (!has_capability('block/courseoutline:view', $context)) {
        return false;
    }

    // The itemid for this plugin is the course ID.
    $courseid = (int)array_shift($args);
    if ($courseid !== $course->id) {
        // Trying to access an outline from a different course context.
        return false;
    }

    // Get the outline record from the database.
    $outline = $DB->get_record('block_courseoutline', ['courseid' => $courseid]);
    if (!$outline) {
        send_file_not_found();
    }

    // Get the file from the file storage.
    $fs = get_file_storage();
    $file = $fs->get_file_by_id($outline->fileid);

    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    // Serve the file to the user.
    send_stored_file($file, 0, 0, $forcedownload, $options);
}