<?php

namespace local_signup\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php'); // important to include Moodle form library!

class course_edit_form extends \moodleform {

    protected $companyid;
    protected $editoroptions;
    protected $categoryid;

    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form;

        // Retrieve custom data.
        $this->companyid = $this->_customdata['companyid'] ?? 0;
        $this->editoroptions = $this->_customdata['editoroptions'] ?? [];
        $this->categoryid = $this->_customdata['categoryid'] ?? 1;

        // Set context.
        $context = \core\context\coursecat::instance($this->categoryid);
        \context_helper::preload_from_record($context);

        // Add form elements here (example)
        $mform->addElement('text', 'coursename', get_string('coursename', 'local_signup'));
        $mform->setType('coursename', PARAM_TEXT);

        // Add submit button.
        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Validate form data.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Custom validations go here.
        return $errors;
    }
}
