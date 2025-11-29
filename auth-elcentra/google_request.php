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
 * Google OAuth2 login request handler for auth_elcentra plugin.
 *
 * @package   auth_elcentra
 * @copyright 2013 onwards Elcentra
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(__DIR__ . '/eblib/google.php');
require_once(__DIR__ . '/eblib/misc.php');

// Load plugin configuration.
$conf = get_config('auth/elcentra');

$googleConfig = [
    'app_id'           => $conf->googleclientid,
    'app_secret'       => $conf->googleclientsecret,
    'base_url'         => $conf->google_base_url,
    'token_access_url' => $conf->google_token_access_url,
    'retrieval_url'    => $conf->google_retrieval_url,
    'scope'            => $conf->google_scope,
];

// Check if the Google client ID and secret are configured.
if (empty($googleConfig['app_id']) || empty($googleConfig['app_secret'])) {
    throw new moodle_exception('Google login is not configured. Contact admin');
}

// Instantiate your Google OAuth helper class and start the OAuth flow.
$googleObject = new EbuildersGoogle();
$googleObject->setConfig($googleConfig);
$googleObject->sendAccessRequest();
