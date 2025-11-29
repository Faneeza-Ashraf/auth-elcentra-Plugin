<?php
// FINAL TEST VERSION
namespace auth_emailadmin\task;

class check_subscriptions extends \core\task\scheduled_task {

    public function get_name() {
        return \get_string('task_check_subscriptions', 'auth_emailadmin');
    }

         public function execute() {
        global $DB;
        $now = time();
        \mtrace("--- Starting Subscription Check at: " . \userdate($now) . " ---");

        $subscriptions = $DB->get_records('auth_emailadmin_subscriptions', ['status' => 'active']);
        if (empty($subscriptions)) {
            \mtrace("Result: No active subscriptions found."); return;
        }

        \mtrace("Found " . count($subscriptions) . " active subscription(s) to check.");

        foreach ($subscriptions as $sub) {
            $user = $DB->get_record('user', ['id' => $sub->userid]);
            $program = $DB->get_record('enrol_programs_programs', ['id' => $sub->programid]);
            $time_remaining = $sub->timeend - $now;
            $reminder_window = 7 * 24 * 60 * 60; 
            if ($time_remaining < 0) {
                \mtrace("Action: Subscription for " . $user->email . " has expired.");
                if ($allocation = $DB->get_record('enrol_programs_allocations', ['userid' => $user->id, 'programid' => $program->id])) {
                    $DB->delete_records('event', ['instance' => $allocation->id]);
                    $DB->delete_records('enrol_programs_allocations', ['id' => $allocation->id]);
                    \enrol_programs\local\allocation::fix_user_enrolments($program->id, $user->id);
                    \mtrace("SUCCESS: User " . $user->email . " has been unenrolled from program: " . $program->fullname);
                }
                $DB->set_field('auth_emailadmin_subscriptions', 'status', 'expired', ['id' => $sub->id]);
                continue; 
            }

            if ($time_remaining <= $reminder_window) {
                \mtrace("Action: Subscription for " . $user->email . " is within the reminder window.");
                                $sql = "SELECT cd.value as cost 
                        FROM {customfield_data} cd 
                        JOIN {customfield_field} cf ON cf.id = cd.fieldid 
                        WHERE cd.instanceid = :instanceid AND cf.shortname = :feetype";
                $params = ['instanceid' => $program->id, 'feetype' => 'monthly_fee'];
                $monthlyfee = (float)($DB->get_field_sql($sql, $params) ?? 0.0);

                if ($monthlyfee > 0) {
                    $params = [
                        'component'   => 'auth_emailadmin',
                        'paymentarea' => 'subscription_renewal',
                        'itemid'      => $program->id,
                        'description' => 'Monthly Subscription Fee for ' . $program->fullname,
                        'amount'      => $monthlyfee
                    ];
                    $payment_url = new \moodle_url('/payment/gateway/stripe/pay.php', $params);
                    $data = new \stdClass();
                    $data->firstname = $user->firstname;
                    $data->programname = $program->fullname;
                    $data->sitename = \get_site()->fullname;
                    $data->paymentlink = $payment_url->out(false);
                    $subject = \get_string('email_reminder_subject', 'auth_emailadmin');
                    $body = \get_string('email_reminder_body', 'auth_emailadmin', $data);
                    
                    if (\email_to_user($user, \core_user::get_support_user(), $subject, $body)) {
                        \mtrace("SUCCESS: Sent subscription reminder email to: " . $user->email);
                    } else {
                        \mtrace("CRITICAL ERROR: The email_to_user() function failed.");
                    }
                }
            }
        }
        \mtrace("--- Subscription Check Finished ---");
    }
}
