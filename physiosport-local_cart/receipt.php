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
 * Local cart receipt page (Created by Endush Fairy)
 *
 * @package   local_cart
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_login();
global $DB, $PAGE, $SESSION, $OUTPUT;
if (isset($SESSION->notifications)) {
    $SESSION->notifications = [];
}
$PAGE->set_url('/local/cart/receipt.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Order Completed');
$output = $PAGE->get_renderer('core');

echo $output->header();

try {
    if (empty($SESSION->local_cart_latest_orderid)) {
        echo $OUTPUT->notification('Could not find a recent order to display.');
        echo $output->footer();
        exit;
    }
    $orderid = $SESSION->local_cart_latest_orderid;
    
    // Get the base order from local_cart_orders to find the paymentid.
    $order = $DB->get_record('local_cart_orders', ['id' => $orderid]);
    if (!$order) {
        throw new \moodle_exception('invalidorderid', 'local_cart', '', null, $orderid);
    }

    // Use the paymentid to get the detailed purchase order from your custom table.
    $purchase_order = $DB->get_record('purchase_orders', ['captureid' => $order->paymentid]);
    if (!$purchase_order) {
        throw new \moodle_exception('Could not find corresponding purchase order details.');
    }

    // Fetch the items associated with this order to display them.
    $orderitems = $DB->get_records('local_cart_order_items', ['orderid' => $orderid]);
    $billedto_user = $DB->get_record('user', ['id' => $order->userid]);
    if (!$billedto_user) {
        throw new \moodle_exception('invaliduserid', 'error', '', null, $order->userid);
    }

    $itemdata = [];
    foreach ($orderitems as $oi) {
        $course = $DB->get_record('course', ['id' => $oi->courseid], 'fullname');
        $title = $course ? $course->fullname : 'Unknown Item';
        $price = isset($oi->price) ? (float)$oi->price : 0.00;
        $quantity = isset($oi->quantity) ? (int)$oi->quantity : 1;
        $line_cost = $price * $quantity;
        $itemdata[] = [
            'title' => $title,
            'type' => $oi->licensetype ?? 'N/A',
            'qty' => $quantity,
            'cost' => '$' . number_format($line_cost, 2)
        ];
    }

    // Use the data from your purchase_orders table for all calculations.
    $subtotal = (float)$purchase_order->totalprice;
    $total = (float)$purchase_order->discounted_price;
    $discount = $subtotal - $total;

    // Prepare the data for the template.
    $templatedata = [
        'total' => '$' . number_format($total, 2),
        'subtotal' => '$' . number_format($subtotal, 2),
        'coupon' => ($discount > 0) ? '-$' . number_format($discount, 2) : null,
        'billed_to_name' => fullname($billedto_user),
        'billed_to_email' => $billedto_user->email,
        'items' => $itemdata
    ];

    echo $output->render_from_template('local_cart/receipt_page', $templatedata);
    unset($SESSION->local_cart_latest_orderid);

} catch (\Exception $e) {
    echo $OUTPUT->notification($e->getMessage(), 'error');
}

echo $output->footer();