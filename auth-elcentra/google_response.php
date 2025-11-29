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
 * Google OAuth2 response handler for auth_elcentra plugin.
 *
 * @package   auth_elcentra
 * @copyright 2013 onwards Elcentra
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once '../../lib/pluginlib.php';
require_once 'eblib/google.php';
require_once 'eblib/misc.php';

$error = optional_param('error', false, PARAM_RAW);

if ($error !== false) {
    if ($error === 'access_denied') {
        loginDenied('google');
    } else {
        throw new moodle_exception('Google returned an error: ' . $error);
    }
}

$code = required_param('code', PARAM_RAW);
$state = required_param('state', PARAM_RAW);

global $PAGE;

$PAGE->set_url('/auth/elcentra/google_response.php', ['code' => $code, 'state' => $state]);

$pluginManager = plugin_manager::instance();

if (!$pluginManager->get_plugin_info('auth_elcentra', true)->is_enabled()) {
    throw new moodle_exception('Enable elcentra plugin');
}

session_start(); // Ensure session is started if not already

if (isset($_SESSION['google_login']['state']) && ($_SESSION['google_login']['state'] === $state)) {
    $google = new EbuildersGoogle();

    $conf = get_config('auth/elcentra');

    $googleConfig = [
        'app_id'           => $conf->googleclientid,
        'app_secret'       => $conf->googleclientsecret,
        'base_url'         => $conf->google_base_url,
        'token_access_url' => $conf->google_token_access_url,
        'retrieval_url'    => $conf->google_retrieval_url,
        'scope'            => $conf->google_scope,
    ];

    if (empty($googleConfig['app_id']) || empty($googleConfig['app_secret'])) {
        throw new moodle_exception('Google login is not configured. Contact admin');
    }

    $google->setConfig($googleConfig);
    $googleReturn = $google->receiveResponse($code);

    $prefix = 'elcentra_google_';

    $accountDetails = [
        $prefix . $googleReturn->id,       // Username
        $googleReturn->email,              // Email
        $googleReturn->given_name,         // First name
        $googleReturn->family_name,        // Last name
        '',                               // Country (empty)
        '',                               // City (empty)
        '',                               // Timezone (empty)
        $googleReturn->verified_email      // Verified status
    ];

    require 'auth.php';
    $elcentraPlugin = new auth_plugin_elcentra();
    $elcentraPlugin->elcentraProcessResponse($accountDetails);

} else {
    throw new moodle_exception('Invalid state parameter. Possible CSRF attack.');
}
