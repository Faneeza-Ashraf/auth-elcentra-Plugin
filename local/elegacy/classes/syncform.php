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
 * The class definition of the report.
 *
 * @package    local_elegacy
 * @copyright  2021-2023 Syed Zonair, Syed {@link http://paktaleem.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Define your namespace and any other code.
namespace local_elegacy;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Report class to get the users historical report.
 */
class syncform extends \moodleform{

    /**
     * Definition of the form.
     */
    public function definition() {
        $mform = $this->_form;

        // Course ID.
        $mform->addElement('text', 'courseid', 'Course ID');
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons(true, 'Run');
    }
}
