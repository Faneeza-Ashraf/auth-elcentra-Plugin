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
 * @package   local_signup
 * @copyright 2021 Derick Turner
 * @author    Derick Turner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_signup;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use context_system;

// ✅ Load the base form class from IOMAD
require_once($CFG->dirroot . '\local\signup\classes\form\company_create.php');

class company_edit_form extends \company_moodleform {
    protected $firstcompany;
    protected $isadding;
    protected $title = '';
    protected $description = '';
    protected $companyid;
    protected $companyrecord;
    protected $parentcompanyid;
    protected $previousroletemplateid;
    protected $previousemailtemplateid;
    protected $child;
    protected $context;
    protected $parentcompany;

    public function __construct($actionurl, $isadding, $companyid, $companyrecord, $firstcompany = false, $parentcompanyid = 0, $child = false) {
        global $DB, $CFG;

        $this->isadding = $isadding;
        $this->companyid = $companyid;
        $this->companyrecord = $companyrecord;
        $this->firstcompany = $firstcompany;
        $this->parentcompanyid = $parentcompanyid;
        $this->previousroletemplateid = $companyrecord->previousroletemplateid;
        $this->previousemailtemplateid = $companyrecord->previousemailtemplateid;
        if (!empty($companyrecord->templates)) {
            $this->companyrecord->templates = array();
        }
        $this->child = $child;
        if (empty($this->companyrecord->theme)) {
            $this->companyrecord->theme = $CFG->theme;
        }
        if ($parentcompanyid) {
            $this->parentcompany = $DB->get_record('company', ['id' => $parentcompanyid], '*', MUST_EXIST);
            $this->context = \core\context\company::instance($parentcompanyid);
        }
        if (!empty($companyid)) {
            $this->context = \core\context\company::instance($companyid);
        }
        if (empty($this->context)) {
            $this->context = context_system::instance();
        }

        parent::__construct($actionurl);
    }

    public function definition() {
        global $CFG, $PAGE, $DB;
        $systemcontext = context_system::instance();

        $mform = &$this->_form;

        $strrequired = get_string('required');

        $mform->addElement('hidden', 'companyid', $this->companyid);
        $mform->setType('companyid', PARAM_INT);
        $mform->addElement('hidden', 'currentparentid', $this->parentcompanyid);
        $mform->setType('currentparentid', PARAM_INT);
        $mform->addElement('hidden', 'companyterminated');
        $mform->setType('companyterminated', PARAM_INT);
        $mform->setDefault('companyterminated', 0);

        if ($this->firstcompany) {
            $mform->addElement('html', '<div class="alert alert-info">' . get_string('firstcompany', 'local_signup') . '</div>');
        }

        $mform->addElement('text', 'name',
            get_string('companyname', 'local_signup'),
            'maxlength="50" size="50"');
        $mform->setType('name', PARAM_NOTAGS);
        $mform->addRule('name', $strrequired, 'required', null, 'client');

        $mform->addElement('text', 'shortname',
            get_string('companyshortname', 'local_signup'),
            'maxlength="25" size="25"');
        $mform->setType('shortname', PARAM_NOTAGS);
        $mform->addRule('shortname', $strrequired, 'required', null, 'client');

        $mform->addElement('text', 'code',
            get_string('companycode', 'local_signup'),
            'maxlength="255" size="25"');
        $mform->setType('code', PARAM_NOTAGS);
        $mform->addHelpButton('code', 'companycode', 'local_signup');

        $mform->addElement('hidden', 'previousroletemplateid');
        $mform->addElement('hidden', 'previousemailtemplateid');

        $mform->setType('parentid', PARAM_INT);
        $mform->setType('templates', PARAM_RAW);
        $mform->setType('previousroletemplateid', PARAM_INT);
        $mform->setType('previousemailtemplateid', PARAM_INT);

        $mform->addElement('textarea', 'address', get_string('address'));
        $mform->setType('address', PARAM_NOTAGS);

        $mform->addElement('text', 'city',
            get_string('companycity', 'local_signup'),
            'maxlength="50" size="50"');
        $mform->setType('city', PARAM_NOTAGS);
        $mform->addRule('city', $strrequired, 'required', null, 'client');

        $mform->addElement('text', 'region',
            get_string('companyregion', 'local_signup'),
            'maxlength="50" size="50"');
        $mform->setType('region', PARAM_NOTAGS);

        $mform->addElement('text', 'postcode',
            get_string('postcode', 'local_signup'), ['size' => 20, 'maxlength' => 20]);
        $mform->setType('postcode', PARAM_NOTAGS);

        $choices = get_string_manager()->get_list_of_countries();
        $choices = array('' => get_string('selectacountry') . '...') + $choices;
        $mform->addElement('select', 'country', get_string('selectacountry'), $choices);
        $mform->addRule('country', $strrequired, 'required', null, 'client');
        if (!empty($CFG->country)) {
            $mform->setDefault('country', $CFG->country);
        }

        if ($this->isadding) {
            $submitlabel = get_string('saveasnewcompany', 'local_signup');
            $mform->addElement('hidden', 'createnew', 1);
            $mform->setType('createnew', PARAM_INT);
        } else {
            $submitlabel = null;
        }

        $mform->disable_form_change_checker();

        $this->add_action_buttons(true, $submitlabel);

        return $errors ?? [];
    }
}
