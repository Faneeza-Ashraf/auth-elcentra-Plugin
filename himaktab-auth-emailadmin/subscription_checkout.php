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

require('../../config.php');
require_once($CFG->dirroot . '/payment/gateway/stripe/.extlib/init.php');

$userid = required_param('userid', PARAM_INT);
$programid = required_param('programid', PARAM_INT);

require_login($userid);

$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
$program = $DB->get_record('enrol_programs_programs', ['id' => $programid], '*', MUST_EXIST);
\Stripe\Stripe::setApiKey('sk_live_YOUR_REAL_STRIPE_SECRET_KEY');
$stripepriceid = 'price_YOUR_PRICE_ID';

header('Content-Type: application/json');

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'customer_email' => $user->email,
        'line_items' => [[
            'price' => $stripepriceid,
            'quantity' => 1,
        ]],
        'mode' => 'subscription',
        'success_url' => (new moodle_url('/enrol/programs/my/index.php'))->out(true),
       'cancel_url' => (new moodle_url('/auth/emailadmin/upload_documents.php', ['uploaddata' => $user->secret . '/' . $user->id]))->out(true),
        'metadata' => [
            'moodle_userid' => $user->id,
            'moodle_programid' => $program->id
        ]
    ]);
    redirect($checkout_session->url); 
} catch (\Stripe\Exception\ApiErrorException $e) {
    throw new moodle_exception('stripeerror', 'auth_emailadmin', '', null, 'Stripe API Error: ' . $e->getMessage());
}