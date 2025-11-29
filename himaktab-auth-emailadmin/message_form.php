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
 * @package    auth
 * @subpackage emailadmin
 *@copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class auth_emailadmin_message_form extends moodleform {
        public function definition() {
        global $CFG;
        $mform = $this->_form;
        $mform->addElement('text', 'subject', get_string('subject', 'auth_emailadmin'), 'size="50"');
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', get_string('required'), 'required', null, 'client');

        $mform->addElement('editor', 'messagebody', get_string('message', 'auth_emailadmin'));
        $mform->setType('messagebody', PARAM_RAW);
        $mform->addRule('messagebody', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(true, get_string('send', 'auth_emailadmin'));
    }
}