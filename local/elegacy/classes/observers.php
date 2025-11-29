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
 * Observer class containing methods monitoring various events.
 *
 * @package    local_elegacy
 * @copyright  2023 Syed Zonair <zonair@paktalem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_elegacy;

/**
 * Observer class containing methods monitoring various events.
 *
 * @since      Moodle 4.0+
 * @package    local_elegacy
 * @copyright  2023 Syed Zonair <zonair@paktalem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observers {

    /**
     * Observer to add ELIS records to elegacy tables. And reset the ELIS class.
     *
     * @param \local_recompletion\event\completion_reset $event
     *
     */
    public static function elegacy_course_recompletion_entry(\local_recompletion\event\completion_reset $event) {
        global $DB, $CFG;

        // Event items.
        $userid     = $event->relateduserid;
        $courseid   = $event->courseid;

        // Get the needed ELIS values.
        // ... Get userid of ELIS.
        $elisuserid = $DB->get_field('local_elisprogram_usr_mdl', 'cuserid', ['muserid' => $userid]);
        // ... Get courseid of ELIS.
        $eliscourseid = $DB->get_field_sql(
            "SELECT ec.id
               FROM {course} c
               JOIN {local_elisprogram_crs} ec ON ec.idnumber = c.idnumber
              WHERE c.id = :courseid", ['courseid' => $courseid]
        );
        // ... Get the classid of the ELIS.
        $elisclassid = $DB->get_field('local_elisprogram_cls', 'id', ['courseid' => $eliscourseid]);
        // ... Get the certificate setting id of ELIS.
        $eliscertsettingid = $DB->get_field('local_elisprogram_certcfg', 'id',
            ['entity_id' => $eliscourseid, 'entity_type' => 'COURSE']
        );

        // Get the class enrol record of ELIS.
        $elisclassenrol = $DB->get_record('local_elisprogram_cls_enrol',
            ['userid' => $elisuserid, 'classid' => $elisclassid]
        );
        // Get the certificate issued record of ELIS.
        $eliscertissued = $DB->get_record('local_elisprogram_certissued',
            ['cm_userid' => $elisuserid, 'cert_setting_id' => $eliscertsettingid]
        );

        // If the certificate is not yet issued.
        if (!$eliscertissued) {
            require_once($CFG->dirroot . '/local/elisprogram/lib/lib.php');
            pm_issue_certificates();

            // Get the certificate issued record of ELIS, AGAIN.
            $eliscertissued = $DB->get_record('local_elisprogram_certissued',
                ['cm_userid' => $elisuserid, 'cert_setting_id' => $eliscertsettingid]
            );
        }

        // Insert the ELIS records in the eLegacy plugin tables.
        self::store_elis_records_to_elegacy($elisclassenrol, $eliscertissued);

        // Reset ELIS.
        $eliscertissuedid = $eliscertissued->id;
        self::reset_elis($elisclassenrol, $eliscertissuedid);
    }

    /**
     * Store the ELIS enrol & certissued records of the user to elegacy tables.
     *
     * @param object $elisclassenrol
     * @param object $eliscertissued
     *
     */
    private static function store_elis_records_to_elegacy(object $elisclassenrol, object $eliscertissued) {
        global $DB;

        // Store ELIS class enrol record to elegacy table.
        $dataobject = clone($elisclassenrol);
        unset($dataobject->id);
        $elegacyenrolid = $DB->insert_record('local_elegacy_cls_enrol', $dataobject);

        // Store ELIS certificate issued record to elegacy table.
        $dataobject = clone($eliscertissued);
        $dataobject->elegacyenrolid = $elegacyenrolid; // Save the elegacy enrol id.
        unset($dataobject->id);
        $DB->insert_record('local_elegacy_certissued', $dataobject);
    }

    /**
     * Unenroll the student from ELIS class instance and then re-enroll.
     * Delete the certificate issued record of the student from ELIS table.
     *
     * @param object $elisclassenrol
     * @param int $eliscertissuedid
     *
     */
    private static function reset_elis(object $elisclassenrol, int $eliscertissuedid) {
        global $DB, $CFG;

        // Unenrol user from the class instance of ELIS.
        if (!empty($elisclassenrol)) {
            require_once($CFG->dirroot . '/local/elisprogram/lib/data/student.class.php');
            $association = new \student($elisclassenrol);

            try {
                $association->delete();
            } catch (\Exception $e) {
                throw $e;
            }

            // Enrol user back to class instance of ELIS.
            $association->save();
        }

        // Delete the certificate issued record of ELIS.
        $DB->delete_records('local_elisprogram_certissued', ['id' => $eliscertissuedid]);
    }
}
