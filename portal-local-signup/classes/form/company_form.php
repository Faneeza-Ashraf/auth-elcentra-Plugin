<?php

namespace local_signup\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class company_form extends \moodleform {
    public function definition() {
        global $CFG;  // you need this for $CFG->country

        $mform = $this->_form;
        $mform->addElement('header', 'companyheader', get_string('createcompany', 'local_signup'));
        $strrequired = get_string('required');

        $mform->addElement('text', 'name', get_string('companyname', 'local_signup'), 'maxlength="50" size="50"');
        $mform->setType('name', PARAM_NOTAGS);
        $mform->addRule('name', $strrequired, 'required', null, 'client');

        $mform->addElement('text', 'shortname', get_string('companyshortname', 'local_signup'), 'maxlength="25" size="25"');
        $mform->setType('shortname', PARAM_NOTAGS);
        $mform->addRule('shortname', $strrequired, 'required', null, 'client');

        $mform->addElement('text', 'city', get_string('companycity', 'local_signup'), 'maxlength="50" size="50"');
        $mform->setType('city', PARAM_NOTAGS);
        $mform->addRule('city', $strrequired, 'required', null, 'client');

        $choices = get_string_manager()->get_list_of_countries();
        $choices = array('' => get_string('selectacountry') . '...') + $choices;
        $mform->addElement('select', 'country', get_string('selectacountry'), $choices);
        $mform->addRule('country', $strrequired, 'required', null, 'client');
        if (!empty($CFG->country)) {
            $mform->setDefault('country', $CFG->country);
        }

        // ADD hidden userid field here
        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('submit', 'submitbutton', get_string('savechanges'));
    }
}
