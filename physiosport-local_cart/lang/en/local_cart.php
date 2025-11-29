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

$string['pluginname'] = 'Local Cart';
$string['cart'] = 'Shopping Cart';
$string['yourcart'] = 'Your Shopping Cart';
$string['cartempty'] = 'Empty Cart';
$string['proceedtocheckout'] = 'Proceed to Checkout';
$string['viewcourse'] = 'View Course';
$string['guestcartmessage'] = 'Please log in to view your cart items.';
$string['additem'] = 'Add item to cart';
$string['viewcart'] = 'View cart';
$string['alreadyaddedtocarderror'] = 'This course has already been added to the cart, please go to your shopping to make changes';
$string['error'] = 'Error';
$string['close'] = 'Close';
$string['ok'] = 'OK';
$string['unknownerror'] = 'An unknown error occurred.'; 
$string['nopermissionaddtocart'] = 'You do not have permission to add items to the cart.';
$string['courseaddedtocart'] = '{$a} successfully added to your cart!';
$string['failedtoaddtocart'] = 'Failed to add the course to your cart. Please try again.';
$string['invalidparams'] = 'Invalid parameters provided.';
$string['mycart'] = 'My Cart';
$string['myorders'] = 'MY ORDERS'; 
$string['myorderhistory'] = 'My Order History';
$string['noordersfound'] = 'You have not placed any orders yet.';
$string['continuetopayment'] = 'Continue to Payment'; 
$string['ordersummary'] = 'Order Summary'; 
$string['total'] = 'Total'; 
$string['incgst'] = 'inc GST'; 
$string['quantity'] = 'Quantity'; 
$string['remove'] = 'Remove'; 
$string['onlineondemand'] = 'ONLINE ON DEMAND';
$string['individual'] = 'Individual';
$string['coupon'] = 'Coupon';
$string['license'] = 'license';
$string['failedtoaddtocart'] = 'Failed to add item to cart. Please try again.'; 
$string['courseremovedfromcart'] = 'Course removed from cart.';
$string['checkout'] = 'Checkout';
$string['course'] = 'Course';
$string['price'] = 'Price';
$string['subtotal'] = 'Subtotal';
$string['gst'] = 'GST';
$string['backtocart'] = 'Back to Cart';
$string['proceedtopayment'] = 'Proceed to Payment';
$string['paymentmethod'] = 'Payment Method';
$string['paymentsuccessful'] = 'Payment process initiated successfully! You will be redirected.';
$string['paymentfailed'] = 'Payment process failed. Please try again.';
$string['guestcannotcheckout'] = 'Guest users cannot proceed to checkout.';
$string['cartemptycheckout'] = 'Your cart is empty. Please add items before checking out.';
$string['failedtocreateorder'] = 'Failed to create your order. Please try again or contact support.';
$string['cartpurchase'] = 'Cart Purchase (Order ID: {$a->orderid})'; 
$string['orderitemnotfound'] = 'Order item not found.';
$string['invalidpaymentcontext'] = 'Invalid payment context for local cart.';
$string['cannotprocessorderforotheruser'] = 'Cannot process order for another user.';
$string['orderprocessedsuccessfully'] = 'Your order #{$a->orderid} has been processed successfully.';
$string['redirectingtopayment'] = 'Redirecting to payment gateway...';
$string['internalerror'] = 'An internal error occurred. Please contact support.'; 
$string['paymentnotcleared'] = 'Payment not cleared. Please try again.'; 
$string['amountmismatch'] = 'Amount mismatch. Please contact support.'; 
$string['cannotfetchorderdatails'] = 'Cannot fetch order details. Please contact support.'; 
$string['failedtoupdatecartitem'] = 'Failed to update cart item. Please try again.';
$string['youhaveselectedcoupon'] = 'You have selected coupon for this course.';
$string['cartitemupdated'] = 'Cart item updated successfully.';
$string['redeemyourcoupon'] = 'Redeem Your Coupon';
$string['haveacoursecoupon'] = 'Have a course coupon? Enter the code here to start your learning.';
$string['couponcode'] = 'Coupon Code';
$string['redeem'] = 'Redeem';
$string['coupondescription'] = 'A coupon can be purchased when you are buying the course for staff members or a friend. They can be for an individual or a group booking. Once purchased, you simply email the course attendee their coupon and get them to register and create a profile. Once logged in, they simply click on the coupon button at the top right of the dashboard, enter their coupon code and boom, they can start their learning.';
$string['couponredeemedsuccessfully'] = 'Coupon redeemed successfully! You can now access your course.';
$string['invalidcouponcode'] = 'Invalid coupon code.';
$string['couponredemptionfailed'] = 'Coupon redemption failed. Please contact support.';
$string['couponalreadyredeemed'] = 'Coupon  has already been redeemed.';
$string['couponexpired'] = 'Coupon has expired.';
$string['couponnotactive'] = 'Coupon is not active.';
$string['useralreadyenrolled'] = 'You are already enrolled in the course associated with coupon "{$a}".';
$string['enrolmanualnotfound'] = 'Manual enrolment plugin not found or not enabled.';
$string['cart_purchase_description'] = 'Courses from shopping cart for user {$a}';
$string['continuetobrowse'] = 'MY ORDERS';
$string['cart'] = 'My Cart';
$string['cart_purchase_description'] = 'Courses from shopping cart for user {$a}';
$string['individual'] = 'Individual';
$string['license'] = 'License';
$string['quantity'] = 'Quantity';
$string['youhaveselectedcoupon'] = 'You have selected coupon';
$string['guestcartmessage'] = 'Guests cannot purchase courses. Please log in.';
$string['courseremovedfromcart'] = 'Course removed from cart';
$string['failedtoupdatecartitem'] = 'Failed to update cart item';
$string['onlineondemand'] = 'Online On Demand';
$string['nopaymentaccountconfigured'] = 'No default payment account is configured. Please contact the site administrator.';
$string['paymentaccount'] = 'Default Payment Account';
$string['paymentaccountdesc'] = 'Select the default payment account to be used for all transactions originating from the cart. This account must be configured under Site administration > Plugins > Payment gateways > Payment accounts.';
$string['nopaymentaccounts'] = 'No payment accounts available';
$string['nopaymentaccountsdesc'] = 'There are no payment accounts configured. Please create and enable at least one payment account under Site administration > Plugins > Payment gateways > Payment accounts.';
$string['paymentgatewayaccount'] = 'Payment Gateway Account';
$string['paymentgatewayaccount_desc'] = 'Select the payment gateway account that will be used for all shopping cart transactions. You must enable at least one payment gateway in Site administration > Plugins > Payment gateways.';
$string['nopaymentgateways'] = 'No enabled payment gateways found';
$string['discountcode'] = 'Discount Code';
$string['resellercode'] = 'Reseller Code';
$string['enterdiscountcode'] = 'Enter discount code';
$string['enterresellercode'] = 'Enter reseller code';
$string['apply'] = 'Apply';
$string['discount'] = 'Discount';
$string['remove'] = 'Remove';
$string['invalidorExpiredCode'] = 'The discount code is invalid or has expired.';
$string['invalidresellercode'] = 'The reseller code is invalid or inactive.';
$string['noassociateddiscount'] = 'No valid discount is associated with this reseller.';
$string['discountapplied'] = 'Discount applied successfully.';
$string['resellerdiscountapplied'] = 'Reseller discount applied successfully.';
$string['discountalreadyapplied'] = 'A discount has already been applied. Please remove it before applying a new one.';
$string['whatisacoupon_title'] = 'What is a coupon?';
$string['whatisacoupon_body'] = 'A coupon can be purchased when you are buying the course for staff members or a friend. They can be for an individual or a group booking. Once purchased, you simply email the course attendee their coupon and get them to register and create a profile. Once logged in, they simply click on the coupon button at the top right of the dashboard, enter their coupon code and boom, they can start their learning.';
$string['close'] = 'Close';
$string['ok'] = 'OK';
$string['completeorder'] = 'Complete Order';
$string['checkoutcomplete'] = 'Checkout successful! You have been enrolled in the course(s).';
$string['continuetoshopping'] = 'Close';
$string['success'] = 'Success';
$string['logintocheckout'] = 'CONTINUE TO PAYMENT';
$string['addtocart'] = 'ADD TO CART';
$string['cart:managecart'] = 'Manage cart items and checkout';
$string['cart:purchase_local_cart'] =  'Purchase courses via local cart';
$string['cart:additem'] = 'Add item to cart';
$string['cart:viewcart'] = 'View cart';
$string['congratulations'] = 'Congratulations!';
$string['youhavebeenenrolled'] = 'You have been enrolled into course:';
$string['opencourse'] = 'Open Course';
$string['back'] = 'Back';
// Added for receipt page by Endush Fairy
$string['receiptpagetitle'] = 'Order Receipt';
$string['receiptpageheading'] = 'ORDER COMPLETED';
$string['receiptpageparagraph'] = 'Thank you for your purchase.';
$string['coursetitle'] = 'Title/Description';
$string['paymenttype'] = 'Type';
$string['quantity'] = 'Qty';
$string['cost'] = 'Cost';
$string['subtotal_cap'] = 'SUBTOTAL';
$string['coupon_cap'] = 'COUPON';
$string['total_cap'] = 'TOTAL';
$string['billing'] = 'BILLED TO';
$string['backtohome'] = 'BACK TO HOME';
//Added By Muskan Arshad//
$string['messageprovider:couponnotification'] = 'Coupon notification';