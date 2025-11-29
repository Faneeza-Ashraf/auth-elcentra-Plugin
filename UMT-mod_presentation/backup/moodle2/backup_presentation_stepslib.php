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
 * backup steps library for the presentation activity.
 *
 * @package     mod_presentation
 * @copyright   2025 Endush Fairy <endush.fairy@paktaleem.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Defines the structure of the presentation activity backup.
 */
class backup_presentation_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        // Define the main table for the presentation activity.
        $presentation = new backup_nested_element('presentation', ['id'], [
            'name', 'intro', 'introformat', 'course', 'grade', 'gradepass',
            'maxfiles', 'maxsize', 'allowsubmissionsfromdate', 'duedate',
            'cutoffdate', 'blindmarking', 'hidegrader', 'markingworkflow',
            'markingallocation', 'timecreated', 'timemodified'
        ]);

        // Define the submissions table as a child of the presentation.
        $submissions = new backup_nested_element('presentation_submissions');
        $submission = new backup_nested_element('presentation_submission', ['id'], [
            'presentationid', 'userid', 'grade', 'teachercomment',
            'timecreated', 'timemodified'
        ]);

        // Link the structure together.
        $presentation->add_child($submissions);
        $submissions->add_child($submission);

        // Tell the backup where to get the data for each element.
        $presentation->set_source_table('presentation', ['id' => backup::VAR_ACTIVITYID]);
        $submission->set_source_table('presentation_submissions', ['presentationid' => backup::VAR_PARENTID]);

        // Define how files are linked to the data.
        $submission->annotate_files('mod_presentation', 'submission_files', 'id');
        $presentation->annotate_files('mod_presentation', 'intro', 'id');

        // Return the whole structure.
        return $this->prepare_activity_structure($presentation);
    }
}