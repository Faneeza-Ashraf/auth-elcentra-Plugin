<?php
require_once(__DIR__.'/../../config.php');
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/signup/company_create.php'));
$PAGE->set_title(get_string('createcompany', 'local_signup'));
$PAGE->set_heading(get_string('createcompany', 'local_signup'));

require_once($CFG->dirroot.'/local/signup/classes/form/company_form.php');

$mform = new \local_signup\form\company_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/my')); // Adjust as needed.
} else if ($data = $mform->get_data()) {
    // Save company record to DB.
    $company = new stdClass();
    $company->name = $data->name;
    $company->shortname = $data->shortname;
    $company->code = $data->code;
    $company->address = $data->address;
    $company->city = $data->city;
    $company->region = $data->region;
    $company->postcode = $data->postcode;
    $company->country = $data->country;
    $company->timecreated = time();
    $company->timemodified = time();

    $companyid = $DB->insert_record('local_signup_company', $company);

    // Send confirmation email.
    $user = $USER;
    $subject = get_string('companycreatedsubject', 'local_signup');
    $message = get_string('companycreatedbody', 'local_signup', $company->name);

    email_to_user($user, core_user::get_support_user(), $subject, $message);

    redirect(new moodle_url('/local/signup/company_list.php'), get_string('companycreatedsuccess', 'local_signup'));
}


$mform->display();
echo $OUTPUT->footer();
