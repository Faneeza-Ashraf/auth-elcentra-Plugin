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

$string['pluginname'] = 'د داخلي مدیریت (بریښنالیک)';
$string['auth_emailadmindescription'] = 'کاروونکو ته اجازه ورکوي چې د حساب لپاره غوښتنلیک وړاندې کړي. یو مدیر بریښنالیک ترلاسه کوي ترڅو غوښتنلیک وګوري او بیا یې منل، ردول، یا د غوښتونکي سره پیغام لیږل کیدی شي.';
$string['auth_emailadminconfirmationsubject'] = 'نوی د داخلي غوښتنلیک وړاندې شوی';
$string['auth_emailadminconfirmation'] = '
<p>سلام،</p>
<p>یو نوی داخلي فورمه وړاندې شوې ده. لاندې مهم معلومات دي:</p>
<p><strong>د غوښتونکي معلومات:</strong></p>
<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; width: 150px;"><strong>غوښتل شوی ټولګی</strong></td>
        <td style="padding: 8px; border: 1px solid #ddd;">{$a->grade}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; width: 150px;"><strong>بریښنالیک</strong></td>
        <td style="padding: 8px; border: 1px solid #ddd;">{$a->email}</td>
    </tr>
</table>
<p> </p>
<p><a href="{$a->link}" style="color: #007bff; text-decoration: none;"><strong>د غوښتنلیک لینک</strong></a></p>
<p>مهرباني وکړئ پورته لینک باندې کلیک وکړئ ترڅو د بشپړ غوښتنلیک بیاکتنه وکړئ.</p>';
$string['auth_emailadminuserconfirmationsubject'] = 'ستاسو HiMaktab داخله تایید شوه';
$string['auth_emailadminuserconfirmationbody'] = '
<p>ګرانه {$a->firstname},</p>
<p>موږ ستاسو د داخلي غوښتنلیک بیاکتنه وکړه او خوښ یو چې تاسو د داخلي لپاره وړ یاست. ستاسو حساب جوړ شوی دی.</p>
<p><strong>تاسو اوس کولی شئ د لاندې لینک څخه د زده کوونکو پورتل ته ننوتل وکړئ:</strong></p>
<p><a href="{$a->link}">HiMaktab ته ننوتل</a></p>
<p>ستاسو د علمي سفر لپاره د آنلاین ښوونځي د انتخاب مننه.</p>
<p> </p>
<p>په درناوي،<br>د داخلي دفتر<br>HiMaktab</p>';
$string['auth_emailadmindenialsubject'] = 'ستاسو د HiMaktab داخلي غوښتنلیک په اړه تازه معلومات';
$string['auth_emailadmindenialbody'] = '
<h3 style="text-align: center;">که داخله رد شوې وي</h3>
<p>ګرانه {$a->firstname},</p>
<p>ستاسو د غوښتل شوي ټولګي لپاره د داخلي غوښتنلیک لپاره مننه. د غوښتنلیک د بیاکتنې وروسته، موږ بخښنه غواړو چې ستاسو داخله د محدودې څوکۍ له امله نه شوه تایید شوې. موږ ستاسو علاقه ستایو او تاسو ته سپارښتنه کوو چې بیا په راتلونکي کې غوښتنلیک وړاندې کړئ.</p>
<p>موږ پوهیږو چې دا ممکن مایوسه کوونکی وي. که تاسو کومه پوښتنه لرئ یا غواړئ د پریکړې وضاحت یا اپیل وغواړئ، مهرباني وکړئ زموږ د داخلي ټیم سره اړیکه ونیسئ.</p>
<p> </p>
<p style="text-align: left;">
    <a href="mailto:{$a->adminemail}" style="background-color: #4CAF50; color: white; padding: 14px 25px; text-align: center; text-decoration: none; display: inline-block; border-radius: 4px;">د مدیر سره اړیکه</a>
</p>
<p> </p>
<p>{$a->admin}</p>';
$string['auth_emailadminconfirmsent'] = 'ستاسو غوښتنلیک وړاندې شوی او د بیاکتنې لاندې دی. ژر به تاسو د پایلې بریښنالیک ترلاسه کړئ.';
$string['auth_emailadminawaitingapproval'] = 'ستاسو حساب د مدیر د تایید لپاره په تمه دی.';

$string['accept'] = 'منل';
$string['deny'] = 'ردول';
$string['send'] = 'لیږل';
$string['sendmessage'] = 'پیغام واستوئ';
$string['subject'] = 'موضوع';
$string['message'] = 'پیغام';
$string['required'] = 'دغه ډګر اړین دی.';

$string['reviewaccount'] = 'د نوي کارونکي حساب بیاکتنه';
$string['reviewdetails'] = 'د {$a} لپاره جزییات بیاکتنه';
$string['accountdenied'] = 'د کارونکي حساب رد شو';
$string['sendmessagetouser'] = 'دلته خپل پیغام ولیکئ!';
$string['messagesent'] = 'ستاسو پیغام په بریالیتوب سره واستول شو.';

$string['auth_emailadminnoemail'] = 'تېروتنه: د تایید بریښنالیک نشو استولی. مهرباني وکړئ له مدیر سره اړیکه ونیسئ.';
$string['accountdeniedanddeleted'] = 'د {$a} کارونکي حساب رد شوی او حذف شوی.';
$string['useralredyconfirmed'] = 'دا کارونکی دمخه تایید شوی او نه شي رد کیدی.';
$string['errordeletinguser'] = 'کارونکي حساب نشو حذف کیدی.';

$string['auth_emailadminrecaptcha'] = 'د reCAPTCHA فعالول';
$string['auth_emailadminrecaptcha_key'] = 'د reCAPTCHA فعالول';
$string['auth_emailadminsettings'] = 'تنظیمات';
$string['auth_emailadminnotif_failed'] = 'د ثبت نام خبرتیا نشو استولی: ';
$string['auth_emailadminnoadmin'] = 'د خبرتیا د ستراتیژۍ پر اساس هیڅ مدیر ونه موندل شو. مهرباني وکړئ auth_emailadmin تنظیمات وګورئ.';
$string['auth_emailadminnotif_strategy_key'] = 'د خبرتیا ستراتیژي:';
$string['auth_emailadminnotif_strategy'] = 'د ثبت نام خبرتیاو د لیږلو ستراتیژي تعریفوي.';
$string['auth_emailadminnotif_strategy_first'] = 'لومړی مدیر کارونکی';
$string['auth_emailadminnotif_strategy_all'] = 'ټول مدیران';
$string['auth_emailadminnotif_strategy_allupdate'] = 'ټول مدیران او هغه کارونکي چې د کارونکي تازه کولو وړتیا لري';

$string['privacy:metadata'] = 'د داخلي مدیریت (بریښنالیک) پلگین پخپله هیڅ شخصي معلومات نه ذخیره کوي، خو د حساب جوړولو پر مهال یې پروسس کوي.';
$string['auth_emailadminbasicnotification'] = '
<p>سلام،</p>
<p>د {$a->email} لخوا یو نوی داخلي غوښتنلیک وړاندې شوی دی.</p>
<p>د بشپړ غوښتنلیک جزییاتو لیدلو، د غوښتل شوي ټولګي په ګډون، او د پریکړې لپاره، لطفاً لاندې لینک وکاروئ:</p>
<p> </p>
<p><a href="{$a->link}" style="color: #007bff; text-decoration: none;"><strong>اوس غوښتنلیک بیاکتنه وکړئ</strong></a></p>';
$string['auth_emailadminparentnotificationsubject'] = 'ستاسو د HiMaktab والدین حساب جوړ شو';
$string['auth_emailadminparentnotificationbody'] = '
<h3 style="text-align: center;">د والدین خبرتیا او حساب جوړول</h3>
<p>ګرانه {$a->parentname},</p>
<p>موږ خوښ یو چې تاسو ته خبر درکړو چې {$a->studentname} په بریالیتوب سره د {$a->grade} ټولګي کې داخل شو.</p>

<h4>د داخلي تایید:</h4>
<ul>
    <li><strong>د زده کوونکي نوم:</strong> {$a->studentname}</li>
    <li><strong>ټولګی:</strong> {$a->grade}</li>
    <li><strong>د پیل نېټه:</strong> {$a->startdate}</li>
</ul>

<h4>ستاسو د والدین پورتل لاسرسی:</h4>
<p>تاسو اوس کولی شئ والدین پورتل ته لاسرسی ومومئ ترڅو د زده کوونکي پرمختګ، حاضري او اړیکو څارنه وکړئ.</p>

<h4>د ننوتلو جزییات:</h4>
<ul>
    <li><strong>کارن نوم:</strong> {$a->username}</li>
    <li><strong>موقتي رمز:</strong> {$a->password}</li>
</ul>
<p>د امنیت لپاره، تاسو به اړ شئ چې لومړی ځل ننوتلو پر مهال خپل رمز بدل کړئ.</p>
<p><strong>د پورتل لینک:</strong> <a href="{$a->link}">{$a->link}</a></p>

<p>که تاسو کومه پوښتنه لرئ یا ملاتړ ته اړتیا لرئ، مهرباني وکړئ له موږ سره اړیکه ونیسئ.</p>
<p>په درناوي،<br>د داخلي دفتر<br>HiMaktab</p>';
$string['auth_emailadminuserconfirmationsubject_upload'] = '{$a}: حساب تایید شو – مهرباني وکړئ خپل اسناد پورته کړئ';
$string['auth_emailadminuserconfirmationbody_upload'] = '
سلام {$a->firstname},

ښه خبر! په \'{$a->sitename}\' کې ستاسو نوی حساب جوړ شوی او زموږ د ادارې ټیم لخوا تایید شوی.

د ثبت بشپړولو او سایټ ته لاسرسی لپاره، مهرباني وکړئ لاندې لینک کې خپل اړین اسناد پورته کړئ. د بریالۍ پورته کولو وروسته، تاسو به په اتوماتيک ډول ننوتل شئ.

{$a->link}

که اړتیا لرئ مرسته ترلاسه کړئ، مهرباني وکړئ د سایټ مدیر سره اړیکه ونیسئ،
د داخلي دفتر';
$string['uploaddocuments'] = 'اړین اسناد پورته کړئ';
$string['uploaddocuments_heading'] = 'ښه راغلاست، {$a}! مهرباني وکړئ خپل اسناد پورته کړئ';
$string['uploaddocuments_intro'] = 'ستاسو حساب تایید شوی دی. وروستۍ مرحله د اړینو اسنادو پورته کول دي. مهرباني وکړئ لاندې فایل انتخاب کړئ او "Submit and log in" باندې کلیک وکړئ.';
$string['pleaseselectfile'] = 'اړین سند';
$string['uploaddocument_help'] = 'مهرباني وکړئ خپل اړین سند پورته کړئ. دا کولی شي په PDF، Word، یا عکس (PNG، JPG) کې وي.';
$string['submit_and_login'] = 'پورته کړئ او ننوتل';
$string['uploadsuccess'] = 'مننه! ستاسو سند په بریالیتوب سره پورته شو. تاسو اوس ننوتلي یاست.';
$string['uploaderror'] = 'بښنه غواړو، یوه تېروتنه وشوه او موږ ستاسو فایل نشو خوندي کولی. مهرباني وکړئ بیا هڅه وکړئ یا له ملاتړ سره اړیکه ونیسئ.';
$string['usernotconfirmed'] = 'ستاسو حساب لا تراوسه تایید شوی نه دی. تاسو نشئ کولی اوس اسناد پورته کړئ.';
$string['auth/emailadmin:uploaddocuments'] = 'د حساب تایید وروسته اسناد پورته کړئ';
$string['applicationdetails'] = 'د غوښتنلیک جزییات';
$string['uploaddocuments'] = 'پورته شوي اسناد';

$string['auth_emailadmindescription'] = 'د بریښنالیک پر بنسټ د ځان ثبت کولو میتود، چیرې چې مدیر باید لومړی حسابونه تایید کړي.';
$string['pluginname'] = 'د بریښنالیک پر بنسټ ځان ثبت کول (د مدیر تایید)';

$string['payment_required_subject'] = 'اقدام اړین: ستاسو د داخلي لومړنۍ تادیه په {$a} کې';
$string['payment_required_body_html'] = '<p>ګرانه {$a->studentname},</p>
<p>مبارکۍ! ستاسو حساب په <strong>{$a->schoolname}</strong> کې زموږ د ادارې لخوا تایید شوی.</p>
<p>د داخلي د فعالولو وروستۍ مرحله دا ده چې لومړنۍ تادیه بشپړه کړئ، کوم چې د داخلي فیس او د میاشتني فیس لومړۍ برخه پوښي. ټولټال مقدار دی: <strong>{$a->totalfee}</strong>.</p>
<p style="text-align: center; margin: 30px 0;">
    <a href="{$a->paymentlink}" style="background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-size: 16px; display: inline-block;">تادیې ته لاړ شئ</a>
</p>
<p>کله چې ستاسو تادیه تایید شي، تاسو به بل بریښنالیک ترلاسه کړئ چې ستاسو د اړینو اسنادو پورته کولو لینک به ولري.</p>
<p>که کومه پوښتنه لرئ، مهرباني وکړئ زموږ د داخلي دفتر سره اړیکه ونیسئ.</p>
<p>په درناوي،<br><strong>{$a->schoolname}</strong></p>';

$string['payment_required_body_text'] = '... (plain text version) ...';

$string['paymentconfirmation'] = 'د تادیې تایید';
$string['paymentstatus'] = 'د تادیې وضعیت';
$string['paymentconfirm_subject'] = 'تادیه تایید شوه: ستاسو د داخلي راتلونکی ګامونه په {$a} کې';
$string['paymentconfirm_body_html'] = '<p>ګرانه {$a->studentname},</p>
<p>موږ خوښ یو چې تایید کړو ستاسو لومړنۍ تادیه په بریالیتوب سره ترلاسه شوې. ښه راغلاست په <strong>{$a->schoolname}</strong> کې!</p>
<p>وروستۍ مرحله د اړینو اسنادو پورته کول دي. مهرباني وکړئ لاندې لینک باندې کلیک وکړئ:</p>
<p style="text-align: center; margin: 30px 0;">
    <a href="{$a->uploadlink}" style="background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-size: 16px; display: inline-block;">خپل اسناد پورته کړئ</a>
</p>
<p>دا لینک یواځې ستاسو لپاره ځانګړی دی. مهرباني وکړئ د داخلي بشپړولو لپاره ژر تر ژره اسناد پورته کړئ.</p>
<p>په درناوي،<br><strong>{$a->schoolname}</strong></p>';

$string['paymentconfirm_body_text'] = '... (plain text version) ...';
$string['paymentconfirm_success_msg'] = 'مننه! ستاسو تادیه تایید شوه. تاسو به ژر بریښنالیک ترلاسه کړئ چې ستاسو د اسنادو پورته کولو لینک ولري. تاسو کولی شئ لاندې تڼۍ هم وکاروئ.';
$string['paymentfailed_msg'] = 'داسې ښکاري چې تادیه بشپړه نه شوه یا ناکامه شوه. مهرباني وکړئ بیا هڅه وکړئ.';
$string['retrypayment'] = 'تادیه بیا هڅه وکړئ';
$string['auth_emailadminuserconfirmationsubject_payment'] = '{$a}: ستاسو حساب تایید شو - راتلونکي ګامونه';

$string['auth_emailadminuserconfirmationbody_payment'] = 'سلام {$a->firstname},

ستاسو نوی حساب په \'{$a->sitename}\' کې جوړ شوی دی. ستاسو حساب اوس تایید شوی دی.

مهرباني وکړئ د داخلي بشپړولو لپاره لاندې ګامونه ترسره کړئ:

1. اسناد پورته کړئ
د پورته کولو پاڼې لپاره لینک وکاروئ:
{$a->link}

2. تادیه وکړئ
{$a->paymentlink}

په ډیرو بریښنالیک پروګرامونو کې، دا به د شین لینک په څیر ښکاره شي چې تاسو یې یوازې کلیک کولی شئ. که دا کار ونکړي، آدرس کاپي او په خپل براوزر کې پیسټ کړئ.

که مرسته ته اړتیا لرئ، مهرباني وکړئ د سایټ مدیر سره اړیکه ونیسئ.

په درناوي،
د داخلي دفتر';

$string['submit_documents'] = 'زما اسناد پورته کړئ';
$string['uploadsuccess_login'] = 'ستاسو اسناد په بریالیتوب سره پورته شوي دي.';
$string['uploaddocuments_intro_multi'] = 'مهرباني وکړئ تر {$a} پورې اړین اسناد پورته کړئ.';
$string['document1'] = 'د زیږون سند یا د حکومت صادر شوی ID یا تذکره';
$string['document2'] = 'تذکره یا پاسپورټ شمېره';
$string['document3'] = 'علمي تاریخ';

$string['proceedtopayment'] = 'تادیې ته لاړ شئ';
$string['checksubscriptions'] = 'اشتراکات وګورئ';
$string['email_reminder_subject'] = 'ستاسو ګډون ژر پای ته رسیږي';
$string['email_reminder_body'] = '
سلام {$a->firstname},

دا یوه یادونه ده چې ستاسو ګډون په "{$a->programname}" برنامه کې په {$a->sitename} کې شاوخوا ۷ ورځو کې پای ته رسیږي.

د دې لپاره چې ستاسو ګډون لغوه نشي، مهرباني وکړئ په وخت سره تادیه وکړئ. تاسو کولی شئ دلته خپل ګډون او د تادیې میتودونه اداره کړئ:
{$a->paymentlink}

که کومه پوښتنه لرئ، مهرباني وکړئ زموږ د ادارې دفتر سره اړیکه ونیسئ.

مننه،
د {$a->sitename} ټیم';

$string['parent_notification_subject'] = 'د {$a->studentname} لپاره د والدینو خبرتیا او حساب جوړول';
$string['parent_notification_html'] = '
<div style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5;">
    <h2>د والدینو خبرتیا او حساب جوړول</h2>
    <p>ګرانه {$a->parentname},</p>
    <p>موږ خوښ یو چې خبر درکړو چې <strong>{$a->studentname}</strong> په بریالیتوب سره د <strong>{$a->grade}</strong> ټولګي کې په {$a->schoolname} کې داخله شوې.</p>
    <h3>د داخلي تایید:</h3>
    <ul>
        <li><strong>د زده کوونکي نوم:</strong> {$a->studentname}</li>
        <li><strong>ټولګی:</strong> {$a->grade}</li>
        <li><strong>د پیل نېټه:</strong> {$a->startdate}</li>
    </ul>
    <h3>ستاسو د والدینو پورتل لاسرسی:</h3>
    <p>تاسو اوس کولی شئ د زده کوونکي پرمختګ، حاضري او اړیکو څارنه وکړئ.</p>
    <h4>د ننوتلو جزییات:</h4>
    <ul>
        <li><strong>کارن نوم:</strong> {$a->username}</li>
        <li><strong>رمز:</strong> {$a->password}</li>
    </ul>
    <p>د امنیت لپاره، تاسو به اړ شئ چې لومړی ځل ننوتلو پر مهال خپل رمز بدل کړئ.</p>
    <p><a href="{$a->link}" style="background-color: #3498db; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px;">د والدینو پورتل ته لاسرسی</a></p>
    <p>که کومه پوښتنه لرئ یا ملاتړ ته اړتیا لرئ، مهرباني وکړئ موږ سره اړیکه ونیسئ.</p>
    <p>په درناوي,<br>{$a->office}<br>{$a->schoolname}</p>
</div>';
$string['adminconfirmationsuccess'] = 'تاسو په بریالیتوب سره د {$a} نوم ثبتول تایید کړل. هغوی ته اوس د خپلو راتلونکو ګامونو په اړه بریښنالیک استول شوی دی.';
$string['error_payment_pending'] = 'ستاسو حساب تصویب شوی، خو د تادیې په تمه دی. مهرباني وکړئ د ننوتلو لپاره د تادیې پروسه بشپړه کړئ.';
$string['email_reminder_body'] = '
<div style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5;">
    <p>ګرانه {$a->firstname},</p>
    <p>دا یو یادښت دی چې ستاسو ګډون په "{$a->programname}" پروګرام کې د {$a->sitename} له لارې نږدې ۷ ورځو کې پای ته رسېږي.</p>
    <p>د دې لپاره چې له پروګرام څخه ونه ایستل شئ، مهرباني وکړئ لاندې تڼۍ باندې کلیک وکړئ او د راتلونکې میاشتې فیس ورکړئ.</p>
    
    <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;" width="100%">
        <tbody>
            <tr>
                <td align="center" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;" valign="top">
                    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                        <tbody>
                            <tr>
                                <td style="font-family: sans-serif; font-size: 14px; vertical-align: top; border-radius: 5px; text-align: center; background-color: #3498db;" valign="top" align="center" bgcolor="#3498db">
                                    <a href="{$a->paymentlink}" target="_blank" style="display: inline-block; color: #ffffff; background-color: #3498db; border: solid 1px #3498db; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #3498db;">میاشتنی فیس ورکړئ</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <p>که کومه پوښتنه لرئ، مهرباني وکړئ د ادارې دفتر سره اړیکه ونیسئ.</p>
    <p>مننه،<br> د {$a->sitename} ټیم</p>
</div>
';
