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
 * Course data source for the Custom Report plugin.
 *
 * @package    report_olarcusage
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_olarcusage\external;

use external_function_parameters;
use external_value;
use external_single_structure;
use external_api;

require_once($CFG->dirroot . '/report/olarcusage/lib.php');

class chart_data extends \external_api {

    public static function get_data_parameters() {
        return new external_function_parameters([
            'semesterid' => new external_value(PARAM_INT, 'The ID of the semester category')
        ]);
    }

    public static function get_data($semesterid) {
        global $CFG, $DB;
        $params = self::validate_parameters(self::get_data_parameters(), ['semesterid' => $semesterid]);
        self::validate_context(context_system::instance());
        require_capability('report/olarcusage:view', context_system::instance());
        return \report_olarcusage_get_chart_data($params['semesterid']);
    }

    public static function get_data_returns() {
        return new \external_multiple_structure(
            new external_single_structure([
                'school_name' => new external_value(PARAM_TEXT, 'Name of the school'),
                'usage_percentage' => new external_value(PARAM_FLOAT, 'Usage percentage'),
                'used_courses' => new external_value(PARAM_INT, 'Number of used courses'),
                'total_courses' => new external_value(PARAM_INT, 'Total courses in school'),
            ])
        );
    }
}