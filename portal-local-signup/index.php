<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once(__DIR__ . '/signup_form.php');
require_once(__DIR__ . '/classes/form/company_form.php');
require_once(__DIR__ . '/classes/form/course_edit_form.php');
require_once($CFG->dirroot . '/blocks/iomad_company_admin/lib.php');
require_once($CFG->dirroot . '/local/iomad/lib/company.php');

$PAGE->set_url(new moodle_url('/local/signup/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('signup', 'local_signup'));
$PAGE->set_heading(get_string('createaccount', 'local_signup'));

$signupform = new signup_form();
$companyform = new \local_signup\form\company_form();

echo $OUTPUT->header();

if ($signupform->is_cancelled() || $companyform->is_cancelled()) {
    redirect(new moodle_url('/login/index.php'));
} else if ($data = $signupform->get_data()) {

    if ($DB->record_exists('user', ['email' => $data->email])) {
        echo $OUTPUT->notification(get_string('emailexists', 'local_signup'), 'notifyproblem');
    } else {
        $user = new stdClass();
        $user->auth = 'manual';
        $user->confirmed = 0; // Email confirmation required
        $user->username = strtolower($data->email);  // Email as username
        $user->email = $data->email;
        $user->firstname = $data->firstname;
        $user->lastname = $data->lastname;
        $user->lang = current_language();
        $user->timecreated = time();
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->firstnamephonetic = '';
        $user->lastnamephonetic = '';
        $user->middlename = '';
        $user->alternatename = '';
        $user->secret = random_string(15); // Unique token for confirmation

        // Create user (without password)
        $user->id = user_create_user($user, false, false);

        // Save secret to DB
        $DB->set_field('user', 'secret', $user->secret, ['id' => $user->id]);

        // Send confirmation email
        $site = get_site();
        $supportuser = core_user::get_support_user();

        $dataobj = new stdClass();
        $dataobj->firstname = $user->firstname;
        $dataobj->sitename = format_string($site->fullname);
        $dataobj->link = $CFG->wwwroot . '/local/signup/setpassword.php?data=' . urlencode($user->username) . '/' . $user->secret;
        $dataobj->admin = generate_email_signoff();

        $subject = get_string('emailconfirmationsubject', '', format_string($site->fullname));
        $message = get_string('emailconfirmation', '', $dataobj);
        $messagehtml = text_to_html($message, false, false, true);

        email_to_user($user, $supportuser, $subject, $message, $messagehtml);

        echo $OUTPUT->notification(get_string('emailconfirmationsent', 'local_signup'), 'notifysuccess');
        echo $OUTPUT->footer();
        exit;
    }

} else if ($companydata = $companyform->get_data()) {

    $existing = $DB->get_records_sql(
        "SELECT * FROM {company} WHERE LOWER(name) = LOWER(?)",
        [$companydata->name]
    );
    if (!empty($existing)) {
        echo $OUTPUT->notification(get_string('companyexists', 'local_signup'), 'notifyproblem');
        $companyform->set_data($companydata);
        $companyform->display();
        echo $OUTPUT->footer();
        exit;
    }

    // Create course category for the company
    $category = new stdClass();
    $category->name = $companydata->name;
    $category->parent = 0;
    $category->visible = 1;
    $category->id = $DB->insert_record('course_categories', $category);

    // Create the company
    $company = new stdClass();
    $company->name = $companydata->name;
    $company->shortname = $companydata->shortname;
    $company->city = $companydata->city;
    $company->country = $companydata->country;
    $company->timecreated = time();
    $company->createdby = $companydata->userid;
    $company->category = $category->id;

    $companyid = $DB->insert_record('company', $company);

    // Assign company manager role
    $companycontext = \core\context\company::instance($companyid);
    $role = $DB->get_record('role', ['shortname' => 'companymanager'], '*', MUST_EXIST);
    role_assign($role->id, $companydata->userid, $companycontext->id);

    // Assign additional capabilities
    $capabilities = [
        'moodle/course:view',
        'moodle/course:update',
        'block/iomad_company_admin:restrict_capabilities',
        'moodle/role:assign',
        'moodle/user:create',
        'moodle/user:update',
        'moodle/user:delete',
        'moodle/user:viewdetails',
        'moodle/category:manage'
    ];
    foreach ($capabilities as $capability) {
        assign_capability($capability, CAP_ALLOW, $role->id, $companycontext->id);
    }

    // Add user to the company
    $departmentid = null;
    $parentdepartment = company::get_company_parentnode($companyid);
    if ($parentdepartment && !empty($parentdepartment->id)) {
        $departmentid = $parentdepartment->id;
    } else {
        $topdept = $DB->get_record('company_department', ['companyid' => $companyid, 'parent' => 0]);
        if ($topdept) {
            $departmentid = $topdept->id;
        } else {
            throw new moodle_exception('cannotfinddepartment', 'local_signup', '', 'No valid department found for company ID ' . $companyid);
        }
    }

    $companyuser = new stdClass();
    $companyuser->userid = $companydata->userid;
    $companyuser->companyid = $companyid;
    $companyuser->departmentid = $departmentid;
    $DB->insert_record('company_users', $companyuser);

    echo $OUTPUT->notification(get_string('companycreatedsuccess', 'local_signup'), 'notifysuccess');
    echo $OUTPUT->continue_button(new moodle_url('/login/index.php'));
    echo $OUTPUT->footer();
    exit;
}

// If user confirmed and redirected from setpassword.php
$userid = optional_param('userid', 0, PARAM_INT);
if ($userid) {
    $companyform->set_data(['userid' => $userid]);
    $companyform->display();
    echo $OUTPUT->footer();
    exit;
}

// Initial signup form display
$signupform->display();
echo $OUTPUT->footer();
