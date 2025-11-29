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
 * Version file for the local_signup plugin.
 *
 * @package   local_signup
 * @copyright 2021 Derick Turner
 * @author    Derick Turner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_signup';         // Full name of the plugin (used for diagnostics).
$plugin->version   = 2025021000;             // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2024100700;             // Requires this Moodle version.
$plugin->release   = '4.5.4 (Build: 20250414)'; // Human-readable release info.
$plugin->maturity  = MATURITY_STABLE;        // This is a stable release.
