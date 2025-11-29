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
 * @package    report_studentprogress
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class filter_form extends moodleform {
    public function definition() {
        global $CFG;
        $mform = $this->_form;

        $semesterid = $this->_customdata['semesterid'] ?? 0;

        $mform->addElement('select', 'semesterid', get_string('semesterid', 'report_studentprogress'),
            report_studentprogress_get_semesters());
        $mform->setDefault('semesterid', $semesterid);

        $schools = report_studentprogress_get_schools_for_semester($semesterid);
        $mform->addElement('select', 'schoolid', get_string('schoolid', 'report_studentprogress'), $schools);
        $mform->setDefault('schoolid', $this->_customdata['schoolid'] ?? 0);

        $this->add_action_buttons(false, get_string('generate_report', 'report_studentprogress'));
    }
    function definition_after_data() {
        $this->_customdata['semesterid'] = $this->get_submitted_data()->semesterid ?? 0;
        $this->_customdata['schoolid'] = $this->get_submitted_data()->schoolid ?? 0;
    }
}