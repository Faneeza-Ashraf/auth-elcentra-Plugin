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
 * Main block class for the Course Outline block.
 *
 * @package    block_courseoutline
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Course Outline block class
 */
class block_courseoutline extends block_base {

    /**
     * Initialize the block
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_courseoutline');
    }

    /**
     * Return the content of this block
     *
     * @return stdClass The block content
     */
   public function get_content() {
        global $COURSE, $USER, $DB, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        // Only show content in course context
        if ($COURSE->id == SITEID) {
            return $this->content;
        }

        // Check if user can view the block
        if (!has_capability('block/courseoutline:view', $this->context)) {
            return $this->content;
        }

        // Get the renderer
        $renderer = $this->page->get_renderer('block_courseoutline');

        // Check if course outline exists
        $outline = $this->get_course_outline($COURSE->id);

        // Prepare data for template
        $templatedata = new stdClass();
        $templatedata->hasoutline = !empty($outline);
        $templatedata->canupload = has_capability('block/courseoutline:upload', $this->context);
        $templatedata->canupdate = has_capability('block/courseoutline:update', $this->context); // This line was added
           $basepermission = has_capability('block/courseoutline:update', $this->context);
        $canoverride = has_capability('block/courseoutline:override', $this->context);

        // Calculate the deadline (1 week after course start date).
        // DAYSECS is a Moodle constant for seconds in a day.
        $deadline = $COURSE->startdate + (7 * DAYSECS);
        $now = time();

    // User can update if they have base permission AND (it's within the deadline OR they can override).
    $templatedata->canupdate = $basepermission && ($now <= $deadline || $canoverride);

        if ($templatedata->hasoutline) {
            // Prepare download data
            $templatedata->downloadurl = $this->get_download_url($outline->fileid);
            $templatedata->timeuploaded = userdate($outline->timeuploaded);
            $templatedata->totalquizzes = $outline->totalquizzes;
            $templatedata->totalassignments = $outline->totalassignments;
            $templatedata->totalpresentations = $outline->totalpresentations;
            $templatedata->totalworkshops = $outline->totalworkshops;

            if ($templatedata->canupload) {
                $teacher = $DB->get_record('user', ['id' => $outline->teacherid]);
                $templatedata->teachername = fullname($teacher);
            }
        }

        // Always provide the upload/update URL for teachers who have permission.
        if ($templatedata->canupload || $templatedata->canupdate) {
            $templatedata->uploadurl = new moodle_url('/blocks/courseoutline/upload.php',
                ['courseid' => $COURSE->id, 'blockid' => $this->instance->id]);
        }

        // Render using Mustache template
        $this->content->text = $renderer->render_block_content($templatedata);

        return $this->content;
    }
 
    /**
     * Get course outline record for a course
     *
     * @param int $courseid Course ID
     * @return stdClass|false Course outline record or false if not found
     */
    private function get_course_outline($courseid) {
        global $DB;

        return $DB->get_record('block_courseoutline', ['courseid' => $courseid]);
    }

    // /**
    //  * Get download URL for a file
    //  *
    //  * @param int $fileid File ID
    //  * @return moodle_url Download URL
    //  */
    // private function get_download_url($fileid) {
    //     global $DB;

    //     $file = $DB->get_record('files', ['id' => $fileid]);
    //     if (!$file) {
    //         return null;
    //     }

    //     return moodle_url::make_pluginfile_url(
    //         $file->contextid,
    //         $file->component,
    //         $file->filearea,
    //         $file->itemid,
    //         $file->filepath,
    //         $file->filename
    //     );
    // }


    /**
     * Get download URL for a file
     *
     * @param int $fileid File ID (unused in this implementation but kept for structure)
     * @return moodle_url Download URL
     */
    private function get_download_url($fileid) {
        global $COURSE;

        // This creates a direct URL to the download.php script, which is the
        // intended handler for file downloads in this plugin.
        return new moodle_url('/blocks/courseoutline/download.php', ['courseid' => $COURSE->id]);
    }   //Updated By Faneeza Muskan  



    /**
     * Allow only one instance of this block per course
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Define where this block can be added
     *
     * @return array
     */
    public function applicable_formats() {
        return [
            'course-view' => true,
            'site' => true,
            'mod' => false,
            'my' => false
        ];
    }

    /**
     * This block has no configuration
     *
     * @return bool
     */
    public function has_config() {
        return false;
    }

    /**
     * This block has no instance configuration
     *
     * @return bool
     */
    public function instance_allow_config() {
        return false;
    }

    /**
     * Serialize and store config data
     */
    public function instance_config_save($data, $nolongerused = false) {
        // No configuration needed for this block
        return true;
    }

    /**
     * Return the plugin config settings for external functions
     *
     * @return stdClass the configs for both the block instance and plugin
     */
    public function get_config_for_external() {
        // No configuration needed
        return (object) [];
    }

    /**
     * This block can be hidden
     *
     * @return bool
     */
    public function instance_can_be_hidden() {
        return true;
    }

    /**
     * This block can be docked
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return false;
    }

    /**
     * This block can be collapsed
     *
     * @return bool
     */
    public function instance_can_be_collapsible() {
        return true;
    }

    /**
     * Delete any related data when block instance is deleted
     */
    public function instance_delete() {
        global $DB;

        // Get course ID from context
        $context = context_block::instance($this->instance->id);
        $coursecontext = $context->get_course_context();
        $courseid = $coursecontext->instanceid;

        // Delete course outline record if it exists
        $outline = $DB->get_record('block_courseoutline', ['courseid' => $courseid]);
        if ($outline) {
            // Delete the file
            $fs = get_file_storage();
            $file = $fs->get_file_by_id($outline->fileid);
            if ($file) {
                $file->delete();
            }

            // Delete the database record
            $DB->delete_records('block_courseoutline', ['id' => $outline->id]);
        }

        return true;
    }
}

