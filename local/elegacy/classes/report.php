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
 * The class definition of the report.
 *
 * @package    local_elegacy
 * @copyright  2021-2023 Syed Zonair, Syed {@link http://paktaleem.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Define your namespace and any other code.
namespace local_elegacy;

/**
 * Report class to get the users historical report.
 */
class report {

    /**
     * CLASS_ENROL_TABLE
     *
     * @var string
     */
    const CLASS_ENROL_TABLE = 'local_elegacy_cls_enrol';

    /**
     * MOODLE userid.
     *
     * @var int
     */
    public int $userid;

    /**
     * ELIS userid.
     *
     * @var int
     */
    public int $elisuserid;

    /**
     * Constructor.
     *
     * @param int|null $userid
     *
     */
    public function __construct(?int $userid = null) {
        global $USER;

        if (is_null($userid)) {
            $this->userid = $USER->id;
        } else {
            $this->userid = $userid;
        }

        $this->elisuserid   = $this->get_elis_userid();
    }

    /**
     * Get the user fullname.
     *
     * @return string
     *
     */
    private function get_username(): string {
        $user = \core_user::get_user($this->userid);
        return fullname($user);
    }

    /**
     * Get user ELIS related id.
     * @return int
     *
     */
    private function get_elis_userid(): int {
        global $DB;
        return $DB->get_field('local_elisprogram_usr_mdl', 'cuserid', ['muserid' => $this->userid]);
    }

    /**
     * Get the MOODLE course name.
     *
     * @param int $classid
     *
     * @return string
     *
     */
    private function get_class_name(int $classid): string {
        $moodlecourseid = moodle_get_course($classid);
        return get_course($moodlecourseid)->fullname;
    }

    /**
     * Get the list of classids, having user enrollment records.
     *
     * @return array
     *
     */
    private function get_list_of_passed_user_classes(): array {
        global $DB;

        $tablename = self::CLASS_ENROL_TABLE;
        return $DB->get_fieldset_sql(
            "SELECT classid
               FROM {{$tablename}}
              WHERE userid = :userid
           GROUP BY classid", ['userid' => $this->elisuserid]
        );
    }

    /**
     * Get enrollments of the user in the class.
     *
     * @param int $classid
     *
     * @return array
     *
     */
    private function get_enrolments_of_user_in_class(int $classid): array {
        global $DB;
        $enrolments = $DB->get_records(self::CLASS_ENROL_TABLE,
            ['classid' => $classid, 'userid' => $this->elisuserid], 'completetime DESC',
            'id, completetime'
        );
        return array_values($enrolments);
    }

    /**
     * Get the template context array.
     *
     * @return array
     *
     */
    private function get_context_for_template(): array {
        $templatecontext = [];

        $templatecontext['userid']      = $this->userid;
        $templatecontext['username']    = $this->get_username();
        $templatecontext['classes']     = [];

        $classlist = $this->get_list_of_passed_user_classes();
        foreach ($classlist as $classid) {
            $classobject                = [];
            $classobject['classid']     = $classid;
            $classobject['classname']   = $this->get_class_name($classid);
            $classobject['enrolments']  = $this->get_enrolments_of_user_in_class($classid);

            // Insert into template context.
            $templatecontext['classes'][] = (object) $classobject;
        }

        return $templatecontext;
    }

    /**
     * Display the report.
     */
    public function display() {
        global $OUTPUT;

        $templatecontext = $this->get_context_for_template();
        return $OUTPUT->render_from_template('local_elegacy/report', $templatecontext);
    }
}
