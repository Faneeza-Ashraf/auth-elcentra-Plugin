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
 * Custom renderer for the Course Outline block.
 *
 * @package    block_courseoutline
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_courseoutline\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

/**
 * Custom renderer class for the Course Outline block
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the main block content using Mustache template
     *
     * @param stdClass $data Template data
     * @return string HTML output
     */
    public function render_block_content($data) {
        return $this->render_from_template('block_courseoutline/view', $data);
    }

    /**
     * Render the upload form using Mustache template
     *
     * @param stdClass $data Template data containing form HTML
     * @return string HTML output
     */
    public function render_upload_form($data) {
        return $this->render_from_template('block_courseoutline/upload_form', $data);
    }

    /**
     * Render success message
     *
     * @param string $message Success message
     * @return string HTML output
     */
    public function render_success_message($message) {
        $data = new \stdClass();
        $data->message = $message;
        $data->type = 'success';

        return $this->render_from_template('block_courseoutline/message', $data);
    }

    /**
     * Render error message
     *
     * @param string $message Error message
     * @return string HTML output
     */
    public function render_error_message($message) {
        $data = new \stdClass();
        $data->message = $message;
        $data->type = 'error';

        return $this->render_from_template('block_courseoutline/message', $data);
    }

    /**
     * Render upload page content
     *
     * @param stdClass $data Page data
     * @return string HTML output
     */
    public function render_upload_page($data) {
        return $this->render_from_template('block_courseoutline/upload_page', $data);
    }

    /**
     * Render file information
     *
     * @param stdClass $fileinfo File information
     * @return string HTML output
     */
    public function render_file_info($fileinfo) {
        return $this->render_from_template('block_courseoutline/file_info', $fileinfo);
    }
}

