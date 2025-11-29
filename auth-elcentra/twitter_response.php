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
 * Twitter OAuth response handler for auth_elcentra plugin.
 *
 * @package   auth_elcentra
 * @copyright 2013 onwards Elcentra
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once '../../lib/pluginlib.php';
require_once 'eblib/twitter.php';
require_once 'eblib/misc.php';

// Check if the user denied access
$denied = optional_param("denied", false, PARAM_RAW);
if ($denied !== false) {
    loginDenied("twitter");
}

// Get required OAuth parameters from Twitter callback
$oauthVerifier = required_param('oauth_verifier', PARAM_RAW);
$oauthToken = required_param("oauth_token", PARAM_RAW);

global $PAGE;

// Set page URL for Moodle
$PAGE->set_url("/auth/elcentra/twitter_response.php", array(
    "oauth_verifier" => $oauthVerifier,
    "oauth_token" => $oauthToken
));

$pluginManager = plugin_manager::instance();

// Check if plugin is enabled
if (!$pluginManager->get_plugin_info("auth_elcentra", true)->is_enabled()) {
    throw new moodle_exception("Enable elcentra plugin");
}

// Verify OAuth token matches session token to prevent CSRF
if (isset($_SESSION['twitter_login']['oauth_token']) && $_SESSION['twitter_login']['oauth_token'] === $oauthToken) {
    $twitter = new EbuildersTwitter();

    $conf = get_config("auth/elcentra");

    $twitterConfig = array(
        "consumer_key"       => $conf->twitterclientid,
        "consumer_secret"    => $conf->twitterclientsecret,
        "authorize_url"      => $conf->twitter_authorize_url,
        "token_access_url"   => $conf->twitter_token_access_url,
        "token_request_url"  => $conf->twitter_token_request_url,
    );

    if ($twitterConfig['consumer_key'] === "" || $twitterConfig['consumer_secret'] === "") {
        throw new moodle_exception("Twitter login is not configured. Contact admin");
    }

    $twitter->setConfig($twitterConfig);

    // Fetch the user details from Twitter using verifier and token
    $twitterReturn = $twitter->receiveResponse($oauthVerifier, $oauthToken);

    // Prefix to avoid username collisions
    $prefix = "elcentra_twitter_";

    // Calculate timezone offset in hours if available
    $timezone = "";
    if (!is_null($twitterReturn->utc_offset)) {
        $timezone = $twitterReturn->utc_offset / 3600;
    }

    // Prepare account details for Moodle authentication
    $accountDetails = array(
        $prefix . $twitterReturn->id, // Username
        "",                          // Email (Twitter API often doesn't provide this)
        "",                          // First Name (not provided here)
        $twitterReturn->name,        // Full Name as last name or display name
        "",                          // Country
        "",                          // City
        $timezone,                   // Timezone offset in hours
        true                        // Verified (assumed true here)
    );

    // Include your Moodle auth plugin and process the user login/registration
    require 'auth.php';
    $elcentraPlugin = new auth_plugin_elcentra();
    $elcentraPlugin->elcentraProcessResponse($accountDetails);
}
