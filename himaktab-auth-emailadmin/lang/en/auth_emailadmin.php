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
 * Defines the scheduled task for checking user subscriptions.
 *
 * @package    auth
 * @subpackage emailadmin
 *@copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
$string['pluginname'] = 'Admissions Management (Email)';
$string['auth_emailadmindescription'] = 'Enables users to apply for an account. An administrator receives an email to review the application and can then accept, deny, or message the applicant.';
$string['auth_emailadminconfirmationsubject'] = 'New Admission Application Submitted';
$string['auth_emailadminconfirmation'] = '
<p>Hi,</p>
<p>A new admission form has been submitted. Below are the key details:</p>
<p><strong>Applicant Information:</strong></p>
<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; width: 150px;"><strong>Applied class</strong></td>
        <td style="padding: 8px; border: 1px solid #ddd;">{$a->grade}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; width: 150px;"><strong>Email</strong></td>
        <td style="padding: 8px; border: 1px solid #ddd;">{$a->email}</td>
    </tr>
</table>
<p> </p>
<p><a href="{$a->link}" style="color: #007bff; text-decoration: none;"><strong>Application Link</strong></a></p>
<p>Please review the full application at your earliest convenience by clicking the link above.</p>';
$string['auth_emailadminuserconfirmationsubject'] = 'Your Admission to HiMaktab has been Approved';
$string['auth_emailadminuserconfirmationbody'] = '
<p>Dear {$a->firstname},</p>
<p>We have reviewed your admission application and are pleased to inform you that you qualify for the admission. Your account has been created.</p>
<p><strong>You can now log in to the student portal using the link below:</strong></p>
<p><a href="{$a->link}">Login to HiMaktab</a></p>
<p>Thank you for choosing Online School for your academic journey.</p>
<p> </p>
<p>Regards,<br>Admissions Office<br>HiMaktab</p>';
$string['auth_emailadmindenialsubject'] = 'An Update on Your Admission Application to HiMaktab';
$string['auth_emailadmindenialbody'] = '
<h3 style="text-align: center;">If Admission is Denied</h3>
<p>Dear {$a->firstname},</p>
<p>Thank you for your interest in your selected Grade. After reviewing your application, we regret to inform you that your admission has not been approved at this time due to limited seat availability. We appreciate your interest and encourage you to apply again in the future.</p>
<p>We understand this may be disappointing. If you have any questions or would like to request clarification or appeal the decision, please feel free to reach out to our admissions team.</p>
<p> </p>
<p style="text-align: left;">
    <a href="mailto:{$a->adminemail}" style="background-color: #4CAF50; color: white; padding: 14px 25px; text-align: center; text-decoration: none; display: inline-block; border-radius: 4px;">Contact Admin</a>
</p>
<p> </p>
<p>{$a->admin}</p>';
$string['auth_emailadminconfirmsent'] = 'Your application has been submitted and is under review. You will receive an email with the outcome shortly.';
$string['auth_emailadminawaitingapproval'] = 'Your account is currently awaiting approval from an administrator.';
$string['accept'] = 'Accept';
$string['deny'] = 'Deny';
$string['send'] = 'Send';
$string['sendmessage'] = 'Send Message';
$string['subject'] = 'Subject';
$string['message'] = 'Message';
$string['required'] = 'This field is required.';
$string['reviewaccount'] = 'Review New User Account';
$string['reviewdetails'] = 'Review details for {$a}';
$string['accountdenied'] = 'User Account Denied';
$string['sendmessagetouser'] = 'Write your message here!';
$string['messagesent'] = 'Your message has been sent successfully.';
$string['auth_emailadminnoemail'] = 'Error: Could not send confirmation email. Please contact the administrator.';
$string['accountdeniedanddeleted'] = 'The user account for {$a} has been denied and deleted.';
$string['useralredyconfirmed'] = 'This user has already been confirmed and cannot be denied.';
$string['errordeletinguser'] = 'Could not delete the user account.';
$string['auth_emailadminrecaptcha'] = 'Enable reCAPTCHA element';
$string['auth_emailadminrecaptcha_key'] = 'Enable reCAPTCHA element';
$string['auth_emailadminsettings'] = 'Settings';
$string['auth_emailadminnotif_failed'] = 'Could not send registration notification to: ';
$string['auth_emailadminnoadmin'] = 'No admin found based on notification strategy. Please check auth_emailadmin configuration.';
$string['auth_emailadminnotif_strategy_key'] = 'Notification strategy:';
$string['auth_emailadminnotif_strategy'] = 'Defines the strategy to send the registration notifications.';
$string['auth_emailadminnotif_strategy_first'] = 'First admin user';
$string['auth_emailadminnotif_strategy_all'] = 'All admin users';
$string['auth_emailadminnotif_strategy_allupdate'] = 'All admins and users with user update capability';
$string['privacy:metadata'] = 'The Admissions Management (Email) plugin does not store any personal data itself, but processes it during account creation.';
$string['auth_emailadminbasicnotification'] = '
<p>Hi,</p>
<p>A new admission application has been submitted by {$a->email}.</p>
<p>To view the full application details, including the desired grade, and to make a decision, please use the link below:</p>
<p> </p>
<p><a href="{$a->link}" style="color: #007bff; text-decoration: none;"><strong>Review Application Now</strong></a></p>';
$string['auth_emailadminbasicnotification'] = '
<p>Hi,</p>
<p>A new admission application has been submitted by {$a->email}.</p>
<p>To view the full application details, including the desired grade, and to make a decision, please use the link below:</p>
<p> </p>
<p><a href="{$a->link}" style="color: #007bff; text-decoration: none;"><strong>Review Application Now</strong></a></p>';
$string['auth_emailadminparentnotificationsubject'] = 'Your HiMaktab Parent Portal Account has been Created';
$string['auth_emailadminparentnotificationbody'] = '
<h3 style="font-family: sans-serif; text-align: center;">Parent Notification & Account Generation</h3>
<p style="font-family: sans-serif;">Dear {$a->parentname},</p>
<p style="font-family: sans-serif;">We\'re pleased to inform you that {$a->studentname} has been successfully enrolled in the {$a->grade} at HiMaktab.</p>

<h4 style="font-family: sans-serif;">Enrollment Confirmation:</h4>
<ul style="font-family: sans-serif;">
    <li><strong>Student Name:</strong> {$a->studentname}</li>
    <li><strong>Grade:</strong> {$a->grade}</li>
    <li><strong>Start Date:</strong> {$a->startdate}</li>
</ul>

<h4 style="font-family: sans-serif;">Your Parent Portal Access:</h4>
<p style="font-family: sans-serif;">You can now access the Parent Portal to monitor academic progress, attendance, and communication.</p>

<h4 style="font-family: sans-serif;">Login Credentials:</h4>
<ul style="font-family: sans-serif;">
    <li><strong>Username:</strong> {$a->username}</li>
    <li><strong>Password:</strong> {$a->password}</li>
</ul>
<p style="font-family: sans-serif;">For security, you will be required to change your password the first time you log in.</p>
<p style="font-family: sans-serif;"><strong>Portal Link:</strong> <a href="{$a->link}">{$a->link}</a></p>

<p style="font-family: sans-serif;">If you have any questions or need support, feel free to contact us.</p>
<p style="font-family: sans-serif;">
    Best regards,<br>
    Admissions Office<br>
    HiMaktab
</p>';

$string['auth_emailadminparentnotificationsubject'] = 'Your Parent Account for HiMaktab Has Been Created';

$string['auth_emailadminparentnotificationbody'] = 'Dear {$a->parentname},

A parent account has been created for you at HiMaktab, linked to your student, {$a->studentname}. This account will allow you to monitor your student\'s progress.

Your login details are:
Username: {$a->username}
Temporary Password: {$a->password}

Please log in at the link below and you will be prompted to choose a new, permanent password.

Login here: {$a->link}

Thank you,
The HiMaktab Team';

$string['auth_emailadminuserconfirmationsubject_upload'] = '{$a}: Account confirmed - Please upload your documents';
$string['auth_emailadminuserconfirmationbody_upload'] = '
Hi {$a->firstname},

Great news! A new account has been created for you at \'{$a->sitename}\' and has been confirmed by our administration team.

To complete your registration and gain access to the site, please follow the link below to upload your required documents. After a successful upload, you will be automatically logged in.

{$a->link}

In most mail programs, this should appear as a blue link which you can just click on. If that doesn\'t work, then cut and paste the address into the address line at the top of your web browser window.

If you need help, please contact the site administrator,
Admissions Office';

$string['uploaddocuments'] = 'Upload Required Documents';
$string['uploaddocuments_heading'] = 'Welcome, {$a}! Please Upload Your Documents';
$string['uploaddocuments_intro'] = 'Your account is confirmed. The final step is to upload the required documentation. Please select your file below and click "Submit and log in".';
$string['pleaseselectfile'] = 'Required document';
$string['uploaddocument_help'] = 'Please upload your required document. This can be in PDF, Word, or image format (PNG, JPG).';
$string['submit_and_login'] = 'Submit and log in';
$string['uploadsuccess'] = 'Thank you! Your document has been uploaded successfully. You are now logged in.';
$string['uploaderror'] = 'Sorry, there was an error and we could not save your file. Please try again or contact support.';
$string['usernotconfirmed'] = 'Your account has not been confirmed yet. You cannot upload documents at this time.';
$string['auth/emailadmin:uploaddocuments'] = 'Upload documents after account confirmation';
$string['applicationdetails'] = 'Application Details';
$string['uploaddocuments'] = 'Uploaded Documents'; 
$string['auth_emailadmindescription'] = 'An email-based self-registration method where an administrator must first confirm accounts.';
$string['pluginname'] = 'Email-based self-registration (Admin-Approval)';
$string['payment_required_subject'] = 'Action Required: Initial Payment for Your Enrollment at {$a}';
$string['payment_required_body_html'] = '<p>Dear {$a->studentname},</p>
<p>Congratulations! Your account for <strong>{$a->schoolname}</strong> has been approved by our administration.</p>
<p>The final step to activate your enrollment is to complete the initial payment, which covers your Admission Fee and first Monthly Fee. The total amount is <strong>{$a->totalfee}</strong>.</p>
<p style="text-align: center; margin: 30px 0;">
    <a href="{$a->paymentlink}" style="background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-size: 16px; display: inline-block;">Proceed to Payment</a>
</p>
<p>Once your payment is confirmed, you will receive another email with a link to upload your required documents.</p>
<p>If you have any questions, please contact our admissions office.</p>
<p>Best regards,<br><strong>{$a->schoolname}</strong></p>';
$string['payment_required_body_text'] = '... (plain text version) ...';
$string['paymentconfirmation'] = 'Payment Confirmation';
$string['paymentstatus'] = 'Payment Status';
$string['paymentconfirm_subject'] = 'Payment Confirmed: Next Steps for Your Enrollment at {$a}';
$string['paymentconfirm_body_html'] = '<p>Dear {$a->studentname},</p>
<p>We are pleased to confirm that your initial payment has been successfully received. Welcome to <strong>{$a->schoolname}</strong>!</p>
<p>The final step is to upload your required enrollment documents. Please click the link below to proceed:</p>
<p style="text-align: center; margin: 30px 0;">
    <a href="{$a->uploadlink}" style="background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-size: 16px; display: inline-block;">Upload Your Documents</a>
</p>
<p>This link is unique to you. Please complete the document upload at your earliest convenience to finalize your enrollment.</p>
<p>Best regards,<br><strong>{$a->schoolname}</strong></p>';
$string['paymentconfirm_body_text'] = '... (plain text version) ...';
$string['paymentconfirm_success_msg'] = 'Thank you! Your payment has been confirmed. You will receive an email shortly with a link to upload your documents. You can also proceed using the button below.';
$string['paymentfailed_msg'] = 'It appears the payment was not completed or failed. Please try again.';
$string['retrypayment'] = 'Retry Payment';
$string['auth_emailadminuserconfirmationsubject_payment'] = '{$a}: Your Account is Confirmed - Next Steps';
$string['auth_emailadminuserconfirmationbody_payment'] = 'Hi {$a->firstname},

A new account has been created for you at \'{$a->sitename}\'. Your account is now confirmed.

Please complete the following steps to finalize your enrollment:

1. UPLOAD DOCUMENTS
Please use the following link to access the upload page:
{$a->link}

2. SUBMIT PAYMENT
{$a->paymentlink}

In most mail programs, these should appear as blue links which you can just click on. If that does not work, then cut and paste the address into the address line at the top of your web browser window.

If you need help, please contact the site administrator.

Warm regards,
Admissions Office';

$string['submit_documents'] = 'Submit documents';
$string['uploadsuccess'] = 'Upload Successful';
$string['uploadsuccess_login'] = 'Your documents have been uploaded successfully. You can now proceed to your dashboard.';
$string['uploaddocuments_intro_multi'] = 'Please upload up to {$a} required documents.';
$string['uploaddocuments'] = 'Documents need to upload';
$string['uploaddocuments_heading'] = 'Welcome, {$a}! Please Upload Your Documents';
$string['uploaddocuments_intro_multi'] = 'Please upload up to {$a} required documents.';
$string['document1'] = 'Birth Certificate or Government-Issued ID or Tazkera';
$string['document2'] = 'Tazkera or Passport Number';
$string['document3'] = 'Academic History';

$string['submit_documents'] = 'Upload My Documents';
$string['uploadsuccess_login'] = 'Your documents have been uploaded successfully.';
$string['usernotconfirmed'] = 'User account is not yet confirmed and cannot upload documents.';
$string['uploaderror'] = 'An error occurred during file upload. Please try again.';
$string['proceedtopayment'] = 'Proceed to Payment';
$string['uploadsuccess'] = 'Your documents have been uploaded successfully.';
$string['uploadsuccess_paynext'] = 'Your next step is to pay the required fees. Please contact the administration office for payment instructions.';
$string['checksubscriptions'] = 'Check subscriptions';
$string['email_reminder_subject'] = 'Your Subscription is Expiring Soon';
$string['email_reminder_body'] = '
Hello {$a->firstname},

This is a reminder that your enrollment in the program "{$a->programname}" at {$a->sitename} is due to expire in approximately 7 days.

To avoid being unenrolled, please ensure your next payment is made on time. You can manage your subscription and payment methods here:
{$a->paymentlink}

If you have any questions, please contact our administration office.

Thank you,
The {$a->sitename} Team';

$string['parent_notification_subject'] = 'Parent Notification & Account Generation for {$a->studentname}';

$string['parent_notification_html'] = '
<div style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5;">
    <h2 style="font-size: 18px; color: #000; border-bottom: 1px solid #ddd; padding-bottom: 10px;">Parent Notification & Account Generation</h2>
    <p>Dear {$a->parentname},</p>
    <p>We\'re pleased to inform you that <strong>{$a->studentname}</strong> has been successfully enrolled in <strong>{$a->grade}</strong> at {$a->schoolname}.</p>
    <h3 style="font-size: 16px; margin-top: 25px; color: #000;">Enrollment Confirmation:</h3>
    <div style="padding-left: 15px; border-left: 3px solid #eee;">
        <p style="margin: 5px 0;"><strong>Student Name:</strong> {$a->studentname}</p>
        <p style="margin: 5px 0;"><strong>Grade:</strong> {$a->grade}</p>
        <p style="margin: 5px 0;"><strong>Start Date:</strong> {$a->startdate}</p>
    </div>
    <h3 style="font-size: 16px; margin-top: 25px; color: #000;">Your Parent Portal Access:</h3>
    <p>You can now access the Parent Portal to monitor academic progress, attendance, and communication.</p>
    <h4 style="font-size: 15px; margin-top: 20px;">Login Credentials:</h4>
    <div style="padding-left: 15px; border-left: 3px solid #eee;">
        <p style="margin: 5px 0;"><strong>Username:</strong> {$a->username}</p>
        <p style="margin: 5px 0;"><strong>Password:</strong> {$a->password}</p>
    </div>
    <p>For security, you will be required to change your password the first time you log in.</p>
    
    <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;" width="100%">
        <tbody>
            <tr>
                <td align="center" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;" valign="top">
                    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                        <tbody>
                            <tr>
                                <td style="font-family: sans-serif; font-size: 14px; vertical-align: top; border-radius: 5px; text-align: center; background-color: #3498db;" valign="top" align="center" bgcolor="#3498db">
                                    <a href="{$a->link}" target="_blank" style="display: inline-block; color: #ffffff; background-color: #3498db; border: solid 1px #3498db; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #3498db;">Access Parent Portal</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top: 30px;">If you have any questions or need support, feel free to contact us.</p>
    <p>Best regards,<br>
       {$a->office}<br>
       {$a->schoolname}</p>
</div>';
$string['adminconfirmationsuccess'] = 'You have successfully confirmed the registration for {$a}. They have now been sent an email with their next steps.';
$string['error_payment_pending'] = 'Your account has been approved, but is awaiting payment. Please complete the payment process to log in.';
$string['email_reminder_body'] = '
<div style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5;">
    <p>Dear {$a->firstname},</p>
    <p>This is a reminder that your enrollment in the program "{$a->programname}" at {$a->sitename} is due to expire in approximately 7 days.</p>
    <p>To avoid being unenrolled, please click the button below to complete your payment for the next month.</p>
    
    <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;" width="100%">
        <tbody>
            <tr>
                <td align="center" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;" valign="top">
                    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                        <tbody>
                            <tr>
                                <td style="font-family: sans-serif; font-size: 14px; vertical-align: top; border-radius: 5px; text-align: center; background-color: #3498db;" valign="top" align="center" bgcolor="#3498db">
                                    <a href="{$a->paymentlink}" target="_blank" style="display: inline-block; color: #ffffff; background-color: #3498db; border: solid 1px #3498db; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #3498db;">Pay Monthly Fee</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <p>If you have any questions, please contact our administration office.</p>
    <p>Thank you,<br>The {$a->sitename} Team</p>
</div>
';