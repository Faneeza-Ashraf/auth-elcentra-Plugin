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
 * Defines the scheduled task for checking user subscriptions.
 *
 * @package    local
 * @subpackage signup
 *@copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_signup_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024052305) {

        $table = new xmldb_table('local_signup_data');
        if ($dbman->field_exists($table, 'files_itemid')) {
            $field = new xmldb_field('files_itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, true, '0');
            $dbman->rename_field($table, $field, 'identitydoc_itemid');
        }
        $field = new xmldb_field('passportdoc_itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, true, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('academichistory', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, false, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2024052305, 'local', 'signup');
    }

    return true;
}