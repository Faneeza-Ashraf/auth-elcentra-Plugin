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
 * Course data source for the Custom Report plugin.
 *
 * @package    report_olarcusage
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/report/olarcusage/lib.php');

class filter_form extends moodleform {

    protected function definition() {
        global $CFG;
        $mform = $this->_form;
        $semesters = report_olarcusage_get_semesters();
        $mform->addElement('select', 'semesterid', get_string('filtersemester', 'report_olarcusage'), $semesters);
        $mform->setType('semesterid', PARAM_INT);
        $schools = report_olarcusage_get_schools();
        $mform->addElement('select', 'schoolid', get_string('filterschool', 'report_olarcusage'), $schools);
        $mform->setType('schoolid', PARAM_INT);
        $reporttypes = [
            'all' => get_string('reporttypeall', 'report_olarcusage'),
            'online' => get_string('reporttypeonline', 'report_olarcusage'),
            'onpremises' => get_string('reporttypeonpremises', 'report_olarcusage'),
        ];
        $mform->addElement('select', 'reporttype', get_string('filterreporttype', 'report_olarcusage'), $reporttypes);
        $mform->setType('reporttype', PARAM_ALPHA);
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('generatereport', 'report_olarcusage'));
        $graphicalbuttonurl = new moodle_url('/report/olarcusage/graphical_report.php');
        $graphicalbuttonhtml = html_writer::link($graphicalbuttonurl, get_string('viewgraphicalreport', 'report_olarcusage'), ['class' => 'btn btn-secondary']);
        $buttonarray[] = $mform->createElement('html', $graphicalbuttonhtml);

        $patternbuttonurl = new moodle_url('/report/olarcusage/pattern_report.php');
        $patternbuttonhtml = html_writer::link($patternbuttonurl, get_string('viewpatternreport', 'report_olarcusage'), ['class' => 'btn btn-secondary']);
        $buttonarray[] = $mform->createElement('html', $patternbuttonhtml);

        $schoolbuttonurl = new moodle_url('/report/olarcusage/school_report.php');
        $schoolbuttonhtml = html_writer::link($schoolbuttonurl, get_string('viewschoolreport', 'report_olarcusage'), ['class' => 'btn btn-secondary']);
        $buttonarray[] = $mform->createElement('html', $schoolbuttonhtml);
        
         $comparisonbuttonurl = new moodle_url('/report/olarcusage/comparison_report.php');
        $comparisonbuttonhtml = html_writer::link($comparisonbuttonurl, get_string('viewcomparisonreport', 'report_olarcusage'), ['class' => 'btn btn-secondary']);
        $buttonarray[] = $mform->createElement('html', $comparisonbuttonhtml);

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
    }
}