<?php
// This file is part of Moodle - http://moodle.org/
/*
  (GPL header...)
*/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

class auth_plugin_elcentra extends auth_plugin_base {

    public function __construct() {
        $this->authtype = 'elcentra';
        $this->config   = get_config('auth_elcentra');
    }

    public function user_login($username, $password) {
        global $CFG, $DB, $SESSION;

        $user = $DB->get_record('user', [
            'username'   => $username,
            'mnethostid' => $CFG->mnet_localhost_id
        ]);

        if (!$user || $user->auth !== 'elcentra') {
            return false;
        }

        if (isset($SESSION->elcentra_login_username) &&
            $SESSION->elcentra_login_username === $username) {
            unset($SESSION->elcentra_login_username);
            return true;
        }

        unset($SESSION->elcentra_login_username);
        return false;
    }

    public function prevent_local_passwords(): bool {
        return false;
    }

    public function is_internal(): bool {
        return false;
    }

    public function can_change_password(): bool {
        return false;
    }

    public function change_password_url(): ?moodle_url {
        return null;
    }

    public function can_reset_password(): bool {
        return false;
    }

    public function loginpage_hook(): void {
    global $PAGE, $CFG;

    $mode = get_config('auth_elcentra', 'add_code_mode') ?: 'auto';
    if ($mode === 'auto') {
        $PAGE->requires->jquery();
        $PAGE->requires->js_init_code("buttonsAddMethod = 'auto';");
        $buttons = str_replace(["\n","\r"], ['\\n','\\r'], self::get_buttons_string());
        $PAGE->requires->js_init_code("buttonsCode = '{$buttons}';");
        $PAGE->requires->js(new moodle_url("{$CFG->wwwroot}/auth/elcentra/script.js"));
    }
}


    public function elcentraProcessResponse(array $details): never {
        global $DB, $CFG, $USER, $SESSION;

        list($username, $email, $firstName, $lastName, $country, $city, $timezone, $verified) = $details;

        if (!$verified) {
            throw new moodle_exception('emailaddressmustbeverified', 'auth_elcentra');
        }

        if ($err = email_is_not_allowed($email)) {
            throw new moodle_exception($err);
        }

        $user = $DB->get_record('user', [
            'username'   => $username,
            'mnethostid' => $CFG->mnet_localhost_id
        ]);

        if (!$user) {
            $user = (object) [
                'username'  => $username,
                'email'     => $email,
                'firstname' => $firstName,
                'lastname'  => $lastName,
                'country'   => $country,
                'city'      => $city,
            ];
            if (!empty($timezone)) {
                $user->timezone = $timezone;
            }
            $newuser = create_user_record($username, '', 'elcentra');
            $user->id = $newuser->id;
            $DB->update_record('user', $user, false);
        }

        $SESSION->elcentra_login_username = $username;
        $loggedinuser = authenticate_user_login($username, '');
        if (!$loggedinuser) {
            throw new moodle_exception('auth_internalusernotfound', 'auth_elcentra');
        }

        complete_user_login($loggedinuser);

        if (user_not_fully_set_up($USER)) {
            $destination = new moodle_url('/user/edit.php');
        } else if (isset($SESSION->wantsurl) &&
                   strpos($SESSION->wantsurl, $CFG->wwwroot) === 0) {
            $destination = new moodle_url($SESSION->wantsurl);
            unset($SESSION->wantsurl);
        } else {
            $destination = new moodle_url('/');
            unset($SESSION->wantsurl);
        }

        redirect($destination);
    }

    private static function get_buttons_string(): string {
        global $CFG;
        return <<<HTML
<div class="moreproviderlink">
    <a href="{$CFG->wwwroot}/auth/elcentra/google_request.php"><img src="{$CFG->wwwroot}/auth/elcentra/img/google.png"></a><br>
    <a href="{$CFG->wwwroot}/auth/elcentra/facebook_request.php"><img src="{$CFG->wwwroot}/auth/elcentra/img/facebook.png"></a><br>
    <a href="{$CFG->wwwroot}/auth/elcentra/twitter_request.php"><img src="{$CFG->wwwroot}/auth/elcentra/img/twitter.png"></a><br>
    <a href="{$CFG->wwwroot}/auth/elcentra/linkedin_request.php"><img src="{$CFG->wwwroot}/auth/elcentra/img/linkedin.png"></a>
</div>
HTML;
    }
}
