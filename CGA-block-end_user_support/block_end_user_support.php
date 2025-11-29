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
 * Course summary block
 *
 * @package    block_course_summary
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot. '/blocks/end_user_support/lib.php');

class block_end_user_support extends block_base{
    function init() {
        $this->title = get_string('pluginname', 'block_end_user_support');
    }
    /**
     * @var bool Flag to indicate whether the header should be hidden or not.
     */

    private $headerhidden = true;


        function get_content()
        {
            global $USER, $PAGE;
            if ($this->content !== null) {
                return $this->content;
            }
            $this->content = new stdClass;
            $this->content->header = get_string('pluginname', 'block_end_user_support');
            $this->content->text = '';
            $this->content->text .= '<h2>NEED HELP?</h2>';
            $this->content->text .= '<P>If you need any sort of help in regards to your training in this portal please:</P>';
            $button = block_end_user_support_modal();
            $this->content->text .= $button;
            $this->content->footer = '';

            return $this->content;
        }



        function hide_header()
        {
            return $this->headerhidden;
        }

}


