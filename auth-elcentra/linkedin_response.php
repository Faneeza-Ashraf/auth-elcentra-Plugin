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
 * Handles LinkedIn OAuth response for auth_elcentra plugin.
 *
 * @package   auth_elcentra
 * @copyright 2013 onwards Elcentra
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once '../../lib/pluginlib.php';
require_once 'eblib/linkedin.php';
require_once 'eblib/misc.php';

// Check if LinkedIn returned an error
$error = optional_param("error", false, PARAM_RAW);
if ($error !== false) {
    if ($error === "access_denied") {
        loginDenied("linkedin");
    } else {
        $errorDesc = required_param("error_description", PARAM_RAW);
        throw new moodle_exception("$errorDesc - ($error)");
    }
}

// Retrieve authorization code and state from LinkedIn callback
$code = required_param('code', PARAM_RAW);
$state = required_param('state', PARAM_RAW);

global $PAGE;
$PAGE->set_url("/auth/elcentra/linkedin_response.php", array("code" => $code, "state" => $state));

// Verify plugin is enabled
$pluginManager = plugin_manager::instance();
if (!$pluginManager->get_plugin_info("auth_elcentra", true)->is_enabled()) {
    throw new moodle_exception("Enable elcentra plugin");
}

// Validate OAuth state to prevent CSRF
if (isset($_SESSION['linkedin_login']['state']) && ($_SESSION['linkedin_login']['state'] === $state)) {
    $linkedin = new EbuildersLinkedin();

    // Load LinkedIn OAuth config
    $conf = get_config("auth/elcentra");
    $linkedinConfig = array(
        "app_id"           => $conf->linkedinclientid,
        "app_secret"       => $conf->linkedinclientsecret,
        "base_url"         => $conf->linkedin_base_url,
        "token_access_url" => $conf->linkedin_token_access_url,
        "retrieval_url"    => $conf->linkedin_retrieval_url,
        "scope"            => $conf->linkedin_scope
    );

    if ($linkedinConfig['app_id'] === "" || $linkedinConfig['app_secret'] === "") {
        throw new moodle_exception("Linkedin login is not configured. Contact admin");
    }

    $linkedin->setConfig($linkedinConfig);

    // Exchange code for access token and fetch user profile
    $linkedinReturn = $linkedin->receiveResponse($code);

    $prefix = "elcentra_linked_";

    // Map LinkedIn user data to your account details array
    $accountDetails = array(
        $prefix . $linkedinReturn->id,                         // Username
        "" . $linkedinReturn->{"email-address"},              // Email
        "" . $linkedinReturn->{"first-name"},                  // First name
        "" . $linkedinReturn->{"last-name"},                   // Last name
        strtoupper("" . $linkedinReturn->location->country->code), // Country (ISO code uppercase)
        "",                                                    // City (empty)
        "",                                                    // Timezone (empty)
        true                                                   // Verified (assumed true)
    );

    // Pass user info to your auth plugin for processing
    require 'auth.php';
    $elcentraPlugin = new auth_plugin_elcentra();
    $elcentraPlugin->elcentraProcessResponse($accountDetails);
}
