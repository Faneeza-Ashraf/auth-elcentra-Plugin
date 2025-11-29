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
$seckey    = required_param('seckey', PARAM_RAW);

require_login();

$studentcontext = \context_user::instance($userid);
if (($USER->id != $userid) && !has_capability('moodle/user:update', $studentcontext)) {
    throw new moodle_exception('nopermissions', 'error');
}

$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
$program = $DB->get_record('enrol_programs_programs', ['id' => $programid], '*', MUST_EXIST);

$expectedseckey = hash('sha256', $user->id . $program->id . $user->password);
if (!hash_equals($expectedseckey, $seckey)) {
    throw new moodle_exception('invalidaccess');
}

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
    $paymenturl = new moodle_url('/payment/gateway/stripe/pay.php', $params);
    redirect($paymenturl);
} else {
    redirect(new moodle_url('/my/'), 'No payment is currently due for this program.', \core\output\notification::NOTIFY_SUCCESS);
}