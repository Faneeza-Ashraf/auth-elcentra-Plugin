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
 * Checks if a course is in the current user's cart.
 * @param int $courseid The ID of the course to check.
 * @return bool True if in cart, false otherwise.
 */
function local_cart_is_course_in_cart(int $courseid): bool {
    global $DB, $USER;
    if (!isloggedin() || isguestuser($USER)) {
        return false; 
    }
    return $DB->record_exists('local_cart_items', ['userid' => $USER->id, 'courseid' => $courseid]);
}

/**
 * Returns the URL for a course image by attempting to retrieve course overview files.
 * This function mimics the logic from your theme's renderer.
 *
 * @param stdClass $course The course object (from mdl_course).
 * @return string The URL of the course image, or a default Moodle icon if none found.
 */
function local_cart_get_course_image_url(stdClass $course): string {
    global $CFG, $OUTPUT;
    $course_list_element = new core_course_list_element($course);
    $overviewfiles = $course_list_element->get_course_overviewfiles();

    foreach ($overviewfiles as $file) {
        if ($file->is_valid_image()) {
            $url = moodle_url::make_file_url(
                "$CFG->wwwroot/pluginfile.php",
                '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                false 
            );
            return $url->out(); 
        }
    }

    return $OUTPUT->image_url('i/course', 'moodle')->out();
}

/**
 * Hook callback for injecting JavaScript before the footer.
 *
 * @param \core\hook\output\before_footer_html_generation $hook
 */
function local_cart_before_footer_hook(\core\hook\output\before_footer_html_generation $hook) {
    global $PAGE, $USER;
    debugging('local_cart_before_footer_hook is firing!', DEBUG_DEVELOPER);

    if (is_loggedin() && !isguestuser($USER)) {
        $PAGE->requires->js_call_amd('local_cart/cart_icon_update', 'init');
    }
}


/**
 * Merges a guest's session cart into their database cart upon login.
 *
 * @param int $userid The ID of the user who has just logged in.
 */
function local_cart_merge_guest_cart_on_login(int $userid) {
    global $DB, $SESSION;

    if (empty($SESSION->guest_cart)) {
        return; // Nothing to merge.
    }

    foreach ($SESSION->guest_cart as $courseid => $item) {
        // If the user already has this item in their DB cart, skip it to avoid duplicates.
        if ($DB->record_exists('local_cart_items', ['userid' => $userid, 'courseid' => $courseid])) {
            continue;
        }

        // Add the item from the session to the user's DB cart.
        $cart_item = new stdClass();
        $cart_item->userid = $userid;
        $cart_item->courseid = $courseid;
        $cart_item->price = (float)$item['price'];
        $cart_item->quantity = (int)$item['quantity'];
        $cart_item->licensetype = $item['licensetype'];
        $cart_item->timecreated = $item['timecreated'];
        $cart_item->timemodified = time();

        $DB->insert_record('local_cart_items', $cart_item);
    }

    // Important: Clear the guest cart from the session after merging.
    unset($SESSION->guest_cart);
}

/**
 * Creates an order and order items from a given set of cart items.
 *
 * @param int $userid The user ID.
 * @param array $cart_items An array of the user's cart items.
 * @param string $status The status of the order (e.g., 'completed').
 * @param int|null $paymentid The ID from the core_payment table, if applicable.
 * @return int The ID of the newly created order.
 * @throws dml_exception
 */
function local_cart_create_order_from_cart(int $userid, array $cart_items, string $status = 'completed', ?int $paymentid = null): int {
    global $DB, $SESSION, $CFG;

    if (empty($cart_items)) {
        return 0; // No items, so no order is created.
    }

    // Calculate total price directly from the provided cart items.
    $totalprice = 0;
    foreach ($cart_items as $item) {
        $totalprice += (float)$item->price * (int)$item->quantity;
    }

    // Apply any discount stored in the session.
    if (isset($SESSION->cart_discount)) {
        $discount = (float)$SESSION->cart_discount['amount'];
        $totalprice = max(0, $totalprice - $discount);
    }

    // 1. Create the main order record with the correct total.
    $order = new stdClass();
    $order->userid = $userid;
    $order->totalamount = $totalprice;
    $order->currency = !empty($CFG->currency) ? $CFG->currency : 'AUD';
    $order->status = $status;
    $order->paymentid = $paymentid;
    $order->timecreated = time();
    $order->timemodified = $order->timecreated;

    $orderid = $DB->insert_record('local_cart_orders', $order);

    // 2. Create historical order item records.
    foreach ($cart_items as $cart_item) {
        $order_item = new stdClass();
        $order_item->orderid = $orderid;
        $order_item->courseid = $cart_item->courseid;
        $order_item->price = $cart_item->price;
        $order_item->quantity = $cart_item->quantity;

        // FIX: Explicitly check the licensetype property from the passed-in cart item.
        // This is a more robust way to ensure the correct value is saved.
        if (isset($cart_item->licensetype) && $cart_item->licensetype === 'coupon') {
            $order_item->licensetype = 'coupon';
        } else {
            $order_item->licensetype = 'individual';
        }

        $DB->insert_record('local_cart_order_items', $order_item);
    }

    return $orderid;
}