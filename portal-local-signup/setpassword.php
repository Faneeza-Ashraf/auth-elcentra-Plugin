<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/user/lib.php');

$PAGE->set_url(new moodle_url('/local/signup/setpassword.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
$PAGE->set_title('Set Password');
$PAGE->set_heading('Confirm Your Account');

// Get raw data from either GET or POST (URL on first load, then form submission)
$data = optional_param('data', null, PARAM_RAW);

// If form submitted, get from POST (fallback)
if (!$data && optional_param('username', null, PARAM_RAW)) {
    $username = required_param('username', PARAM_RAW);
    $secret = required_param('secret', PARAM_RAW);
    $data = $username . '/' . $secret;
}

// Now validate the token
if (!$data || !strpos($data, '/')) {
    throw new moodle_exception('missingparam', 'error', '', 'data');
}

list($username, $secret) = explode('/', $data, 2);

// Validate user
if (!$user = $DB->get_record('user', ['username' => $username, 'secret' => $secret, 'confirmed' => 0])) {
    throw new moodle_exception('invalidconfirmdata', 'error');
}

// Password form definition
class set_password_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('header', 'companyheader', get_string('setpassword', 'local_signup'));
        $mform->addElement('passwordunmask', 'password', get_string('password'));
        $mform->addRule('password', get_string('required'), 'required');

        $mform->addElement('passwordunmask', 'confirmpassword', get_string('confirmpassword', 'local_signup'));
        $mform->addRule('confirmpassword', get_string('required'), 'required');

        $mform->addElement('hidden', 'username');
        $mform->setType('username', PARAM_RAW);

        $mform->addElement('hidden', 'secret');
        $mform->setType('secret', PARAM_RAW);

        $mform->addElement('hidden', 'data');
        $mform->setType('data', PARAM_RAW);

        $mform->addElement('submit', 'submitbutton', get_string('savechanges'));
    }

    // Custom validation to compare passwords
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['password'] !== $data['confirmpassword']) {
            $errors['confirmpassword'] = get_string('passwordsdonotmatch');
        }

        return $errors;
    }
}


// Create form and pre-fill hidden data
$form = new set_password_form(null, ['username' => $username, 'secret' => $secret, 'data' => $data]);
$form->set_data([
    'username' => $username,
    'secret' => $secret,
    'data' => $data
]);

echo $OUTPUT->header();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/login/index.php'));
} else if ($formdata = $form->get_data()) {
    // Recheck user before updating
    if (!$user = $DB->get_record('user', [
        'username' => $formdata->username,
        'secret' => $formdata->secret,
        'confirmed' => 0
    ])) {
        throw new moodle_exception('invalidconfirmdata', 'error');
    }

    update_internal_user_password($user, $formdata->password);
    $DB->set_field('user', 'confirmed', 1, ['id' => $user->id]);
    $DB->set_field('user', 'secret', '', ['id' => $user->id]);

    echo $OUTPUT->notification('Your password has been set. Now you can create your company.', 'notifysuccess');
    echo $OUTPUT->continue_button(new moodle_url('/local/signup/index.php', ['userid' => $user->id]));
    echo $OUTPUT->footer();
    exit;
}

$form->display();
echo $OUTPUT->footer();
