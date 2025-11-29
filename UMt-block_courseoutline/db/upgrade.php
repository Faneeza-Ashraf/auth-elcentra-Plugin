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
 * Database upgrade script for the Course Outline block.
 *
 * @package    block_courseoutline
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function for the Course Outline block
 *
 * @param int $oldversion The old version of the plugin
 * @return bool True on success
 */
function xmldb_block_courseoutline_upgrade($oldversion) {
    global $DB;

    // It is very important to get the database manager from the global DB object
    $dbman = $DB->get_manager();

    // The version number should match the one in your version.php file.
    if ($oldversion < 2025071501) {

        // Define table block_courseoutline to be altered.
        $table = new xmldb_table('block_courseoutline');

        // Define fields to be added.
        $field_quizzes = new xmldb_field('totalquizzes', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'timeuploaded');
        $field_assignments = new xmldb_field('totalassignments', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'totalquizzes');
        $field_presentations = new xmldb_field('totalpresentations', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'totalassignments');
        $field_workshops = new xmldb_field('totalworkshops', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'totalpresentations');

        // Conditionally add the fields.
        if (!$dbman->field_exists($table, $field_quizzes)) {
            $dbman->add_field($table, $field_quizzes);
        }
        if (!$dbman->field_exists($table, $field_assignments)) {
            $dbman->add_field($table, $field_assignments);
        }
        if (!$dbman->field_exists($table, $field_presentations)) {
            $dbman->add_field($table, $field_presentations);
        }
        if (!$dbman->field_exists($table, $field_workshops)) {
            $dbman->add_field($table, $field_workshops);
        }

        // Courseoutline savepoint reached.
        upgrade_block_savepoint(true, 2025071501, 'courseoutline');
    }

    return true;
}