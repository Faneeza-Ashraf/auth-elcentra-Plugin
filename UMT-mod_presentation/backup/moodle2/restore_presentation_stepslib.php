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
 * Restore library for presentation module
 *
 * @package     mod_presentation
 * @copyright   2025 Endush Fairy <endush.fairy@paktaleem.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class restore_presentation_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {
        $paths = [];

        $paths[] = new restore_path_element('presentation', '/activity/presentation');
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('submission', '/activity/presentation/submissions/submission');
        }

        return $this->prepare_activity_structure($paths);
    }

    public function process_presentation($data) {
        global $DB;
        $data = (object)$data;
        $data->course = $this->get_courseid();

        $data->timecreated = isset($data->timecreated) ? $this->apply_date_offset($data->timecreated) : time();
        $data->timemodified = isset($data->timemodified) ? $this->apply_date_offset($data->timemodified) : time();

        $newitemid = $DB->insert_record('presentation', $data);
        $this->apply_activity_instance($newitemid);
    }

    public function process_submission($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;

        $data->presentationid = $this->get_new_parentid('presentation');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $data->timecreated = isset($data->timecreated) ? $this->apply_date_offset($data->timecreated) : time();
        $data->timemodified = isset($data->timemodified) ? $this->apply_date_offset($data->timemodified) : time();

        $newitemid = $DB->insert_record('presentation_submissions', $data);
        $this->set_mapping('submission', $oldid, $newitemid);
    }

    /**
     * This method is called after the step has been executed.
     * THIS IS THE CORRECT PLACE TO MAP FILES.
     */
    protected function after_execute() {
        // Add related files to the restore plan.
        $this->add_related_files('mod_presentation', 'intro', 'presentation');

        if ($this->get_setting_value('userinfo')) {
            $this->add_related_files('mod_presentation', 'submission', 'submission');
        }
    }
}