<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/authlib.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/signup/confirm.php'));
$PAGE->set_title('Email Confirmation');
$PAGE->set_heading('Email Confirmation');

$data = optional_param('data', '', PARAM_RAW);
list($username, $secret) = explode('/', $data);

// Validate user and secret
if (!$user = $DB->get_record('user', ['username' => $username, 'secret' => $secret, 'confirmed' => 0])) {
    throw new moodle_exception('invalidconfirmdata', 'error');
}

// Mark user as confirmed
$DB->set_field('user', 'confirmed', 1, ['id' => $user->id]);

// Redirect to company form with userid
redirect(new moodle_url('/local/signup/index.php', ['userid' => $user->id]));
