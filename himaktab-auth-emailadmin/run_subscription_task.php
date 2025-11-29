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

define('MY_TASK_SECRET_KEY', 'a_very_secret_key_12345');

$key = required_param('key', PARAM_RAW);
if ($key !== MY_TASK_SECRET_KEY) {
    throw new moodle_exception('invalidaccess');
}

require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/auth/emailadmin/run_subscription_task.php', ['key' => $key]));
$PAGE->set_title("Manual Task Runner");
$PAGE->set_heading("Running 'Check subscriptions' Task");

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox boxaligncenter');

try {
    echo "<h3>Executing Task: \\auth_emailadmin\\task\\check_subscriptions</h3>";
    echo "<hr><pre>"; 
    $tasktorun = new \auth_emailadmin\task\check_subscriptions();

    $tasktorun->execute();

    echo "</pre><hr>";
    echo "<h3>Task execution finished.</h3>";

} catch (Exception $e) {
    echo "AN ERROR OCCURRED: " . $e->getMessage();
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();