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
 * Resetore task for the presentation activity.
 *
 * @package     mod_presentation
 * @copyright   2025 Endush Fairy <endush.fairy@paktaleem.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/presentation/backup/moodle2/restore_presentation_stepslib.php');

class restore_presentation_activity_task extends restore_activity_task {

    protected function define_my_settings() {
        // No specific settings.
    }

    protected function define_my_steps() {
        $this->add_step(new restore_presentation_activity_structure_step('presentation_structure', 'presentation.xml'));
    }

    /**
     * Define the dependencies.
     */
    protected function define_my_dependencies() {
        // An activity always needs its context (for permissions/files) to exist first.
        $this->add_dependency(new restore_dependency('context', 'contextid', restore_dependency::MANDATORY));
        // An activity always needs the course section it belongs to.
        $this->add_dependency(new restore_dependency('course_section', 'sectionid', restore_dependency::MANDATORY));
    }

    // Link decoding rules
    public static function define_decode_contents() {
        $contents = [];
        $contents[] = new restore_decode_content('presentation', ['intro'], 'presentation');
        return $contents;
    }

    public static function define_decode_rules() {
        $rules = [];
        $rules[] = new restore_decode_rule('PRESENTATIONVIEWBYID', '/mod/presentation/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('PRESENTATIONINDEX', '/mod/presentation/index.php?id=$1', 'course');
        return $rules;
    }
}