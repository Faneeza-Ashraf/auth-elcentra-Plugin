<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_presentation
 * @category    upgrade
 * @copyright   2025 Endush Fairy <endush.fairy@paktaleem.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_presentation upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_presentation_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025110703) {

        // Define table presentation_submissions to be created.
        $table = new xmldb_table('presentation_submissions');

        // Adding fields to table presentation_submissions.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('presentationid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table presentation_submissions.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('fk_presentation', XMLDB_KEY_FOREIGN, ['presentationid'], 'presentation', ['id']);
        $table->add_key('fk_user', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Conditionally launch create table for presentation_submissions.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Presentation savepoint reached.
        upgrade_mod_savepoint(true, 2025110703, 'presentation');
    }
        if ($oldversion < 2025110704) {

        // Define field grade to be added to presentation_submissions.
        $table = new xmldb_table('presentation_submissions');
        $field = new xmldb_field('grade', XMLDB_TYPE_NUMBER, '10, 2', null, null, null, null, 'timemodified');

        // Conditionally launch add field grade.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field teachercomment to be added to presentation_submissions.
        $table = new xmldb_table('presentation_submissions');
        $field = new xmldb_field('teachercomment', XMLDB_TYPE_TEXT, null, null, null, null, null, 'grade');

        // Conditionally launch add field teachercomment.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Presentation savepoint reached.
        upgrade_mod_savepoint(true, 2025110704, 'presentation');
    }
     if ($oldversion < 2025110707) {

        // Changing type of field grade on table presentation_submissions to char.
        $table = new xmldb_table('presentation_submissions');
        $field = new xmldb_field('grade', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'timemodified');

        // Launch change of type for field grade.
        $dbman->change_field_type($table, $field);

        // Presentation savepoint reached.
        upgrade_mod_savepoint(true, 2025110707, 'presentation');
     }

  if ($oldversion < 2025110709) {

        // Define field grade to be added to presentation.
        $table = new xmldb_table('presentation');
        $field = new xmldb_field('grade', XMLDB_TYPE_NUMBER, '10', null, null, null, '100', 'introformat');

        // Conditionally launch add field grade.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
         $table = new xmldb_table('presentation');
        $field = new xmldb_field('scale', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'grade');

        // Conditionally launch add field scale.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Define field gradepass to be added to presentation.
        $table = new xmldb_table('presentation');
        $field = new xmldb_field('gradepass', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0', 'scale');

        // Conditionally launch add field gradepass.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

         // Define field blindmarking to be added to presentation.
        $table = new xmldb_table('presentation');
        $field = new xmldb_field('blindmarking', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'gradepass');

        // Conditionally launch add field blindmarking.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field hidegrader to be added to presentation.
        $table = new xmldb_table('presentation');
        $field = new xmldb_field('hidegrader', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'blindmarking');

        // Conditionally launch add field hidegrader.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
         // Define field markingworkflow to be added to presentation.
        $table = new xmldb_table('presentation');
        $field = new xmldb_field('markingworkflow', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'hidegrader');

        // Conditionally launch add field markingworkflow.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field markingallocation to be added to presentation.
        $table = new xmldb_table('presentation');
        $field = new xmldb_field('markingallocation', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'markingworkflow');

        // Conditionally launch add field markingallocation.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Presentation savepoint reached.
        upgrade_mod_savepoint(true, 2025110709, 'presentation');
    }
        if ($oldversion < 2025110710) {

        // Define field teachercommentformat to be added to presentation_submissions.
        $table = new xmldb_table('presentation_submissions');
        $field = new xmldb_field('teachercommentformat', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'teachercomment');

        // Conditionally launch add field teachercommentformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Presentation savepoint reached.
        upgrade_mod_savepoint(true, 2025110710, 'presentation');
    }
if ($oldversion < 2025110712) {

        // Clean the existing data before changing the column type.
        // This SQL statement finds any grade that is not a valid number and sets it to '0'.
        // This prevents the 'Incorrect DECIMAL value' error during the upgrade.
        $sql = "UPDATE {presentation_submissions} SET grade = '0' WHERE grade IS NULL OR grade = '' OR grade NOT REGEXP '^[0-9.-]+$'";
        $DB->execute($sql);

        // Now that the data is clean, we can safely change the field type.
        $table = new xmldb_table('presentation_submissions');
        $field = new xmldb_field('grade', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, '0.00000', 'timemodified');

        // Launch the change of the field type.
        $dbman->change_field_type($table, $field);

        // Presentation savepoint reached.
        upgrade_mod_savepoint(true, 2025110712, 'presentation');
    }
    if ($oldversion < 2025110716) {

        // Define field allowsubmissionsfromdate to be added to presentation.
        $table = new xmldb_table('presentation');
        $field = new xmldb_field('allowsubmissionsfromdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'markingallocation');

        // Conditionally launch add field allowsubmissionsfromdate.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field duedate to be added to presentation.
        $table = new xmldb_table('presentation');
        $field = new xmldb_field('duedate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'allowsubmissionsfromdate');

        // Conditionally launch add field duedate.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field cutoffdate to be added to presentation.
        $table = new xmldb_table('presentation');
        $field = new xmldb_field('cutoffdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'duedate');

        // Conditionally launch add field cutoffdate.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

         // Define field maxfiles to be added to presentation.
        $table = new xmldb_table('presentation');
        $field = new xmldb_field('maxfiles', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'cutoffdate');

        // Conditionally launch add field maxfiles.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field maxsize to be added to presentation.
        $table = new xmldb_table('presentation');
        $field = new xmldb_field('maxsize', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'maxfiles');

        // Conditionally launch add field maxsize.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Presentation savepoint reached.
        upgrade_mod_savepoint(true, 2025110716, 'presentation');
    }


    return true;
}