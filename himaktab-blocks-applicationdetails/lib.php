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
 * Defines the scheduled task for checking user subscriptions.
 *
 * @package    block
 * @subpackage applicationdetails
 *@copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Serves the files from the 'user_documents' file area.
 *
 * @param stdClass $course The course object
 * @param stdClass $cm The course module object
 * @param context $context The context
 * @param string $filearea The name of the file area
 * @param array $args The arguments
 * @param bool $forcedownload Whether to force a download
 * @param array $options Additional options
 * @return bool
 */
function block_applicationdetails_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {
    require_login();
    if ($context->contextlevel != CONTEXT_USER) {
        send_file_not_found();
    }
    require_capability('moodle/user:viewdetails', $context);
    if ($filearea !== 'user_documents') {
        send_file_not_found();
    }
    $itemid = array_shift($args);
    $filename = array_pop($args);
    $filepath = empty($args) ? '/' : '/' . implode('/', $args) . '/';
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'auth_emailadmin', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        send_file_not_found();
    }
    send_stored_file($file, 0, 0, $forcedownload, $options);
    return true;
}