<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The main mod_presentation configuration form.
 *
 * @package     mod_presentation
 * @copyright   2025 Endush Fairy <endush.fairy@paktaleem.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->libdir.'/formslib.php');
class mod_presentation_mod_form extends moodleform_mod {

    public function definition() {
        global $COURSE;
        $mform = $this->_form;

        // General Settings
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('presentationname', 'mod_presentation'), ['size'=>'64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $this->standard_intro_elements();

        // Availability Settings
        $mform->addElement('header', 'availability', get_string('availability', 'assign'));
        $mform->addElement('date_time_selector', 'allowsubmissionsfromdate', get_string('allowsubmissionsfromdate', 'assign'), ['optional'=>true]);
        $mform->addElement('date_time_selector', 'duedate', get_string('duedate', 'assign'), ['optional'=>true]);
        $mform->addElement('date_time_selector', 'cutoffdate', get_string('cutoffdate', 'assign'), ['optional'=>true]);

        // Submission Settings
        $mform->addElement('header', 'submissionsettings', get_string('submissionsettings', 'mod_presentation'));
        $mform->addElement('select', 'maxfiles', get_string('maxfiles', 'mod_presentation'), array_combine(range(1, 20), range(1, 20)));
        $mform->setDefault('maxfiles', 1);
        $choices = get_max_upload_sizes($COURSE->maxbytes);
        $mform->addElement('select', 'maxsize', get_string('maxsize', 'mod_presentation'), $choices);

        // Use Moodle's standard grading UI so grade/gradepass behave correctly.
        // This creates the proper 'grade' modgrade element and 'gradepass' handling.
        $this->standard_grading_coursemodule_elements();

        // Blind marking
        $mform->addElement('selectyesno', 'blindmarking', get_string('blindmarking', 'assign'));
        $mform->addHelpButton('blindmarking', 'blindmarking', 'assign');

        // Hide grader identity
        $mform->addElement('selectyesno', 'hidegrader', get_string('hidegrader', 'assign'));
        $mform->addHelpButton('hidegrader', 'hidegrader', 'assign');

        // Marking workflow
        $mform->addElement('selectyesno', 'markingworkflow', get_string('markingworkflow', 'assign'));
        $mform->addHelpButton('markingworkflow', 'markingworkflow', 'assign');

        // Marking allocation
        $mform->addElement('selectyesno', 'markingallocation', get_string('markingallocation', 'assign'));
        $mform->addHelpButton('markingallocation', 'markingallocation', 'assign');
        $mform->hideIf('markingallocation', 'markingworkflow', 'eq', 0); // Hide if workflow is 'No'

        $this->standard_coursemodule_elements();
        // Apply any admin defaults (for grading defaults etc.) when creating a new instance.
        $this->apply_admin_defaults();
        $this->add_action_buttons();
    }

}