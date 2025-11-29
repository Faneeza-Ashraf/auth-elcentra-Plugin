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
// GNU General Public License for more details//
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

namespace local_cart\payment;

use core_payment\local\entities\payable;

class service_provider implements \core_payment\local\callback\service_provider {

public static function get_payable(string $paymentarea, int $itemid): payable {
    global $DB, $CFG, $SESSION;
    $userid = $itemid;
    if ($paymentarea !== 'local_cart') {
        throw new \moodle_exception('invalidpaymentarea', 'local_cart');
    }
    // Calculate cart total
    $cartitems = $DB->get_records('local_cart_items', ['userid' => $userid]);
    $totalcost = 0.00;
    foreach ($cartitems as $item) {
        $totalcost += ((float)$item->price * (int)$item->quantity);
    }
    if (isset($SESSION->cart_discount)) {
        $discount_amount = (float)$SESSION->cart_discount['amount'];
        $totalcost -= $discount_amount;
        if ($totalcost < 0) {
            $totalcost = 0;
        }
    }
    $currency = !empty($CFG->currency) ? $CFG->currency : 'AUD';
    // Find a valid enabled payment account
    $accountid = 0;
    // If user has already selected a gateway, use it
    if (!empty($SESSION->selected_gateway)) {
        $selectedgateway = $SESSION->selected_gateway;
        $payment_account = $DB->get_record('payment_accounts', [
            'gatewayname' => $selectedgateway,
            'enabled' => 1
        ]);
        if ($payment_account) {
            $accountid = $payment_account->id;
        }
    }
    // If no gateway selected or not found, pick the first enabled one
    if (!$accountid) {
        $payment_account = $DB->get_record('payment_accounts', ['enabled' => 1], '*', IGNORE_MULTIPLE);
        if ($payment_account) {
            $accountid = $payment_account->id;
        } else {
            throw new \moodle_exception('paymentgatewaynotfound', 'core_payment');
        }
    }
    return new payable($totalcost, $currency, $accountid);
} // Updated by Endush Fairy for all enabled payment gateways

    public static function get_success_url(string $paymentarea, int $itemid): \moodle_url {
        return new \moodle_url('/local/cart/receipt.php');
    } // Updated by Endush Fairy
     public static function deliver_order(string $paymentarea, int $itemid, int $paymentid, int $recipientuserid): bool {
        global $DB, $SESSION;
        $userid = $itemid;

        require_once(__DIR__ . '/../../lib.php');

        if ($paymentarea !== 'local_cart' || $userid !== $recipientuserid) {
            return false;
        }
        
        $cartitems = $DB->get_records('local_cart_items', ['userid' => $userid]);
        if (empty($cartitems)) {
            return false;
        }
// Code added for mdl_purchase_orders starts here
        $totalprice = 0;
        foreach ($cartitems as $item) {
            $totalprice += ((float)$item->price * (int)$item->quantity);
        }
        $discounted_price = $totalprice;
        $discountcodeid = null;
        $resellercodeid = null;
        if (isset($SESSION->cart_discount)) {
            $discount_amount = (float)$SESSION->cart_discount['amount'];
            $discount_code_name = $SESSION->cart_discount['code'];
            $discounted_price = max(0, $totalprice - $discount_amount);
            if ($reseller = $DB->get_record('purchase_resellers', ['code_name' => $discount_code_name])) {
                $resellercodeid = $reseller->id;
                if ($discount_record = $DB->get_record('purchase_discount_codes', ['resellerid' => $resellercodeid])) {
                    $discountcodeid = $discount_record->id;
                }
            } else {
                if ($discount_record = $DB->get_record('purchase_discount_codes', ['code_name' => $discount_code_name])) {
                    $discountcodeid = $discount_record->id;
                }
            }
        }
        $purchaseorder = new \stdClass();
        $purchaseorder->userid = $userid;
        $purchaseorder->type = 'cart_purchase';
        $purchaseorder->discounted_price = $discounted_price;
        $purchaseorder->captureid = $paymentid;
        $purchaseorder->totalprice = $totalprice;
        $purchaseorder->discountcodeid = $discountcodeid;
        $purchaseorder->resellercodeid = $resellercodeid;
        $purchaseorder->timecreated = time();
        $purchaseorder->timemodified = time();
        $purchaseorder->timeprocessed = time();
        $purchaseorder->status = 'paid';
        $purchaseorder->is_renewal = 0;
        $DB->insert_record('purchase_orders', $purchaseorder); //Added by Endush Fairy

        $orderid = local_cart_create_order_from_cart($userid, $cartitems, 'completed', $paymentid); // Updated by Endush Fairy
        $SESSION->local_cart_latest_orderid = $orderid;

        $purchaser = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        $studentrole = $DB->get_record('role', ['archetype' => 'student']);
        if (!$studentrole) {
            return false;
        }

        $manualenrolplugin = \enrol_get_plugin('manual');
        if (!$manualenrolplugin) {
            return false;
        }
        
        $generatedcouponsbycourse = [];

        foreach ($cartitems as $item) {
            $courseid = $item->courseid;
            $context = \context_course::instance($courseid);
            $course = $DB->get_record('course', ['id' => $courseid], 'id, fullname', MUST_EXIST);

            if ($item->licensetype === 'individual') {
                if (\is_enrolled($context, $userid)) {
                    continue;
                }
                $enrolinstance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual', 'status' => 0], '*', IGNORE_MULTIPLE);
                if ($enrolinstance) {
                     $manualenrolplugin->enrol_user($enrolinstance, $userid, $studentrole->id, time());
                }

            } else if ($item->licensetype === 'coupon') {
                $quantity = (int)$item->quantity;
                $couponsforthisitem = [];

                for ($i = 0; $i < $quantity; $i++) {
                    $coupon = new \stdClass();
                    $coupon->courseid = $courseid;
                    $coupon->purchaserid = $userid;
                    $coupon->userid = null;
                    $coupon->timecreated = time();
                    $coupon->status = 0;

                    do {
                        $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
                    } while ($DB->record_exists('coupon', ['couponcode' => $code]));

                    $coupon->couponcode = $code;
                    $DB->insert_record('coupon', $coupon);
                    $couponsforthisitem[] = $code;
                }
                
                if (!empty($couponsforthisitem)) {
                    $coursename = $course->fullname;
                    $generatedcouponsbycourse[$coursename] = [
                        'quantity' => $quantity,
                        'codes' => $couponsforthisitem
                    ];
                }
            }
        }

        if (!empty($generatedcouponsbycourse)) {
            $supportuser = \core_user::get_support_user();
            $subject = 'Your Physiosports Education Purchase Confirmation';
            $messagehtml = "<p>Dear " . htmlspecialchars($purchaser->firstname) . ",</p>";
            $messagehtml .= "<p>Thank you for your recent purchase from the Physiosports Education.</p>";
            $messagehtml .= "<p>You have successfully purchased the following:</p>";
            $messagehtml .= "<ul>";
            foreach ($generatedcouponsbycourse as $coursename => $details) {
                $messagehtml .= "<li>" . htmlspecialchars($coursename) . " - Quantity: " . $details['quantity'] . " - Coupons:<ul>";
                foreach ($details['codes'] as $code) {
                    $messagehtml .= "<li>" . htmlspecialchars($code) . "</li>";
                }
                $messagehtml .= "</ul></li>";
            }
            $messagehtml .= "</ul>";
            $messagehtml .= "<p>If you have purchased a Coupon, please follow the below instructions.</p>";
            $messagehtml .= "<ul>";
            $messagehtml .= "<li>Log in to the Physiosports Education.</li>";
            $messagehtml .= "<li>Click on the COUPON tab on the top right corner of the screen.</li>";
            $messagehtml .= "<li>Enter your unique coupon code given to you in the box provided.</li>";
            $messagehtml .= "<li>Press the 'Redeem' button.</li>";
            $messagehtml .= "</ul>";
            $messagehtml .= "<p>You will then have access to all the course materials for the corresponding course.</p>";
            $messagehtml .= "<p>Please email courses@physiosports.com.au if you have any queries.</p>";
            $messagehtml .= "<p>We hope you enjoy your online learning experience with us.<br>The Physiosports Brighton Courses Team</p>";
            
            try {
                $mail = get_mailer();
                $mail->addAddress($purchaser->email, fullname($purchaser));
                $mail->From = $supportuser->email;
                $mail->FromName = fullname($supportuser);
                $mail->Subject = $subject;
                $mail->isHTML(true);
                $mail->Body = $messagehtml;
                $mail->send();
            } catch (\Exception $e) {
                error_log("local_cart: Could not send coupon email. Error: " . $e->getMessage());
            }
        }

        $DB->delete_records('local_cart_items', ['userid' => $userid]);
        unset($SESSION->cart_discount);
        
        return true;
    }
}