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

namespace auth_emailadmin\payment;

use moodle_url;
use core_payment;

defined('MOODLE_INTERNAL') || die();

class service_provider implements \core_payment\local\callback\service_provider {

    public static function get_payable(string $paymentarea, int $itemid): \core_payment\local\entities\payable {
        global $DB;
        $programid = $itemid;
        $sql = "";
        $params = [];
        
        if ($paymentarea === 'subscription_renewal') {
            $sql = "SELECT cd.value as cost 
                    FROM {customfield_data} cd 
                    JOIN {customfield_field} cf ON cf.id = cd.fieldid 
                    WHERE cd.instanceid = :instanceid AND cf.shortname = :feetype";
            $params = ['instanceid' => $programid, 'feetype' => 'monthly_fee'];
        } else { 
            $sql = "SELECT SUM(cd.value) as cost 
                    FROM {customfield_data} cd 
                    JOIN {customfield_field} cf ON cf.id = cd.fieldid 
                    WHERE cd.instanceid = :instanceid AND (cf.shortname = :fee1 OR cf.shortname = :fee2)";
            $params = ['instanceid' => $programid, 'fee1' => 'admission_fee', 'fee2' => 'monthly_fee'];
        }

        $totalcost = (float)($DB->get_field_sql($sql, $params) ?? 0.0);
        return new \core_payment\local\entities\payable($totalcost, 'usd', 1);
    }

    /**
     * This function runs after a successful payment and now handles BOTH cases.
     */
    public static function deliver_order(string $paymentarea, int $itemid, int $paymentid, int $userid): bool {
        global $DB;
        $programid = $itemid;
        try {
            if ($paymentarea === 'subscription_renewal') {
                if ($sub = $DB->get_record('auth_emailadmin_subscriptions', ['userid' => $userid, 'programid' => $programid])) {
                    $new_end_date = $sub->timeend + (30 * 24 * 60 * 60);
                    $DB->set_field('auth_emailadmin_subscriptions', 'timeend', $new_end_date, ['id' => $sub->id]);
                    $DB->set_field('enrol_programs_allocations', 'timeend', $new_end_date, ['userid' => $userid, 'programid' => $programid]);
                }
            } else if ($paymentarea === 'program_enrolment') {
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
                    $newallocation->programid = $program->id;
                    $newallocation->userid = $userid;
                    $newallocation->sourceid = $source->id;
                    $newallocation->archived = 0;
                    $newallocation->timeallocated = time();
                    $newallocation->timestart = time();
                    $newallocation->timeend = time() + (30 * 24 * 60 * 60); 
                    $newallocation->timecreated = time();
                    $newallocation->id = $DB->insert_record('enrol_programs_allocations', $newallocation);
                    \enrol_programs\local\allocation::fix_user_enrolments($program->id, $userid);
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

  /**
     * Returns the URL the user is sent to after a successful payment.
     * This signature MUST match the parent interface exactly.
     */
    public static function get_success_url(string $paymentarea, int $itemid): \moodle_url {
        global $USER, $DB;

        if (empty($USER->id)) {
            return new \moodle_url('/my/');
        }

        $usersecret = $DB->get_field('user', 'secret', ['id' => $USER->id]);
        if (empty($usersecret)) {
             return new \moodle_url('/my/');
        }
        $uploaddata = $usersecret . '/' . urlencode($USER->username);

        return new \moodle_url('/auth/emailadmin/upload_documents.php', ['uploaddata' => $uploaddata]);
    }
}