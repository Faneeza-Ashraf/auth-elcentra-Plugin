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
 * Custom renderer for the Custom Report plugin.
 *
 * @package    report_olarcusage
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_olarcusage\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use html_table;
use html_writer;
use moodle_url;

/**
 * Custom renderer class for the Custom Report plugin
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the main report page
     * TODO: Not in use.
     *
     * @param array $headers Table headers
     * @param array $data Table data
     * @param int $courseid Course ID
     * @return string HTML output
     */
    public function render_report_page($headers, $data, $courseid = 0) {
        // Prepare template data
        $templatedata = new \stdClass();
        $templatedata->title = get_string('reporttitle', 'report_olarcusage');

        if ($courseid > 0) {
            global $DB;
            $course                 = $DB->get_record('course', ['id' => $courseid]);
            $templatedata->subtitle = $course ? format_string($course->fullname) : get_string('course');
        } else {
            $templatedata->subtitle = get_string('allcourses', 'moodle');
        }

        $templatedata->generated_date   = userdate(time());
        $templatedata->courseid         = $courseid;
        $templatedata->has_data         = !empty($data);

        if ($templatedata->has_data) {
            $templatedata->headers      = $headers;
            $templatedata->data         = $this->prepare_table_data($data);
            $templatedata->statistics   = $this->prepare_statistics_data($data);
        }

        // Prepare export URLs
        $templatedata->csv_url = new \moodle_url('/report/olarcusage/index.php',
            ['id' => $courseid, 'format' => 'csv']);
        $templatedata->excel_url = new \moodle_url('/report/olarcusage/index.php',
            ['id' => $courseid, 'format' => 'excel']);
        $templatedata->pdf_url = new \moodle_url('/report/olarcusage/index.php',
            ['id' => $courseid, 'format' => 'pdf']);

        if ($courseid > 0) {
            $templatedata->course_url = new \moodle_url('/course/view.php', ['id' => $courseid]);
        }

        return $this->render_from_template('report_olarcusage/report_page', $templatedata);
    }

    /**
     * Prepare table data for template
     *
     * @param array $data Raw report data
     * @return array Formatted data for template
     */
    protected function prepare_table_data($data) {
        $formatted_data = [];

        foreach ($data as $row) {
            $formatted_row = [
                'semester'                      => $row['semester'],
                'school'                        => $row['school'],
                'program'                       => $row['program'],
                'course_code'                   => $row['course_code'],
                'course_title'                  => $row['course_title'],
                'faculty'                       => $row['faculty'],
                'email'                         => $row['email'],
                'section'                       => $row['section'],
                'assignments_given'             => $row['assignments_given'],
                'quizzes_taken'                 => $row['quizzes_taken'],
                'chats'                         => $row['chats'],
                'learning_material_uploaded'    => $row['learning_material_uploaded'],
                'course_outline_added'          => $row['course_outline_added'],
                'students_on_lms'               => $row['students_on_lms'],
                'lms_usage_status'              => $row['lms_usage_status'],
                'recording_link'                => $row['recording_link'],
                'course_link'                   => $row['course_link'],
                'template_status'               => $row['template_status'],
                'usage_activity_percent'        => $row['usage_activity_percent'],
            ];

            $formatted_data[] = $formatted_row;
        }

        return $formatted_data;
    }

    /**
     * Prepare statistics data for template
     *
     * @param array $data Report data
     * @return array Statistics data
     */
    protected function prepare_statistics_data($data) {
        if (empty($data)) {
            return [];
        }

        $total_courses      = count($data);
        $total_students     = array_sum(array_column($data, 'students_on_lms'));
        $total_assignments  = array_sum(array_column($data, 'assignments_given'));
        $total_quizzes      = array_sum(array_column($data, 'quizzes_taken'));

        $courses_with_outline = 0;
        $avg_usage_percentage = 0;

        foreach ($data as $row) {
            if ($row['course_outline_added'] !== 'no') {
                $courses_with_outline++;
            }
            $avg_usage_percentage += $row['usage_activity_percent'];
        }

        $avg_usage_percentage = $total_courses > 0 ? round($avg_usage_percentage / $total_courses, 2) : 0;

        $statistics = [
            [
                'key'   => 'total_courses',
                'label' => get_string('totalcourses', 'moodle'),
                'value' => $total_courses,
            ],
            [
                'key'   => 'total_students',
                'label' => get_string('totalstudents', 'moodle'),
                'value' => $total_students,
            ],
            [
                'key'   => 'total_assignments',
                'label' => get_string('totalassignments', 'moodle'),
                'value' => $total_assignments,
            ],
            [
                'key'   => 'total_quizzes',
                'label' => get_string('totalquizzes', 'moodle'),
                'value' => $total_quizzes,
            ],
            [
                'key'   => 'courses_with_outline',
                'label' => get_string('courseswithoutline', 'report_olarcusage'),
                'value' => $courses_with_outline,
            ],
            [
                'key'   => 'avg_usage_percentage',
                'label' => get_string('avgusagepercentage', 'report_olarcusage'),
                'value' => $avg_usage_percentage . '%',
            ],
        ];

        return $statistics;
    }
public function render_graphical_report_page(\moodleform $filterform, bool $showchart, int $semesterid) {
        $data = new \stdClass();
        $data->filterform = $filterform->render();
        $data->showchart = $showchart;
        $data->semesterid = $semesterid;
        // This now renders the BAR chart's JavaScript.
        $data->footerjs = $this->render_from_template('report_olarcusage/bar_chart_js', new \stdClass());
        return $this->render_from_template('report_olarcusage/graphical_report_page', $data);
    }

      public function render_pattern_report_page(\moodleform $filterform, ?array $chartdata, ?string $selectedsemester) {
        $data = new \stdClass();
        $data->filterform = $filterform->render();
        $data->haschartdata = !empty($chartdata);
        $data->selectedsemester = $selectedsemester;
        $data->footerjs = $this->render_from_template('report_olarcusage/pattern_chart_js', new \stdClass());
        
        return $this->render_from_template('report_olarcusage/pattern_report_page', $data);
    }

       /**
     * Renders the School-wise Usage (by Program) report page.
     * This is the final, definitive version.
     */
    public function render_school_report_page(\moodleform $filterform, ?array $chartdata, ?string $selectedschool) {
        $data = new \stdClass();
        $data->filterform = $filterform->render();
        $data->haschartdata = !empty($chartdata);
        $data->selectedschool = $selectedschool;
        $data->footerjs = $this->render_from_template('report_olarcusage/school_chart_js', new \stdClass());
        
        return $this->render_from_template('report_olarcusage/school_report_page', $data);
    }

    /**
* Renders the Semester Comparison report page.
*/
public function render_comparison_report_page(?array $chartdata) {
$data = new \stdClass();
$data->haschartdata = !empty($chartdata);
$data->footerjs = $this->render_from_template('report_olarcusage/comparison_chart_js', new \stdClass());
return $this->render_from_template('report_olarcusage/comparison_report_page', $data);
}
}

