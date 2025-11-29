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
 * Manager class for Course Outline block operations.
 *
 * @package    block_courseoutline
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_courseoutline;

defined('MOODLE_INTERNAL') || die();

/**
 * Manager class for handling course outline operations
 */
class manager {

    /**
     * Get course outline record for a specific course
     *
     * @param int $courseid Course ID
     * @return stdClass|false Course outline record or false if not found
     */
    public static function get_course_outline($courseid) {
        global $DB;

            return $DB->get_record('block_courseoutline', ['courseid' => $courseid]);
    }

    /**
     * Check if a course has an outline uploaded
     *
     * @param int $courseid Course ID
     * @return bool True if outline exists, false otherwise
     */
    public static function has_course_outline($courseid) {
        return self::get_course_outline($courseid) !== false;
    }

    /**
     * Get the uploaded file for a course outline
     *
     * @param int $courseid Course ID
     * @return stored_file|false The file object or false if not found
     */
    public static function get_outline_file($courseid) {
        $outline = self::get_course_outline($courseid);
        if (!$outline) {
            return false;
        }

        $fs = get_file_storage();
        return $fs->get_file_by_id($outline->fileid);
    }

    /**
     * Create a new course outline record
     *
     * @param int $courseid Course ID
     * @param int $teacherid Teacher ID who uploaded the file
     * @param int $fileid File ID from mdl_files table
     * @return int|false The new record ID or false on failure
     */
    public static function create_course_outline($courseid, $teacherid, $fileid) {
        global $DB;

        // Check if outline already exists
        if (self::has_course_outline($courseid)) {
            return false;
        }

        $record = new \stdClass();
        $record->courseid = $courseid;
        $record->teacherid = $teacherid;
        $record->fileid = $fileid;
        $record->timeuploaded = time();

        return $DB->insert_record('block_courseoutline', $record);
    }

    /**
     * Delete a course outline and its associated file
     *
     * @param int $courseid Course ID
     * @return bool True on success, false on failure
     */
    public static function delete_course_outline($courseid) {
        global $DB;

        $outline = self::get_course_outline($courseid);
        if (!$outline) {
            return false;
        }

        // Delete the file
        $file = self::get_outline_file($courseid);
        if ($file) {
            $file->delete();
        }

        // Delete the database record
        return $DB->delete_records('block_courseoutline', ['id' => $outline->id]);
    }

    /**
     * Get download URL for a course outline
     *
     * @param int $courseid Course ID
     * @return moodle_url|false Download URL or false if no outline exists
     */
    public static function get_download_url($courseid) {
        if (!self::has_course_outline($courseid)) {
            return false;
        }

        return new \moodle_url('/blocks/courseoutline/download.php', ['courseid' => $courseid]);
    }

    /**
     * Get upload URL for a course outline
     *
     * @param int $courseid Course ID
     * @param int $blockid Block instance ID
     * @return moodle_url Upload URL
     */
    public static function get_upload_url($courseid, $blockid) {
        return new \moodle_url('/blocks/courseoutline/upload.php',
            ['courseid' => $courseid, 'blockid' => $blockid]);
    }

    /**
     * Get all course outlines uploaded by a specific teacher
     *
     * @param int $teacherid Teacher ID
     * @return array Array of course outline records
     */
    public static function get_outlines_by_teacher($teacherid) {
        global $DB;

        return $DB->get_records('block_courseoutline', ['teacherid' => $teacherid]);
    }

    /**
     * Get statistics about course outlines
     *
     * @return stdClass Statistics object
     */
    public static function get_statistics() {
        global $DB;

        $stats = new \stdClass();
        $stats->total_outlines = $DB->count_records('block_courseoutline');
        $stats->total_courses_with_outlines = $DB->count_records('block_courseoutline');
        $stats->total_courses = $DB->count_records('course', ['visible' => 1]) - 1; // Exclude site course

        if ($stats->total_courses > 0) {
            $stats->percentage_with_outlines = round(($stats->total_courses_with_outlines / $stats->total_courses) * 100, 2);
        } else {
            $stats->percentage_with_outlines = 0;
        }

        return $stats;
    }

    /**
     * Validate uploaded file
     *
     * @param stored_file $file The uploaded file
     * @return array Array of validation errors (empty if valid)
     */
    public static function validate_file($file) {
        $errors = [];

        if (!$file) {
            $errors[] = get_string('error_file_not_found', 'block_courseoutline');
            return $errors;
        }

        // Check file type
        $filename = $file->get_filename();
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($extension !== 'pdf') {
            $errors[] = get_string('invalid_file_type', 'block_courseoutline');
        }

        // Check file size
        $maxsize = get_config('block_courseoutline', 'max_file_size') ?: 10485760; // 10MB default
        if ($file->get_filesize() > $maxsize) {
            $errors[] = get_string('file_too_large', 'block_courseoutline', display_size($maxsize));
        }

        return $errors;
    }

    /**
     * Clean up orphaned files and records
     *
     * @return int Number of cleaned up records
     */
    public static function cleanup_orphaned_data() {
        global $DB;

        $cleaned = 0;
        $fs = get_file_storage();

        // Get all outline records
        $outlines = $DB->get_records('block_courseoutline');

        foreach ($outlines as $outline) {
            $file = $fs->get_file_by_id($outline->fileid);
            $course = $DB->get_record('course', ['id' => $outline->courseid]);

            // If file doesn't exist or course doesn't exist, clean up
            if (!$file || !$course) {
                if ($file) {
                    $file->delete();
                }
                $DB->delete_records('block_courseoutline', ['id' => $outline->id]);
                $cleaned++;
            }
        }

        return $cleaned;
    }
}

