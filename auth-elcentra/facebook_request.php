<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify it
// under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

require('../../config.php');
require_once(__DIR__ . '/eblib/facebook.php');
require_once(__DIR__ . '/eblib/misc.php');

// Ensure user isn't already logged in.
if (isloggedin() && !isguestuser()) {
    redirect(new moodle_url('/'));
}

$pluginconfig = get_config('auth_elcentra');

$appId     = trim($pluginconfig->facebookclientid ?? '');
$appSecret = trim($pluginconfig->facebookclientsecret ?? '');
$baseUrl   = $pluginconfig->facebook_base_url ?? $CFG->wwwroot . '/auth/elcentra/facebook_response.php';

if (empty($appId) || empty($appSecret)) {
    throw new moodle_exception('Facebook login is not configured. Contact the administrator.');
}

$facebookConfig = [
    'app_id'             => $appId,
    'app_secret'         => $appSecret,
    'base_url'           => $baseUrl,
    'token_access_url'   => $pluginconfig->facebook_token_access_url ?? '',
    'retrieval_url'      => $pluginconfig->facebook_retrieval_url ?? '',
    'scope'              => $pluginconfig->facebook_scope ?? 'email'
];

$facebook = new EbuildersFacebook();
$facebook->setConfig($facebookConfig);
$facebook->sendAccessRequest(); // This should redirect the user to Facebook for login
