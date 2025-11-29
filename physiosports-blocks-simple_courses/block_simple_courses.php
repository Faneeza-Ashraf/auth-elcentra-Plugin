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
 * @package   block_simple_courses
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We must include these Moodle libraries to get course content information.
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/format/lib.php');

/**
 * The main class for the simple_courses block.
 *
 * Handles data retrieval and presentation logic for the block.
 */
class block_simple_courses extends block_base {
    /**
     * Initializes the block.
     *
     * Sets the title that Moodle will display for the block. Setting it to an
     * empty string effectively hides the block header from view.
     *
     * @return void
     */
    public function init() {
        $this->title = '';
    }

    /**
     * Retrieves the content for the block.
     *
     * This is the main function where the block's content is generated. It fetches
     * the user's enrolled courses, finds relevant activities within them, and
     * prepares the data for rendering via a Mustache template.
     *
     * @return stdClass|null The content object for the block, or null if no content.
     */
    public function get_content() {
        global $USER, $DB, $PAGE, $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        // SQL to fetch all visible courses a user is currently enrolled in.
        $sql = "SELECT c.id, c.fullname, c.shortname, c.summary, c.category FROM {course} c JOIN {enrol} e ON e.courseid = c.id JOIN {user_enrolments} ue ON ue.enrolid = e.id WHERE ue.userid = :userid AND c.visible = 1 AND ue.status = 0 AND e.status = 0 AND (ue.timeend = 0 OR ue.timeend > :now1) AND ue.timestart <= :now2 GROUP BY c.id ORDER BY c.fullname ASC";
        $params = ['userid' => $USER->id, 'now1' => time(), 'now2' => time()];
        $courses = $DB->get_records_sql($sql, $params);

        $this->content = new stdClass;

        if (empty($courses)) {
            $renderer = $this->page->get_renderer('block_simple_courses');
            $this->content->text = $renderer->render_from_template('block_simple_courses/main', ['courses' => []]);
            $this->content->footer = '';
            return $this->content;
        }

        $displaydata = [];
        foreach ($courses as $course) {
            $coursecontext = context_course::instance($course->id);
            $courseitem = new stdClass();
            $courseitem->id = $course->id;
            $courseitem->fullname = $course->fullname;
            $courseitem->viewurl = (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false);

            // SQL to find the most appropriate background image for the course.
            // It searches both 'summary' and 'overviewfiles' and prioritizes images
            // with common names like 'courseimage' or 'cover'.
            $imgsql = "SELECT * FROM {files}
                        WHERE contextid = :contextid
                          AND component = 'course'
                          AND filearea IN ('summary', 'overviewfiles')
                          AND mimetype LIKE 'image/%'
                          AND itemid = 0 AND filename <> '.'
                     ORDER BY
                        CASE WHEN filearea = 'overviewfiles' THEN 1 ELSE 2 END,
                        CASE
                            WHEN LOWER(filename) LIKE '%course%image%' THEN 1
                            WHEN LOWER(filename) LIKE '%cover%' THEN 2
                            WHEN LOWER(filename) LIKE '%banner%' THEN 3
                            ELSE 4
                        END,
                        sortorder ASC,
                        id ASC";
            $images = $DB->get_records_sql($imgsql, ['contextid' => $coursecontext->id]);

            if (!empty($images)) {
                $image = reset($images);
                // Manually construct the URL to avoid issues with Moodle's URL generation functions.
                // This creates a clean URL without the problematic '/0/' itemid.
                $courseimageurl = $CFG->wwwroot . '/pluginfile.php/' . $image->contextid . '/' . $image->component . '/overviewfiles/' . rawurlencode($image->filename);
                $courseitem->courseimage = $courseimageurl;
            } else {
                $courseitem->courseimage = $this->page->theme->image_url('course-default', 'theme')->out(false);
            }

            // Prepare the structure for the three activity types.
            $courseitem->activities = [
                'lectures'    => ['title' => 'LECTURES',    'content' => null, 'button_text' => 'START',      'courseviewurl' => $courseitem->viewurl],
                'workshops'   => ['title' => 'WORKSHOPS',   'content' => null, 'button_text' => 'START',      'courseviewurl' => $courseitem->viewurl],
                'live_events' => ['title' => 'LIVE EVENTS', 'content' => null, 'button_text' => 'VIEW COURSE', 'courseviewurl' => $courseitem->viewurl]
            ];

            $modinfo = get_fast_modinfo($course);
            $sections = $modinfo->get_section_info_all();

            $sectionnames = ['lectures' => 'Lectures', 'workshops' => 'Workshops', 'live_events' => 'Live Events'];
            foreach ($sectionnames as $key => $name) {
                $foundsection = null;
                foreach ($sections as $section) {
                    // Cast section name to string to prevent passing null to strip_tags, avoiding a deprecation warning.
                    if (trim(strip_tags((string) $section->name)) == $name) {
                        $foundsection = $section;
                        break;
                    }
                }

                if ($foundsection && !empty($foundsection->sequence)) {
                    $firstactivityid = explode(',', $foundsection->sequence)[0];
                    if ($cm = $modinfo->get_cm($firstactivityid)) {
                        $content = new stdClass();
                        $activityurl = new moodle_url('/course/view.php', ['id' => $course->id, 'go' => 'module-' . $cm->id]);
                        $content->url = $activityurl->out(false);

                        // Split activity name into title and subtitle if ' - ' is present.
                        if (strpos($cm->name, ' - ') !== false) {
                            list($content->title, $content->subtitle) = explode(' - ', $cm->name, 2);
                        } else {
                            $content->title = $cm->name;
                            $content->subtitle = false;
                        }

                        // Find an image for the activity itself.
                        $modcontext = context_module::instance($cm->id);
                        $filesql = "SELECT * FROM {files} WHERE contextid = :contextid AND component = :component AND filearea IN ('content', 'intro') AND mimetype LIKE 'image/%' ORDER BY filearea ASC, sortorder ASC, id ASC";
                        $fileparams = ['contextid' => $modcontext->id, 'component' => 'mod_' . $cm->modname];
                        $activityimages = $DB->get_records_sql($filesql, $fileparams);

                        if (!empty($activityimages)) {
                            $activityimage = reset($activityimages);
                            $imageurl = moodle_url::make_pluginfile_url($activityimage->contextid, $activityimage->component, $activityimage->filearea, $activityimage->itemid, $activityimage->filepath, $activityimage->filename);
                            $content->image = $imageurl->out(false);
                        }

                        // If the activity has no image, fall back to using the main course image.
                        if (empty($content->image)) {
                            $content->image = $courseitem->courseimage;
                        }

                        $courseitem->activities[$key]['content'] = $content;
                    }
                }
            }
            $displaydata[] = $courseitem;
        }

        // Set the first course as active by default.
        if (!empty($displaydata)) {
            $displaydata[0]->isactive = true;
        }

        $data = ['courses' => $displaydata];

        $renderer = $PAGE->get_renderer('block_simple_courses');
        $this->content->text = $renderer->render_main_content($data);
        $this->content->footer = '';

        // Call the AMD module to initialize the JavaScript for this block.
        // We use a stable attribute selector to guarantee the JS can find the block.
        $selector = '[data-block="simple_courses"][data-instance-id="' . $this->instance->id . '"]';
        $PAGE->requires->js_call_amd('block_simple_courses/main', 'init', [$selector]);

        return $this->content;
    }

    /**
     * Specifies which page formats this block can be added to.
     *
     * @return array
     */
    public function applicable_formats() {
        return ['all' => true];
    }

    /**
     * Determines if the block's content can be cached.
     *
     * @return bool False, because the content is dynamic per user.
     */
    public function instance_can_be_cached() {
        return false;
    }
}