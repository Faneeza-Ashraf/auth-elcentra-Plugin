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
global $CFG, $DB, $OUTPUT, $PAGE, $USER, $SESSION;
require_once(__DIR__ . '/lib.php');

// If a user is now logged in, check for a guest cart in their session and merge it.
if (isloggedin() && !isguestuser($USER)) {
    local_cart_merge_guest_cart_on_login($USER->id);
}

$action = optional_param('action', '', PARAM_ALPHANUMEXT);
if ($action === 'remove_item') {
    require_sesskey();
    $cartitemid = required_param('cartitemid', PARAM_INT);

    if (isloggedin() && !isguestuser($USER)) {
        // For logged-in users, $cartitemid is the ID from the local_cart_items table.
        $DB->delete_records('local_cart_items', ['id' => $cartitemid, 'userid' => $USER->id]);
    } else {
        // For guests, we treat $cartitemid as the course ID.
        if (isset($SESSION->guest_cart[$cartitemid])) {
            unset($SESSION->guest_cart[$cartitemid]);
        }
    }
    redirect(new moodle_url('/local/cart/index.php'), get_string('courseremovedfromcart', 'local_cart'));
}

$pagelayout = 'standard';
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url(new moodle_url('/local/cart/index.php'));
$PAGE->set_title(get_string('cart', 'local_cart'));
$PAGE->set_heading(get_string('cart', 'local_cart'));
$PAGE->requires->css(new moodle_url('/local/cart/styles.css'));
$PAGE->requires->jquery();
$PAGE->requires->strings_for_js(['failedtoupdatecartitem'], 'local_cart');
$PAGE->requires->js_call_amd('local_cart/main', 'init');
$PAGE->requires->js_call_amd('local_cart/cart_icon_update', 'init');

echo $OUTPUT->header();

$totalprice = 0;
$cart_items_for_template = [];
$is_guest = !isloggedin() || isguestuser($USER);

if (!$is_guest) {
    // --- LOGGED-IN USER LOGIC ---
    $sql = "SELECT lci.id AS cartitemid, lci.courseid, lci.price, lci.quantity, lci.licensetype, c.fullname
            FROM {local_cart_items} lci
            JOIN {course} c ON c.id = lci.courseid
            WHERE lci.userid = :userid
            ORDER BY lci.timecreated ASC";
    $raw_cart_items = $DB->get_records_sql($sql, ['userid' => $USER->id]);
    if ($raw_cart_items) {
        foreach ($raw_cart_items as $item) {
            $course = $DB->get_record('course', ['id' => $item->courseid], '*', MUST_EXIST);
            $itemprice = (float)$item->price;
            $itemtotal = $itemprice * (int)$item->quantity;
            $totalprice += $itemtotal;
            $cart_items_for_template[] = (object)[
                'cartitemid' => $item->cartitemid,
                'fullname' => $course->fullname,
                'image' => local_cart_get_course_image_url($course),
                'quantity' => (int)$item->quantity,
                'licensetype' => $item->licensetype,
                'is_individual_licensetype' => ($item->licensetype === 'individual'),
                'is_coupon_licensetype' => ($item->licensetype === 'coupon'),
                'itemprice' => number_format($itemprice, 2),
                'itemtotalformatted' => number_format($itemtotal, 2),
                'viewcourseurl' => new moodle_url('/course/view.php', ['id' => $item->courseid]),
                'removeitemurl' => new moodle_url('/local/cart/index.php', ['action' => 'remove_item', 'cartitemid' => $item->cartitemid, 'sesskey' => sesskey()]),
            ];
        }
    }
} else {
    // --- GUEST USER LOGIC ---
    if (!empty($SESSION->guest_cart)) {
        foreach ($SESSION->guest_cart as $courseid => $item) {
            $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
            $itemprice = (float)$item['price'];
            $itemtotal = $itemprice * (int)$item['quantity'];
            $totalprice += $itemtotal;
            $cart_items_for_template[] = (object)[
                'cartitemid' => $courseid,
                'fullname' => $course->fullname,
                'image' => local_cart_get_course_image_url($course),
                'quantity' => (int)$item['quantity'],
                'licensetype' => $item['licensetype'],
                'is_individual_licensetype' => ($item['licensetype'] === 'individual'),
                'is_coupon_licensetype' => ($item['licensetype'] === 'coupon'),
                'itemprice' => number_format($itemprice, 2),
                'itemtotalformatted' => number_format($itemtotal, 2),
                'viewcourseurl' => new moodle_url('/course/view.php', ['id' => $courseid]),
                'removeitemurl' => new moodle_url('/local/cart/index.php', ['action' => 'remove_item', 'cartitemid' => $courseid, 'sesskey' => sesskey()]),
            ];
        }
    }
}

// Discount and final template data setup
$applied_discount = 0;
$applied_code = '';
$has_discount = false;
if (isset($SESSION->cart_discount)) {
    $applied_discount = (float)$SESSION->cart_discount['amount'];
    $applied_code = format_string($SESSION->cart_discount['code']);
    $totalprice = max(0, $totalprice - $applied_discount);
    $has_discount = true;
}

$loginurl = new moodle_url('/login/index.php', ['wantsurl' => (new moodle_url('/local/cart/index.php'))->out(false)]);

$template_data = [
    'myordersurl' => new moodle_url('/local/cart/my_orders.php'),
    'browsecoursesurl' => new moodle_url('/course/index.php'), // Added for the new button
    'cartitems' => $cart_items_for_template,
    'totalpriceformatted' => number_format($totalprice, 2),
    'isemptycart' => empty($cart_items_for_template),
    'isguest' => $is_guest,
    'loginurl' => $loginurl->out(),
    'paymentitemid' => $is_guest ? 0 : $USER->id,
    'paymentcost' => $totalprice,
    'paymentdescription' => $is_guest ? '' : get_string('cart_purchase_description', 'local_cart', format_string(fullname($USER))),
    'has_discount' => $has_discount,
    'applied_code' => $applied_code,
    'discountformatted' => '-$' . number_format($applied_discount, 2),
    'iszerototal' => ($totalprice == 0 && !empty($cart_items_for_template)),
    'freecheckouturl' => new moodle_url('/local/cart/free_checkout.php', ['sesskey' => sesskey()]),
];

echo $OUTPUT->render_from_template('local_cart/cart_display', $template_data);
echo $OUTPUT->footer();