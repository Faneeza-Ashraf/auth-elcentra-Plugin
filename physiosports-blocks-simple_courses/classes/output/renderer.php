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
 * The renderer for the simple_courses block.
 *
 * @package   block_simple_courses
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>2025 Faneeza Muskan
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_simple_courses\output;

use plugin_renderer_base;

/**
 * Handles the rendering of the block's Mustache templates.
 */
class renderer extends plugin_renderer_base {
    /**
     * Renders the main content of the simple_courses block.
     *
     * @param  stdClass|array $data The data to be passed to the template.
     * @return string The rendered HTML output.
     */
    public function render_main_content($data) {
        return $this->render_from_template('block_simple_courses/main', $data);
    }
}