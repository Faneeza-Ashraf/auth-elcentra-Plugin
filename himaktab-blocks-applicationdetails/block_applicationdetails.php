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

class block_applicationdetails extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_applicationdetails');
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        global $USER, $PAGE;

        if ($PAGE->context->contextlevel != CONTEXT_USER) {
            return null;
        }
        $profileuserid = $PAGE->context->instanceid;

        $canview = ($profileuserid == $USER->id) || has_capability('moodle/user:viewdetails', $PAGE->context);

        if (!$canview) {
            return null;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        $url = new moodle_url('/blocks/applicationdetails/view.php', ['id' => $profileuserid]);

        $linktext = get_string('viewdetailslink', 'block_applicationdetails');
        $this->content->text = html_writer::link($url, $linktext);

        return $this->content;
    }

    public function applicable_formats() {
        return ['user-profile' => true];
    }
}