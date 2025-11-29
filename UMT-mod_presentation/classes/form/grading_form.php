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
 * Grading form for presentation module
 *
 * @package     mod_presentation
 * @copyright   2025 Endush Fairy <endush.fairy@paktaleem.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_presentation\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

class grading_form extends \moodleform {

    public function definition() {
        global $DB;
        $mform = $this->_form;

        // Get the presentation settings passed in from grade.php
        $presentation = $this->_customdata['presentation'];

        // Hidden field for the submission ID.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);

        // Dynamic Grade Element
        if ($presentation->grade > 0) {
            // Point-based Grading (e.g., 0-100).
            $maxgrade = (int)$presentation->grade;
            $gradearray = array_combine(range(0, $maxgrade), range(0, $maxgrade));
            $mform->addElement('select', 'grade', get_string('grade', 'mod_presentation'), $gradearray);

        } else if ($presentation->grade < 0) {
            // Scale-based Grading.
            $scaleid = -$presentation->grade;

            // Check for "Separate and connected ways of knowing" (Standard Moodle ID is 2).
            if ($scaleid == 2) {
                $scale_options = [
                    '0' => get_string('nograde', 'mod_presentation'),
                    '3' => get_string('mostlyconnectedknowing', 'mod_presentation'),
                    '2' => get_string('separateandconnected', 'mod_presentation'),
                    '1' => get_string('mostlyseparateknowing', 'mod_presentation'),
                ];
                $mform->addElement('select', 'grade', get_string('grade', 'mod_presentation'), $scale_options);

            // Check for "Default competence scale" (Standard Moodle ID is 1).
            } else if ($scaleid == 1) {
                // If it is, we hardcode the options to exactly match your new image.
                // The keys (0, 2, 1) are the correct values Moodle saves for this scale's options.
                $scale_options = [
                    '0' => get_string('nograde', 'mod_presentation'),
                    '2' => get_string('competent', 'mod_presentation'),
                    '1' => get_string('notyetcompetent', 'mod_presentation'),
                ];
                $mform->addElement('select', 'grade', get_string('grade', 'mod_presentation'), $scale_options);

            } else {
                // This is a fallback for any OTHER scale you might use, so the form doesn't break.
                $scale = $DB->get_record('scale', ['id' => $scaleid]);
                if ($scale) {
                    $scale_items = explode(',', $scale->scale);
                    $scale_options = [0 => get_string('nograde', 'mod_presentation')] + \core_grade_scale::make_menu_options($scale_items);
                    $mform->addElement('select', 'grade', get_string('grade', 'mod_presentation'), $scale_options);
                }
            }
        }

        // Comment textarea
        $mform->addElement('textarea', 'teachercomment', get_string('comment', 'mod_presentation'), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('teachercomment', PARAM_TEXT);

        // Action buttons
        $this->add_action_buttons();
    }
}