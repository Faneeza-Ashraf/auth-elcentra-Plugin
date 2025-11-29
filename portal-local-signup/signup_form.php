<?php
require_once("$CFG->libdir/formslib.php");

class signup_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        // Heading
        $mform->addElement('header', 'signupheader', get_string('createaccount', 'local_signup'));

        // Email (used as username)
        $mform->addElement('text', 'email', get_string('email', 'local_signup'));
        $mform->setType('email', PARAM_EMAIL);
        $mform->addRule('email', null, 'required');
        $mform->addHelpButton('email', 'email', 'local_signup');

       
        // First name
        $mform->addElement('text', 'firstname', get_string('firstname', 'local_signup'));
        $mform->setType('firstname', PARAM_NOTAGS);
        $mform->addRule('firstname', null, 'required');

        // Last name
        $mform->addElement('text', 'lastname', get_string('lastname', 'local_signup'));
        $mform->setType('lastname', PARAM_NOTAGS);
        $mform->addRule('lastname', null, 'required');

        // Action button
         $mform->addElement('submit', 'submitbutton', get_string('createaccount', 'local_signup'));
    }
}
