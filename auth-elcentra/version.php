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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin version information.
 *
 * @package    auth_elcentra
 * @copyright  2013 onwards Elcentra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2025062700;        // Plugin version (Date: YYYYMMDDXX) - today’s date with revision 00.
$plugin->requires  = 2024041200;        // Requires Moodle 4.5 release version.
$plugin->component = 'auth_elcentra';   // Full name of the plugin (used for diagnostics).
$plugin->maturity  = MATURITY_BETA;     // This version's maturity level.
$plugin->release   = '1.04';             // Human-readable version name.
$plugin->cron      = 0;                  // No cron task required.
$plugin->dependencies = [];              // No plugin dependencies.
