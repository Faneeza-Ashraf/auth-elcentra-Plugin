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

define('NO_MOODLE_COOKIES', true);
require('../../config.php');
require_once($CFG->dirroot . '/payment/gateway/stripe/.extlib/init.php');

\Stripe\Stripe::setApiKey('sk_live_YOUR_REAL_STRIPE_SECRET_KEY'); 

$endpoint_secret = 'whsec_YOUR_WEBHOOK_SIGNING_SECRET'; 

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch(\Exception $e) {
    http_response_code(400); 
    exit();
}

switch ($event->type) {
       case 'checkout.session.completed':
        $session = $event->data->object;
        $metadata = $session->metadata;
        $stripe_subscription_id = $session->subscription;
        $userid = $metadata->moodle_userid;
        $programid = $metadata->moodle_programid;

        $program = $DB->get_record('enrol_programs_programs', ['id' => $programid]);
        if ($program && !$DB->record_exists('enrol_programs_allocations', ['programid' => $program->id, 'userid' => $userid])) {
            $sourcetype = \enrol_programs\local\source\manual::get_type();
            $source = $DB->get_record('enrol_programs_sources', ['programid' => $program->id, 'type' => $sourcetype]);
            if (!$source) {
                $source = new \stdClass();
                $source->programid = $program->id;
                $source->type = $sourcetype;
                $source->datajson = '{}';
                $source->id = $DB->insert_record('enrol_programs_sources', $source);
            }
            $newallocation = new \stdClass();
            $newallocation->programid       = $program->id;
            $newallocation->userid          = $userid;
            $newallocation->sourceid        = $source->id;
            $newallocation->archived        = 0;
            $newallocation->timeallocated   = time();
            $newallocation->timestart       = \enrol_programs\local\allocation::get_default_timestart($program, $newallocation->timeallocated);
            $newallocation->timedue         = \enrol_programs\local\allocation::get_default_timedue($program, $newallocation->timeallocated, $newallocation->timestart);
            $newallocation->timeend         = time() + (30 * 24 * 60 * 60);
            $newallocation->timecreated     = $newallocation->timeallocated;
            $newallocation->id = $DB->insert_record('enrol_programs_allocations', $newallocation);
            \enrol_programs\local\allocation::fix_user_enrolments($program->id, $userid);
            \enrol_programs\local\allocation::make_snapshot($newallocation->id, 'payment_success');
        }
        $subscription = new stdClass();
        $subscription->userid = $userid;
        $subscription->programid = $programid;
        $subscription->stripe_subscription_id = $stripe_subscription_id;
        $subscription->status = 'active';
        $subscription->timecreated = time();
        $subscription->timestart = time();
        $subscription->timeend = time() + (30 * 24 * 60 * 60); 
        $DB->insert_record('auth_emailadmin_subscriptions', $subscription);
        break;

    case 'invoice.payment_succeeded':
        $invoice = $event->data->object;
        $stripe_subscription_id = $invoice->subscription;

        if ($sub = $DB->get_record('auth_emailadmin_subscriptions', ['stripe_subscription_id' => $stripe_subscription_id])) {
            $new_end_date = $sub->timeend + (30 * 24 * 60 * 60);
            $DB->set_field('auth_emailadmin_subscriptions', 'timeend', $new_end_date, ['id' => $sub->id]);
        }
        break;

    case 'customer.subscription.deleted':
        $stripe_sub = $event->data->object;
        $DB->set_field('auth_emailadmin_subscriptions', 'status', 'cancelled', ['stripe_subscription_id' => $stripe_sub->id]);
        break;
}

http_response_code(200); 