<?php

/**
 * @author Syed Mahtab Hussain
 * @copyright 2012
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot. '/blocks/end_user_support/lib.php');
require_login();
$id = optional_param('id',0,PARAM_INT);
$username = optional_param('username', 0, PARAM_INT);
$email = optional_param('email', 0, PARAM_INT);
$name = optional_param('name', 0 , PARAM_TEXT);
$description = required_param('description', PARAM_TEXT);
$url = required_param('url', PARAM_URL);
$status = optional_param('status' , 0 , PARAM_TEXT);
$PAGE->set_url(new moodle_url($CFG->wwwroot.'/blocks/end_user_support/contact.php'));
$context = context_system::instance();
$PAGE->set_context($context);
global $USER, $COURSE;
$formdata = $_POST;
$coursecontext = context_course::instance($COURSE->id);
$descriptionhtml = end_user_support_displaylinks($formdata);
$formdata['description'] = $descriptionhtml;
$subject = get_string('supportsubject', 'block_end_user_support', $formdata);
$message = get_string('supportemail', 'block_end_user_support', $formdata);
$messagehtml = text_to_html($message, false, false, true);
$supportuser = core_user::get_support_user();
$supportuser->mailformat = 1;
if(isloggedin() && !is_guest($coursecontext)){
    email_to_user($supportuser, $USER, $subject, $message, $messagehtml, '', '' ,'', $USER->email);
}else{
    $name = explode(' ', $formdata['username']);
    $from = new stdClass();
    $from->firstname = $name[0];
    count($name) > 1 ? $from->lastname = $name[1] : $from->lastname = '';
    $from->email = $formdata['email'];
    email_to_user($supportuser, $from, $subject, $message, $messagehtml, '', '', '', $formdata['email']);
}

 notice('Your email has been received and we will respond you with in next 24 - 48 hours.', new moodle_url($formdata['url']));
?>