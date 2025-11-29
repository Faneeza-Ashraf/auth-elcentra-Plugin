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

require_once(__DIR__ . '/../../config.php');
global $CFG, $DB, $USER, $SESSION;
require_once(__DIR__ . '/lib.php');

require_login();
require_sesskey();
$totalprice = 0;
$discount = 0;
$cart_items = $DB->get_records('local_cart_items', ['userid' => $USER->id]);

if (empty($cart_items)) {
    redirect(new moodle_url('/local/cart/index.php'));
}

foreach ($cart_items as $item) {
    $totalprice += (float)$item->price * (int)$item->quantity;
}
if (isset($SESSION->cart_discount)) {
    $discount = (float)$SESSION->cart_discount['amount'];
}
$final_total = $totalprice - $discount;

if ($final_total > 0) {
    redirect(new moodle_url('/local/cart/index.php', ['error_message' => 'Cannot complete free checkout for a non-zero total.']));
}

// FIX: Pass the already fetched $cart_items array to the function.
local_cart_create_order_from_cart($USER->id, $cart_items, 'completed');

foreach ($cart_items as $item) {
    $courseid = $item->courseid;
    if ($enrol = enrol_get_plugin('manual')) {
        if ($instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual'], '*', IGNORE_MULTIPLE)) {
           $enrol->enrol_user($instance, $USER->id, get_config('moodle', 'defaultuserroleid'));
        }
    }
}
$DB->delete_records('local_cart_items', ['userid' => $USER->id]);
unset($SESSION->cart_discount);
redirect(new moodle_url('/'), get_string('checkoutcomplete', 'local_cart'), 5);