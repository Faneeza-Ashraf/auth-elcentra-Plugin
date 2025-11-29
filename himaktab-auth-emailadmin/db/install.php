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
 * @package    auth
 * @subpackage emailadmin
 *@copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

function xmldb_auth_emailadmin_install() {
    global $DB;

    $dbman = $DB->get_manager();
    $table = new xmldb_table('auth_emailadmin_subscriptions');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('programid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('stripe_subscription_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('status', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'active');
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('timestart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('timeend', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    $table->add_key('userid_programid', XMLDB_KEY_UNIQUE, ['userid', 'programid']);
    $table->add_key('stripe_sub_id', XMLDB_KEY_UNIQUE, ['stripe_subscription_id']);
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    
    return true;
}