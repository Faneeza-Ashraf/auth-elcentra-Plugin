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
 * Displays the user's order history.
 *
 * @package   local_cart
 * @copyright 2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
global $DB, $OUTPUT, $PAGE, $USER;

require_login();

$url = new moodle_url('/local/cart/my_orders.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_title(get_string('myorders', 'local_cart'));
$PAGE->set_heading(get_string('myorders', 'local_cart'));
$PAGE->set_pagelayout('standard');
$PAGE->requires->css(new moodle_url('/local/cart/styles.css'));

$PAGE->navbar->add(get_string('myorders', 'local_cart'));

// Fetch all orders for the current user, newest first.
$orders = $DB->get_records('local_cart_orders', ['userid' => $USER->id], 'timecreated DESC');

$template_data = [];
$template_data['orders'] = [];

if (!empty($orders)) {
    foreach ($orders as $order) {

        $purchase_order = null;
        if (!empty($order->paymentid)) {
            $purchase_order = $DB->get_record('purchase_orders', ['captureid' => $order->paymentid]);
        }// Added by Endush Fairy

        // Fetch all items associated with this order.
        $order_items_raw = $DB->get_records('local_cart_order_items', ['orderid' => $order->id]);

        $order_data = new stdClass();
        $order_data->date = userdate($order->timecreated, '%d/%m/%Y');
        $order_data->items = [];
        
        // This is only used as a fallback for orders made before the purchase_orders table was used.
        $running_subtotal_fallback = 0.0;

        foreach ($order_items_raw as $item) {
            $course = $DB->get_record('course', ['id' => $item->courseid], 'fullname');
            $price = isset($item->price) ? (float) $item->price : 0.0;
            $quantity = isset($item->quantity) ? (int) $item->quantity : 1;
            $subtotal = $price * $quantity;
            $running_subtotal_fallback += $subtotal;

            $item_data = new stdClass();
            $item_data->coursename = $course ? $course->fullname : get_string('unknowncourse', 'local_cart');
            $item_data->licensetype = ucfirst(isset($item->licensetype) ? (string) $item->licensetype : 'individual');
            $item_data->priceformatted = '$' . number_format($price, 2);
            $item_data->quantity = $quantity;
            $item_data->subtotalformatted = '$' . number_format($subtotal, 2);
            $order_data->items[] = $item_data;
        }

        // Determine the correct totals to display.
        $original_total = 0.0;
        $final_total = 0.0;
        
        if ($purchase_order) {
            // BEST CASE: We have a detailed record from the purchase_orders table.
            $original_total = (float) $purchase_order->totalprice;
            $final_total = (float) $purchase_order->discounted_price;
        } else {
            // FALLBACK: For older orders without a detailed record. Assume no discount.
            $original_total = $running_subtotal_fallback;
            $final_total = $running_subtotal_fallback;
        } // Added by Endush Fairy

        $order_data->hasdiscount = ($original_total > $final_total);
        $order_data->originaltotalformatted = '$' . number_format($original_total, 2);
        $order_data->finaltotalformatted = '$' . number_format($final_total, 2);

        $template_data['orders'][] = $order_data;
    }
}

$template_data['hasorders'] = !empty($orders);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_cart/my_orders_page', $template_data);
echo $OUTPUT->footer();