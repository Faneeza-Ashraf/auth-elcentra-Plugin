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
 * Renderer for the Presentation activity module.
 *
 * @package     mod_presentation
 * @copyright   2025 Endush Fairy <endush.fairy@paktaleem.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_presentation\output;
defined('MOODLE_INTERNAL') || die();
class renderer extends \plugin_renderer_base {
    protected function get_coursemodule_icon(\cm_info $mod) {
        global $OUTPUT;
        
        // This code works perfectly for icon.png
        $icon = new \pix_icon('icon', $mod->name, 'mod_presentation');

        return $OUTPUT->render($icon);
    }
}