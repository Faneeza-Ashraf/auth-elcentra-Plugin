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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/report/olarcusage/lib.php');

class school_filter_form extends moodleform {
    protected function definition() {
        $mform = $this->_form;
        $semesterid = $this->_customdata['semesterid'] ?? optional_param('semesterid', 0, PARAM_INT);
        $semesters = report_olarcusage_get_semesters();
        $mform->addElement('select', 'semesterid', get_string('filtersemester', 'report_olarcusage'), $semesters);
        $mform->setType('semesterid', PARAM_INT);
        $mform->addElement('submit', 'updateschools', get_string('filterschools', 'report_olarcusage'));
        if ($semesterid) {
            $schools = report_olarcusage_get_schools($semesterid);
            $mform->addElement('select', 'schoolid', get_string('filterschool', 'report_olarcusage'), $schools);
            $mform->setType('schoolid', PARAM_INT);
            $mform->addRule('schoolid', get_string('required'), 'required', null, 'client');
        }

        $this->add_action_buttons(false, get_string('generategraph', 'report_olarcusage'));
    }
}