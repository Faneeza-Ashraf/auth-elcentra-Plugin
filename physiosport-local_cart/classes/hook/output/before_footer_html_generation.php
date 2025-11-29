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
 * The simple courses block class.
 *
 * Used to produce a master-detail view of a user's enrolled courses and their activities.
 *
 * @package   local_cart
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cart\hook\output;

use core\hook\output\before_footer_html_generation;

class before_footer_html_generation_hook {
    /**
     * The callback method that Moodle will run.
     *
     * @param before_footer_html_generation $hook The hook data.
     * @return void
     */
    public static function callback(before_footer_html_generation $hook): void {
        global $OUTPUT, $USER;

        if (isloggedin() && !isguestuser($USER)) {
            $hook->html .= $OUTPUT->render_from_template('local_cart/footer_content', []);
        }
    }
}