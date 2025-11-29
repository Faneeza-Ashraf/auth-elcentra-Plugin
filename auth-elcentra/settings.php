<?php
// This file is part of Moodle.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // GOOGLE SETTINGS
    $settings->add(new admin_setting_heading('auth_elcentra_google_heading', get_string('googleclient_title', 'auth_elcentra'), ''));
    
    $settings->add(new admin_setting_configtext(
        'auth_elcentra/googleclientid',
        get_string('googleclientid_text', 'auth_elcentra'),
        get_string('googleclientid_description', 'auth_elcentra'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'auth_elcentra/googleclientsecret',
        get_string('googleclientsecret_text', 'auth_elcentra'),
        get_string('googleclientsecret_description', 'auth_elcentra'),
        ''
    ));

    // FACEBOOK SETTINGS
    $settings->add(new admin_setting_heading('auth_elcentra_facebook_heading', get_string('facebookclient_title', 'auth_elcentra'), ''));

    $settings->add(new admin_setting_configtext(
        'auth_elcentra/facebookclientid',
        get_string('facebookclientid_text', 'auth_elcentra'),
        get_string('facebookclientid_description', 'auth_elcentra'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'auth_elcentra/facebookclientsecret',
        get_string('facebookclientsecret_text', 'auth_elcentra'),
        get_string('facebookclientsecret_description', 'auth_elcentra'),
        ''
    ));

    // LINKEDIN SETTINGS
    $settings->add(new admin_setting_heading('auth_elcentra_linkedin_heading', get_string('linkedinclient_title', 'auth_elcentra'), ''));

    $settings->add(new admin_setting_configtext(
        'auth_elcentra/linkedinclientid',
        get_string('linkedinclientid_text', 'auth_elcentra'),
        get_string('linkedinclientid_description', 'auth_elcentra'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'auth_elcentra/linkedinclientsecret',
        get_string('linkedinclientsecret_text', 'auth_elcentra'),
        get_string('linkedinclientsecret_description', 'auth_elcentra'),
        ''
    ));

    // TWITTER SETTINGS
    $settings->add(new admin_setting_heading('auth_elcentra_twitter_heading', get_string('twitterclient_title', 'auth_elcentra'), ''));

    $settings->add(new admin_setting_configtext(
        'auth_elcentra/twitterclientid',
        get_string('twitterclientid_text', 'auth_elcentra'),
        get_string('twitterclientid_description', 'auth_elcentra'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'auth_elcentra/twitterclientsecret',
        get_string('twitterclientsecret_text', 'auth_elcentra'),
        get_string('twitterclientsecret_description', 'auth_elcentra'),
        ''
    ));

    // Add Code Mode (Radio button)
    $settings->add(new admin_setting_configselect(
        'auth_elcentra/add_code_mode',
        get_string('add_code_mode_text', 'auth_elcentra'),
        get_string('add_code_mode_description', 'auth_elcentra'),
        'auto',
        [
            'auto' => get_string('add_code_mode_auto_text', 'auth_elcentra'),
            'manual' => get_string('add_code_mode_manual_text', 'auth_elcentra')
        ]
    ));
}
