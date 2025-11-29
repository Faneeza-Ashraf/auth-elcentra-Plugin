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

define('NO_DEBUG_DISPLAY', true); // Prevent any accidental output from breaking JSON.
require_once(__DIR__ . '/../../config.php');
global $CFG, $DB, $USER, $SESSION;
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/../../blocks/coursemanagement/lib.php');

header('Content-Type: application/json');

try {
    $action = required_param('action', PARAM_ALPHANUMEXT);
    $isloggedin = isloggedin() && !isguestuser();

    // Only require a session key if the user is logged in.
    if ($isloggedin) {
        require_sesskey();
    }

    switch ($action) {
        case 'add_to_cart':
            $courseid = required_param('course_id', PARAM_INT);
            $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

            // Check if the item is already in the cart and send the specific JSON response.
            if (($isloggedin && $DB->record_exists('local_cart_items', ['userid' => $USER->id, 'courseid' => $courseid])) ||
                (!$isloggedin && !empty($SESSION->guest_cart[$courseid]))) {
                echo json_encode(['status' => 'error', 'code' => 'already_in_cart', 'message' => get_string('alreadyaddedtocarderror', 'local_cart')]);
                exit;
            }

            $price_to_store = 0.00;
            if (function_exists('block_coursemanagement_get_price')) {
                $price_to_store = (float)block_coursemanagement_get_price($course->id);
            }

            if ($isloggedin) {
                $cart_item = new stdClass();
                $cart_item->userid = $USER->id;
                $cart_item->courseid = $courseid;
                $cart_item->timecreated = time();
                $cart_item->price = $price_to_store;
                $cart_item->quantity = 1;
                $cart_item->licensetype = 'individual';
                $DB->insert_record('local_cart_items', $cart_item);
            } else {
                if (!isset($SESSION->guest_cart)) $SESSION->guest_cart = [];
                $SESSION->guest_cart[$courseid] = [
                    'courseid' => $courseid, 'price' => $price_to_store, 'quantity' => 1,
                    'licensetype' => 'individual', 'timecreated' => time()
                ];
            }
            echo json_encode(['status' => 'success', 'message' => get_string('courseaddedtocart', 'local_cart', format_string($course->fullname))]);
            break;

        case 'update_cart_item':
            $cartitemid = required_param('cartitemid', PARAM_INT);
            $newquantity = optional_param('quantity', 1, PARAM_INT);
            $newlicensetype = optional_param('licensetype', 'individual', PARAM_ALPHANUMEXT);
            if ($newquantity < 1) $newquantity = 1;

            $totalprice = 0;
            $itemtotal = 0;

            if ($isloggedin) {
                $cart_item = $DB->get_record('local_cart_items', ['id' => $cartitemid, 'userid' => $USER->id]);
                if (!$cart_item) throw new moodle_exception('itemnotfoundincart', 'local_cart');

                $cart_item->quantity = $newquantity;
                $cart_item->licensetype = $newlicensetype;
                $cart_item->timemodified = time();
                $DB->update_record('local_cart_items', $cart_item);
                $itemtotal = (float)$cart_item->price * $cart_item->quantity;

                $allitems = $DB->get_records('local_cart_items', ['userid' => $USER->id]);
                foreach ($allitems as $item) {
                    $totalprice += (float)$item->price * (int)$item->quantity;
                }
            } else {
                // For guests, cartitemid is the courseid.
                if (!isset($SESSION->guest_cart[$cartitemid])) throw new moodle_exception('itemnotfoundincart', 'local_cart');

                $SESSION->guest_cart[$cartitemid]['quantity'] = $newquantity;
                $SESSION->guest_cart[$cartitemid]['licensetype'] = $newlicensetype;
                $itemtotal = (float)$SESSION->guest_cart[$cartitemid]['price'] * $newquantity;
                
                foreach ($SESSION->guest_cart as $item) {
                    $totalprice += (float)$item['price'] * (int)$item['quantity'];
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => get_string('cartitemupdated', 'local_cart'),
                'cartitemid' => $cartitemid,
                'newquantity' => $newquantity,
                'newlicensetype' => $newlicensetype,
                'itemtotalformatted' => number_format($itemtotal, 2),
                'totalpriceformatted' => number_format($totalprice, 2)
            ]);
            break;
          
                   case 'get_cart_count':
        $count = 0;
        if (isloggedin() && !isguestuser($USER)) {
            // Logged-in user: count items from the database.
            $count = (int) $DB->count_records('local_cart_items', ['userid' => $USER->id]);
        } else {
            // Guest user: count items from the session.
            if (!empty($SESSION->guest_cart) && is_array($SESSION->guest_cart)) {
                $count = count($SESSION->guest_cart);
            }
        }
        // Send the final count.
        echo json_encode(['status' => 'success', 'count' => $count]);
        exit;
        
        case 'apply_discount_code':
        case 'apply_reseller_code':
            $code = required_param('code', PARAM_TEXT);
            if (isset($SESSION->cart_discount)) {
                throw new moodle_exception('discountalreadyapplied', 'local_cart');
            }

            $discount_record = null;
            if ($action === 'apply_discount_code') {
                $discount_record = $DB->get_record('purchase_discount_codes', ['code_name' => $code, 'status' => 1]);
            } else {
                $reseller = $DB->get_record('purchase_resellers', ['code_name' => $code, 'status' => 1]);
                if (!$reseller) throw new moodle_exception('invalidresellercode', 'local_cart');
                $discount_record = $DB->get_record('purchase_discount_codes', ['resellerid' => $reseller->id, 'status' => 1]);
            }
            if (!$discount_record) {
                throw new moodle_exception('invalidorExpiredCode', 'local_cart');
            }

            $cart_total = 0;
            if ($isloggedin) {
                $cart_items = $DB->get_records('local_cart_items', ['userid' => $USER->id]);
                foreach ($cart_items as $item) $cart_total += (float)$item->price * (int)$item->quantity;
            } else {
                if (!empty($SESSION->guest_cart)) {
                    foreach ($SESSION->guest_cart as $item) $cart_total += (float)$item['price'] * (int)$item['quantity'];
                }
            }

            $discount_amount = 0;
            if ($discount_record->type === 'percentage') {
                $discount_amount = ($cart_total * (float)$discount_record->amount) / 100;
            } else {
                $discount_amount = (float)$discount_record->amount;
            }

            $new_total = max(0, $cart_total - $discount_amount);
            $SESSION->cart_discount = ['code' => $code, 'amount' => $discount_amount];

            echo json_encode([
                'status' => 'success',
                'message' => ($action === 'apply_discount_code') ? get_string('discountapplied', 'local_cart') : get_string('resellerdiscountapplied', 'local_cart'),
                'discountformatted' => '-$' . number_format($discount_amount, 2),
                'newtotalformatted' => '$' . number_format($new_total, 2),
                'codename' => $code,
                'iszerototal' => ($new_total == 0)
            ]);
            break;

        case 'remove_discount':
            unset($SESSION->cart_discount);
            $cart_total = 0;
            if ($isloggedin) {
                $cart_items = $DB->get_records('local_cart_items', ['userid' => $USER->id]);
                foreach ($cart_items as $item) $cart_total += (float)$item->price * (int)$item->quantity;
            } else {
                if (!empty($SESSION->guest_cart)) {
                    foreach ($SESSION->guest_cart as $item) $cart_total += (float)$item['price'] * (int)$item['quantity'];
                }
            }
            echo json_encode([
                'status' => 'success',
                'newtotalformatted' => '$' . number_format($cart_total, 2),
                'iszerototal' => ($cart_total == 0 && $cart_total >= 0)
            ]);
            break;

        default:
            throw new moodle_exception('unknownaction', 'local_cart');
    }

} catch (Exception $e) {
    // Gracefully catch all exceptions and return them as a JSON error.
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}