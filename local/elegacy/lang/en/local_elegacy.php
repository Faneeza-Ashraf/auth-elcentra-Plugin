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
 * Legacy plugin language strings
 *
 * @package    local_elegacy
 * @copyright  2021-2023 Syed Zonair, Syed {@link http://paktaleem.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['pluginname']           = 'ELIS Legacy Plugin';

// REPORT.
$string['report_single_title']  = 'Historical Certificate Report';
$string['enrolmentstart']       = 'Course completion date';
$string['certificatedownload']  = 'Certificate';
$string['norecord']             = 'There are no legacy records found for this user.';

// SEARCH BAR.
$string['searchplaceholder']    = 'Search for a user';
$string['searchbtn']            = 'Search';

// SYNC.
$string['syncscript']           = 'Course Recompletion Synchronization Script';
$string['sync_message_0']       = 'Script is completed successfully.';
$string['sync_message_1']       = 'Course ID is invalid.';
$string['sync_message_2']       = 'Course completion setting of the course is not set.';
