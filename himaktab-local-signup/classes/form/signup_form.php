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
 * @package    local
 * @subpackage signup
 *@copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class local_signup_form extends moodleform {

    public function definition() {
        global $CFG;
        $mform = $this->_form;
        $mform->addElement('header', 'accountheader', get_string('accountdetails', 'local_signup'));
        $mform->addElement('text', 'username', get_string('username'));
        $mform->setType('username', PARAM_ALPHANUM);
        $mform->addRule('username', get_string('required'), 'required');

        $mform->addElement('passwordunmask', 'password', get_string('password'));
        $mform->setType('password', PARAM_RAW);
        $mform->addRule('password', get_string('required'), 'required');

        $mform->addElement('header', 'personalheader', get_string('personalinformation', 'local_signup'));
        $mform->addElement('text', 'firstname', get_string('studentsfirstname', 'local_signup'));
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', get_string('required'), 'required');

        $mform->addElement('text', 'lastname', get_string('studentslastname', 'local_signup'));
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname', get_string('required'), 'required');

        $mform->addElement('text', 'email', get_string('email'));
        $mform->setType('email', PARAM_EMAIL);
        $mform->addRule('email', get_string('required'), 'required');

        $mform->addElement('date_selector', 'dateofbirth', get_string('dateofbirth', 'local_signup'));

        $mform->addElement('header', 'contactheader', get_string('contactinformation', 'local_signup'));

        $mform->addElement('text', 'homeaddress', get_string('homeaddress', 'local_signup'));
        $mform->setType('homeaddress', PARAM_TEXT);
        $mform->addRule('homeaddress', get_string('required'), 'required');

        $mform->addElement('text', 'parentname', get_string('parentname', 'local_signup'));
        $mform->setType('parentname', PARAM_TEXT);
        $mform->addRule('parentname', get_string('required'), 'required');

        $mform->addElement('text', 'emergencycontactname', get_string('emergencycontactname', 'local_signup'));
        $mform->setType('emergencycontactname', PARAM_TEXT);
        $mform->addRule('emergencycontactname', get_string('required'), 'required');

        $mform->addElement('text', 'emergencyphone', get_string('emergencyphone', 'local_signup'));
        $mform->setType('emergencyphone', PARAM_TEXT);
        $mform->addRule('emergencyphone', get_string('required'), 'required');

        $mform->addElement('text', 'parentemail', get_string('parentemail', 'local_signup'));
        $mform->setType('parentemail', PARAM_EMAIL);
        $mform->addRule('parentemail', get_string('required'), 'required');

        $mform->addElement('text', 'phone2', get_string('phone2', 'local_signup'));
        $mform->setType('phone2', PARAM_TEXT);

        $mform->addElement('header', 'healthheader', get_string('healthinformation', 'local_signup'));
        $mform->addElement('textarea', 'healthinfo', get_string('healthinfo_details', 'local_signup'), 'wrap="virtual" rows="3" cols="50"');
        $mform->setType('healthinfo', PARAM_TEXT);

        $mform->addElement('header', 'additionalheader', get_string('additionalinformation', 'local_signup'));
        $mform->addElement('textarea', 'specialneeds', get_string('specialneeds_details', 'local_signup'), 'wrap="virtual" rows="3" cols="50"');
        $mform->setType('specialneeds', PARAM_TEXT);

        $mform->addElement('header', 'programheader', get_string('programselection', 'local_signup'));
        $gradeoptions = [
            '' => get_string('select'),
            'Grade 6' => 'Grade 6',
            'Grade 7' => 'Grade 7',
            'Grade 8' => 'Grade 8',
            'Grade 9' => 'Grade 9',
            'Grade 10' => 'Grade 10',
            'Grade 11' => 'Grade 11',
            'Grade 12' => 'Grade 12',
        ];
        $mform->addElement('select', 'desiredgrade', get_string('desiredgrade', 'local_signup'), $gradeoptions);
        $mform->addRule('desiredgrade', get_string('required'), 'required');

        $this->add_action_buttons(true, get_string('createaccount', 'local_signup'));
    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        if ($DB->record_exists('user', ['username' => $data['username'], 'mnethostid' => get_config('moodle', 'mnethostid')])) {
            $errors['username'] = get_string('username_exists', 'local_signup');
        }
        if ($DB->record_exists('user', ['email' => $data['email'], 'mnethostid' => get_config('moodle', 'mnethostid')])) {
            $errors['email'] = get_string('email_exists', 'local_signup');
        }
        return $errors;
    }
}