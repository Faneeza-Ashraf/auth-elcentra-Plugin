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
 * Upload form for the Course Outline block.
 *
 * @package    block_courseoutline
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_courseoutline\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/form/filemanager.php');  //Added by Faneeza Muskan
/**
 * Form for uploading course outline files
 */
class upload_form extends \moodleform {

    /**
     * Define the form elements
     */
 /**
     * Define the form elements
     */
    public function definition() {
        $mform = $this->_form;

        // Hidden fields
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);

        // Instructions
        $mform->addElement('html', '<div class="alert alert-info">' .
            get_string('upload_instructions', 'block_courseoutline') . '</div>');

        // File upload element
        $mform->addElement('filepicker', 'outline_file',
            get_string('file_upload', 'block_courseoutline'),
            null,
            [
                'maxbytes' => get_config('block_courseoutline', 'max_file_size') ?: 10485760, // 10MB default
                'accepted_types' => ['.pdf'],
                'return_types' => \FILE_INTERNAL
            ],
        );
        $mform->addHelpButton('outline_file', 'file_upload', 'block_courseoutline');

        // --- START OF CORRECTION ---

        // Only make the file required if this is a new upload, not an update.
        if (empty($this->_customdata['isupdating'])) {
            $mform->addRule('outline_file', get_string('required'), 'required', null, 'client');
        }

        // --- END OF CORRECTION ---

        // Add new text fields.
        $mform->addElement('text', 'totalquizzes', get_string('total_quizzes', 'block_courseoutline'));
        $mform->setType('totalquizzes', PARAM_INT);
        $mform->addRule('totalquizzes', get_string('required', 'block_courseoutline'), 'required', null, 'client');
        $mform->addRule('totalquizzes', get_string('numeric', 'block_courseoutline'), 'numeric', null, 'client'); // Corrected get_string

        $mform->addElement('text', 'totalassignments', get_string('total_assignments', 'block_courseoutline'));
        $mform->setType('totalassignments', PARAM_INT);
        $mform->addRule('totalassignments', get_string('required', 'block_courseoutline'), 'required', null, 'client');
        $mform->addRule('totalassignments', get_string('numeric', 'block_courseoutline'), 'numeric', null, 'client'); // Corrected get_string

        $mform->addElement('text', 'totalpresentations', get_string('total_presentations', 'block_courseoutline'));
        $mform->setType('totalpresentations', PARAM_INT);
        $mform->addRule('totalpresentations', get_string('required', 'block_courseoutline'), 'required', null, 'client');
        $mform->addRule('totalpresentations', get_string('numeric', 'block_courseoutline'), 'numeric', null, 'client'); // Corrected get_string

        $mform->addElement('text', 'totalworkshops', get_string('total_workshops', 'block_courseoutline'));
        $mform->setType('totalworkshops', PARAM_INT);
        $mform->addRule('totalworkshops', get_string('required', 'block_courseoutline'), 'required', null, 'client');
        $mform->addRule('totalworkshops', get_string('numeric', 'block_courseoutline'), 'numeric', null, 'client'); // Corrected get_string

        // Submit buttons
        $this->add_action_buttons(true, get_string('upload_outline', 'block_courseoutline'));
    }

    /**
     * Validate the form data
     *
     * @param array $data Form data
     * @param array $files Uploaded files
     * @return array Validation errors
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Check if course outline already exists
          if (!empty($data['courseid'])) {
        $existing = $DB->get_record('block_courseoutline', ['courseid' => $data['courseid']]);
        if ($existing) {
            // This check needs to be changed to allow updates.
            // For now, we are removing it. A better check would be to see if we are in "update" mode.
        }
    }

        // Validate file type (additional check)
        if (!empty($files['outline_file'])) {
            $filename = $files['outline_file']['name'];
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if ($extension !== 'pdf') {
                $errors['outline_file'] = get_string('invalid_file_type', 'block_courseoutline');
            }
        }

        return $errors;
    }
}
