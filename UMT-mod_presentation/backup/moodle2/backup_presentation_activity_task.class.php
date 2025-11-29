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
 * backup task for the presentation activity.
 *
 * @package     mod_presentation
 * @copyright   2025 Endush Fairy <endush.fairy@paktaleem.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/presentation/backup/moodle2/backup_presentation_stepslib.php');

/**
 * Defines the backup task for the presentation activity.
 */
class backup_presentation_activity_task extends backup_activity_task {

    protected function define_my_settings() {
        // No specific settings for this plugin.
    }

    protected function define_my_steps() {
        // Add the structure step to the backup plan.
        $this->add_step(new backup_presentation_activity_structure_step('presentation_structure', 'presentation.xml'));
    }

    /**
     * This is the function that fixes your error.
     * It encodes any Moodle internal links found in the text fields.
     *
     * @param string $content The content to be encoded.
     * @return string The encoded content.
     */
    static public function encode_content_links($content) {
        global $CFG;

        // The 'intro' field is the primary text content we need to process.
        $base = preg_quote($CFG->wwwroot, '/');
        $content = preg_replace('/('.$base.'\/mod\/presentation\/index.php\?id\=)([0-9]+)/', '$@PRESENTATIONINDEX*$2@$', $content);
        $content = preg_replace('/('.$base.'\/mod\/presentation\/view.php\?id\=)([0-9]+)/', '$@PRESENTATIONVIEWBYID*$2@$', $content);

        return $content;
    }
}