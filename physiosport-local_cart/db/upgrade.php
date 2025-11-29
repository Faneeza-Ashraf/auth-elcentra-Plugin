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
 * @package   local_cart
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the local_cart plugin.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool True on success.
 */
function xmldb_local_cart_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2024010101) {
        $table = new xmldb_table('local_cart_items');
        $field = new xmldb_field('licensetype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'individual', 'quantity');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2024010101, 'local', 'cart');
    }
    if ($oldversion < 2024010103) {
        $table = new xmldb_table('local_cart_coupons');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('couponcode', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('purchasedbyuserid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timeexpires', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('redeemedbyuserid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timeredeemed', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'active');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('couponcode_uk', XMLDB_KEY_UNIQUE, array('couponcode'));
        $table->add_key('courseid_fk', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->add_key('purchasedby_fk', XMLDB_KEY_FOREIGN, array('purchasedbyuserid'), 'user', array('id'));
        $table->add_key('redeemedby_fk', XMLDB_KEY_FOREIGN, array('redeemedbyuserid'), 'user', array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2024010103, 'local', 'cart');
    }
    if ($oldversion < 2025091100) {
        // Define table local_cart_order_items.
        $table = new xmldb_table('local_cart_order_items');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('orderid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('price', XMLDB_TYPE_NUMBER, '10, 2', null, XMLDB_NOTNULL, null, '0.00');
        $table->add_field('quantity', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1');
        $table->add_field('licensetype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'individual');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('orderid_fk', XMLDB_KEY_FOREIGN, array('orderid'), 'local_cart_orders', array('id'));
        $table->add_key('courseid_fk', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2025091100, 'local', 'cart');
    }

    if ($oldversion < 2025091103) {

            // Define table local_cart_used_discounts to be created.
            $table = new xmldb_table('local_cart_used_discounts');

            // Adding fields to table local_cart_used_discounts.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('discount_code', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timeused', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

            // Adding keys to table local_cart_used_discounts.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

            // Conditionally launch create table for local_cart_used_discounts.
            if (!$dbman->table_exists($table)) {
                $dbman->create_table($table);
            }

            // Cart savepoint reached.
            upgrade_plugin_savepoint(true, 2025091103, 'local', 'cart');
        }
        return true;
    }
