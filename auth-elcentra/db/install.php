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
 * Install script for the 'auth_elcentra' plugin.
 *
 * @package   auth_elcentra
 * @copyright 2013 onwards Elcentra
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_auth_elcentra_install(): void {
    // Facebook settings.
    set_config('facebook_base_url', 'https://www.facebook.com/dialog/oauth?', 'auth_elcentra');
    set_config('facebook_token_access_url', 'https://graph.facebook.com/oauth/access_token?', 'auth_elcentra');
    set_config('facebook_retrieval_url', 'https://graph.facebook.com/me?', 'auth_elcentra');
    set_config('facebook_scope', 'email', 'auth_elcentra');
    set_config('facebookclientid', '', 'auth_elcentra');
    set_config('facebookclientsecret', '', 'auth_elcentra');

    // Twitter settings.
    set_config('twitter_authorize_url', 'https://api.twitter.com/oauth/authenticate', 'auth_elcentra');
    set_config('twitter_token_access_url', 'https://api.twitter.com/oauth/access_token', 'auth_elcentra');
    set_config('twitter_token_request_url', 'https://api.twitter.com/oauth/request_token', 'auth_elcentra');
    set_config('twitterclientid', '', 'auth_elcentra');
    set_config('twitterclientsecret', '', 'auth_elcentra');

    // LinkedIn settings.
    set_config('linkedin_base_url', 'https://www.linkedin.com/uas/oauth2/authorization?', 'auth_elcentra');
    set_config('linkedin_token_access_url', 'https://www.linkedin.com/uas/oauth2/accessToken?', 'auth_elcentra');
    set_config('linkedin_retrieval_url', 'https://api.linkedin.com/v1/people/~:(id,first-name,last-name,location:(country:(code)),email-address)?', 'auth_elcentra');
    set_config('linkedin_scope', 'r_basicprofile%20r_emailaddress', 'auth_elcentra');
    set_config('linkedinclientid', '', 'auth_elcentra');
    set_config('linkedinclientsecret', '', 'auth_elcentra');

    // Google settings.
    set_config('google_base_url', 'https://accounts.google.com/o/oauth2/auth?', 'auth_elcentra');
    set_config('google_token_access_url', 'https://accounts.google.com/o/oauth2/token', 'auth_elcentra');
    set_config('google_retrieval_url', 'https://www.googleapis.com/oauth2/v1/userinfo?', 'auth_elcentra');
    set_config('google_scope', 'https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile', 'auth_elcentra');
    set_config('googleclientid', '', 'auth_elcentra');
    set_config('googleclientsecret', '', 'auth_elcentra');
}
