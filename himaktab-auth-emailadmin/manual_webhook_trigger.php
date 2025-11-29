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

$userid    = required_param('userid', PARAM_INT);
$programid = required_param('programid', PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/auth/emailadmin/manual_webhook_trigger.php', ['userid'=>$userid, 'programid'=>$programid]));
$PAGE->set_title("Manual Subscription Test");
$PAGE->set_heading("Subscription Test Trigger");
echo $OUTPUT->header();

try {
    $program = $DB->get_record('enrol_programs_programs', ['id' => $programid], '*', MUST_EXIST);
    if (!$DB->record_exists('enrol_programs_allocations', ['programid' => $program->id, 'userid' => $userid])) {
    }

    $subscription = new stdClass();
    $subscription->userid = $userid;
    $subscription->programid = $programid;
    $subscription->stripe_subscription_id = 'manual_test_' . time();
    $subscription->status = 'active';
    $subscription->timecreated = time();
    $subscription->timestart = time();
    $subscription->timeend = time() + (8 * 60);

    if ($existing = $DB->get_record('auth_emailadmin_subscriptions', ['userid' => $userid, 'programid' => $programid])) {
        $subscription->id = $existing->id;
        $DB->update_record('auth_emailadmin_subscriptions', $subscription);
    } else {
        $DB->insert_record('auth_emailadmin_subscriptions', $subscription);
    }
    
    if ($allocation = $DB->get_record('enrol_programs_allocations', ['userid' => $userid, 'programid' => $programid])) {
        $DB->set_field('enrol_programs_allocations', 'timeend', $subscription->timeend, ['id' => $allocation->id]);
    }

    echo $OUTPUT->notification('SUCCESS: User ID ' . $userid . ' has been enrolled and their subscription has been set to expire in 8 minutes.', 'notifysuccess');
    $endtime_string = userdate($subscription->timeend);
    echo $OUTPUT->notification("Subscription end time has been set to: <strong>" . $endtime_string . "</strong>", 'notifysuccess');

} catch (Exception $e) {
    echo $OUTPUT->notification('ERROR: ' . $e->getMessage());
}
echo $OUTPUT->footer();