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
 * Farsi language pack for the Admissions Management (Email) plugin.
 *
 * @package    auth
 * @subpackage emailadmin
 *@copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
$string['pluginname'] = 'مدیریت پذیرش (ایمیل)';
$string['auth_emailadmindescription'] = 'یک روش ثبت‌نام خودکار مبتنی بر ایمیل که در آن مدیر باید ابتدا حساب‌ها را تأیید کند.';
$string['auth_emailadminconfirmationsubject'] = 'درخواست پذیرش جدید ارسال شد';
$string['auth_emailadminconfirmation'] = '
<p>سلام،</p>
<p>یک فرم پذیرش جدید ارسال شده است. جزئیات کلیدی در زیر آمده است:</p>
<p><strong>اطلاعات متقاضی:</strong></p>
<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; width: 150px;"><strong>کلاس درخواستی</strong></td>
        <td style="padding: 8px; border: 1px solid #ddd;">{$a->grade}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; width: 150px;"><strong>ایمیل</strong></td>
        <td style="padding: 8px; border: 1px solid #ddd;">{$a->email}</td>
    </tr>
</table>
<p> </p>
<p><a href="{$a->link}" style="color: #007bff; text-decoration: none;"><strong>لینک درخواست</strong></a></p>
<p>لطفاً در اولین فرصت با کلیک بر روی لینک بالا، درخواست کامل را بررسی کنید.</p>';
$string['auth_emailadminuserconfirmationsubject'] = 'پذیرش شما در HiMaktab تأیید شد';
$string['auth_emailadminuserconfirmationbody'] = '
<p>کاربر گرامی {$a->firstname}،</p>
<p>ما درخواست پذیرش شما را بررسی کرده و خوشحالیم به اطلاع برسانیم که شما برای پذیرش واجد شرایط هستید. حساب کاربری شما ایجاد شده است.</p>
<p><strong>اکنون می‌توانید با استفاده از لینک زیر وارد پورتال دانش‌آموزی شوید:</strong></p>
<p><a href="{$a->link}">ورود به HiMaktab</a></p>
<p>از اینکه مدرسه آنلاین ما را برای مسیر تحصیلی خود انتخاب کردید سپاسگزاریم.</p>
<p> </p>
<p>با احترام،<br>دفتر پذیرش<br>HiMaktab</p>';
$string['auth_emailadmindenialsubject'] = 'به‌روزرسانی در مورد درخواست پذیرش شما در HiMaktab';
$string['auth_emailadmindenialbody'] = '
<h3 style="text-align: center;">در صورت رد پذیرش</h3>
<p>کاربر گرامی {$a->firstname}،</p>
<p>از علاقه شما به پایه تحصیلی انتخابی سپاسگزاریم. پس از بررسی درخواست شما، متأسفانه به دلیل محدودیت ظرفیت، پذیرش شما در این زمان تأیید نشد. ما از علاقه شما قدردانی کرده و شما را تشویق می‌کنیم تا در آینده دوباره درخواست دهید.</p>
<p>می‌دانیم که این خبر ممکن است ناامیدکننده باشد. اگر سؤالی دارید یا می‌خواهید درخواست توضیح یا تجدیدنظر کنید، لطفاً با تیم پذیرش ما تماس بگیرید.</p>
<p> </p>
<p style="text-align: left;">
    <a href="mailto:{$a->adminemail}" style="background-color: #4CAF50; color: white; padding: 14px 25px; text-align: center; text-decoration: none; display: inline-block; border-radius: 4px;">تماس با مدیر</a>
</p>
<p> </p>
<p>{$a->admin}</p>';
$string['parent_notification_subject'] = 'اطلاعیه والدین و ایجاد حساب کاربری برای {$a->studentname}';
$string['parent_notification_html'] = '
<div style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5;">
    <h2 style="font-size: 18px; color: #000; border-bottom: 1px solid #ddd; padding-bottom: 10px;">اطلاعیه والدین و ایجاد حساب کاربری</h2>
    <p>والد گرامی {$a->parentname}،</p>
    <p>خوشحالیم به اطلاع برسانیم که <strong>{$a->studentname}</strong> با موفقیت در <strong>{$a->grade}</strong> در {$a->schoolname} ثبت‌نام شده است.</p>
    <h3 style="font-size: 16px; margin-top: 25px; color: #000;">تأیید ثبت‌نام:</h3>
    <div style="padding-left: 15px; border-left: 3px solid #eee;">
        <p style="margin: 5px 0;"><strong>نام دانش‌آموز:</strong> {$a->studentname}</p>
        <p style="margin: 5px 0;"><strong>پایه تحصیلی:</strong> {$a->grade}</p>
        <p style="margin: 5px 0;"><strong>تاریخ شروع:</strong> {$a->startdate}</p>
    </div>
    <h3 style="font-size: 16px; margin-top: 25px; color: #000;">دسترسی شما به پورتال والدین:</h3>
    <p>اکنون می‌توانید برای نظارت بر پیشرفت تحصیلی، حضور و غیاب و ارتباطات به پورتال والدین دسترسی پیدا کنید.</p>
    <h4 style="font-size: 15px; margin-top: 20px;">اطلاعات ورود به سیستم:</h4>
    <div style="padding-left: 15px; border-left: 3px solid #eee;">
        <p style="margin: 5px 0;"><strong>نام کاربری:</strong> {$a->username}</p>
        <p style="margin: 5px 0;"><strong>رمز عبور:</strong> {$a->password}</p>
    </div>
    <p>برای امنیت، در اولین ورود از شما خواسته می‌شود که رمز عبور خود را تغییر دهید.</p>
    <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;" width="100%">
        <tbody>
            <tr>
                <td align="center" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;" valign="top">
                    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                        <tbody>
                            <tr>
                                <td style="font-family: sans-serif; font-size: 14px; vertical-align: top; border-radius: 5px; text-align: center; background-color: #3498db;" valign="top" align="center" bgcolor="#3498db">
                                    <a href="{$a->link}" target="_blank" style="display: inline-block; color: #ffffff; background-color: #3498db; border: solid 1px #3498db; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #3498db;">دسترسی به پورتال والدین</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <p style="margin-top: 30px;">اگر سؤالی دارید یا به پشتیبانی نیاز دارید، با ما تماس بگیرید.</p>
    <p>با احترام،<br>
       {$a->office}<br>
       {$a->schoolname}</p>
</div>';
$string['auth_emailadminconfirmsent'] = 'درخواست شما ارسال شده و در حال بررسی است. به زودی ایمیلی با نتیجه دریافت خواهید کرد.';
$string['auth_emailadminawaitingapproval'] = 'حساب کاربری شما در حال حاضر در انتظار تأیید مدیر است.';
$string['adminconfirmationsuccess'] = 'شما با موفقیت ثبت‌نام {$a} را تأیید کردید. اکنون ایمیلی با مراحل بعدی برای ایشان ارسال شده است.';
$string['accept'] = 'پذیرش';
$string['deny'] = 'رد کردن';
$string['send'] = 'ارسال';
$string['sendmessage'] = 'ارسال پیام';
$string['subject'] = 'موضوع';
$string['message'] = 'پیام';
$string['required'] = 'این فیلد الزامی است.';
$string['reviewaccount'] = 'بررسی حساب کاربری جدید';
$string['reviewdetails'] = 'بررسی جزئیات برای {$a}';
$string['accountdenied'] = 'حساب کاربری رد شد';
$string['sendmessagetouser'] = 'پیام خود را اینجا بنویسید!';
$string['messagesent'] = 'پیام شما با موفقیت ارسال شد.';
$string['applicationdetails'] = 'جزئیات درخواست';
$string['auth_emailadminnoemail'] = 'خطا: ایمیل تأیید ارسال نشد. لطفاً با مدیر تماس بگیرید.';
$string['accountdeniedanddeleted'] = 'حساب کاربری {$a} رد و حذف شد.';
$string['useralredyconfirmed'] = 'این کاربر قبلاً تأیید شده و قابل رد کردن نیست.';
$string['errordeletinguser'] = 'حذف حساب کاربری امکان‌پذیر نبود.';
$string['error_payment_pending'] = 'حساب شما تأیید شده است، اما در انتظار پرداخت است. لطفاً برای ورود به سیستم، فرآیند پرداخت را تکمیل کنید.';
$string['uploaderror'] = 'متأسفانه خطایی رخ داد و نتوانستیم فایل شما را ذخیره کنیم. لطفاً دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.';
$string['usernotconfirmed'] = 'حساب کاربری شما هنوز تأیید نشده است. در حال حاضر نمی‌توانید مدارک را بارگذاری کنید.';
$string['paymentfailed_msg'] = 'به نظر می‌رسد پرداخت تکمیل نشده یا ناموفق بوده است. لطفاً دوباره تلاش کنید.';
$string['auth_emailadminsettings'] = 'تنظیمات';
$string['auth_emailadminrecaptcha'] = 'فعال کردن reCAPTCHA';
$string['auth_emailadminrecaptcha_key'] = 'فعال کردن reCAPTCHA';
$string['auth_emailadminnotif_failed'] = 'اعلان ثبت‌نام برای این آدرس ارسال نشد: ';
$string['auth_emailadminnoadmin'] = 'بر اساس استراتژی اعلان، هیچ مدیری یافت نشد. لطفاً پیکربندی auth_emailadmin را بررسی کنید.';
$string['auth_emailadminnotif_strategy_key'] = 'استراتژی اعلان:';
$string['auth_emailadminnotif_strategy'] = 'استراتژی ارسال اعلان‌های ثبت‌نام را تعریف می‌کند.';
$string['auth_emailadminnotif_strategy_first'] = 'اولین کاربر مدیر';
$string['auth_emailadminnotif_strategy_all'] = 'همه کاربران مدیر';
$string['auth_emailadminnotif_strategy_allupdate'] = 'همه مدیران و کاربرانی که قابلیت به‌روزرسانی کاربر را دارند';
$string['auth/emailadmin:uploaddocuments'] = 'بارگذاری مدارک پس از تأیید حساب';
$string['uploaddocuments'] = 'بارگذاری مدارک مورد نیاز';
$string['uploaddocuments_heading'] = 'خوش آمدید، {$a}! لطفاً مدارک خود را بارگذاری کنید';
$string['uploaddocuments_intro'] = 'حساب شما تأیید شده است. مرحله نهایی بارگذاری مدارک مورد نیاز است. لطفاً فایل خود را در زیر انتخاب کرده و روی "ارسال و ورود به سیستم" کلیک کنید.';
$string['uploaddocuments_intro_multi'] = 'لطفاً تا {$a} مدرک مورد نیاز را بارگذاری کنید.';
$string['pleaseselectfile'] = 'مدرک مورد نیاز';
$string['uploaddocument_help'] = 'لطفاً مدرک مورد نیاز خود را بارگذاری کنید. این مدرک می‌تواند در قالب PDF، Word یا تصویر (PNG, JPG) باشد.';
$string['document1'] = 'شناسنامه یا کارت شناسایی دولتی یا تذکره';
$string['document2'] = 'شماره تذکره یا گذرنامه';
$string['document3'] = 'سوابق تحصیلی';
$string['submit_and_login'] = 'ارسال و ورود به سیستم';
$string['submit_documents'] = 'بارگذاری مدارک من';
$string['uploadsuccess'] = 'متشکریم! مدرک شما با موفقیت بارگذاری شد. اکنون شما وارد سیستم شده‌اید.';
$string['uploadsuccess_login'] = 'مدارک شما با موفقیت بارگذاری شد. اکنون می‌توانید به داشبورد خود بروید.';
$string['uploadsuccess_paynext'] = 'مرحله بعدی شما پرداخت هزینه‌های مورد نیاز است. لطفاً برای دستورالعمل‌های پرداخت با دفتر مدیریت تماس بگیرید.';
$string['payment_required_subject'] = 'اقدام لازم: پرداخت اولیه برای ثبت‌نام شما در {$a}';
$string['payment_required_body_html'] = '<p>کاربر گرامی {$a->studentname}،</p>
<p>تبریک می‌گوییم! حساب شما برای <strong>{$a->schoolname}</strong> توسط مدیریت ما تأیید شده است.</p>
<p>مرحله نهایی برای فعال‌سازی ثبت‌نام شما، تکمیل پرداخت اولیه است که شامل هزینه پذیرش و اولین شهریه ماهانه شما می‌شود. مبلغ کل <strong>{$a->totalfee}</strong> است.</p>
<p style="text-align: center; margin: 30px 0;">
    <a href="{$a->paymentlink}" style="background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-size: 16px; display: inline-block;">انتقال به صفحه پرداخت</a>
</p>
<p>پس از تأیید پرداخت شما، ایمیل دیگری با لینکی برای بارگذاری مدارک مورد نیاز دریافت خواهید کرد.</p>
<p>اگر سؤالی دارید، لطفاً با دفتر پذیرش ما تماس بگیرید.</p>
<p>با احترام،<br><strong>{$a->schoolname}</strong></p>';
$string['payment_required_body_text'] = '... (نسخه متنی ساده) ...';
$string['paymentconfirmation'] = 'تأیید پرداخت';
$string['paymentstatus'] = 'وضعیت پرداخت';
$string['paymentconfirm_subject'] = 'پرداخت تأیید شد: مراحل بعدی برای ثبت‌نام شما در {$a}';
$string['paymentconfirm_body_html'] = '<p>کاربر گرامی {$a->studentname}،</p>
<p>خوشحالیم تأیید کنیم که پرداخت اولیه شما با موفقیت دریافت شد. به <strong>{$a->schoolname}</strong> خوش آمدید!</p>
<p>مرحله نهایی، بارگذاری مدارک ثبت‌نام مورد نیاز شما است. لطفاً برای ادامه روی لینک زیر کلیک کنید:</p>
<p style="text-align: center; margin: 30px 0;">
    <a href="{$a->uploadlink}" style="background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-size: 16px; display: inline-block;">بارگذاری مدارک</a>
</p>
<p>این لینک منحصر به فرد شماست. لطفاً برای نهایی کردن ثبت‌نام خود، بارگذاری مدارک را در اولین فرصت انجام دهید.</p>
<p>با احترام،<br><strong>{$a->schoolname}</strong></p>';
$string['paymentconfirm_body_text'] = '... (نسخه متنی ساده) ...';
$string['paymentconfirm_success_msg'] = 'متشکریم! پرداخت شما تأیید شد. به زودی ایمیلی با لینکی برای بارگذاری مدارک خود دریافت خواهید کرد. همچنین می‌توانید با استفاده از دکمه زیر ادامه دهید.';
$string['retrypayment'] = 'تلاش مجدد برای پرداخت';
$string['proceedtopayment'] = 'انتقال به صفحه پرداخت';
$string['auth_emailadminuserconfirmationsubject_payment'] = '{$a}: حساب شما تأیید شد - مراحل بعدی';
$string['auth_emailadminuserconfirmationbody_payment'] = 'سلام {$a->firstname}،

یک حساب کاربری جدید برای شما در \'{$a->sitename}\' ایجاد شده است. حساب شما اکنون تأیید شده است.

لطفاً برای نهایی کردن ثبت‌نام خود، مراحل زیر را تکمیل کنید:

۱. بارگذاری مدارک
لطفاً از لینک زیر برای دسترسی به صفحه بارگذاری استفاده کنید:
{$a->link}

۲. ارسال پرداخت
{$a->paymentlink}

در اکثر برنامه‌های ایمیل، این لینک‌ها باید به صورت لینک‌های آبی قابل کلیک ظاهر شوند. اگر این‌طور نیست، آدرس را کپی کرده و در نوار آدرس مرورگر وب خود جای‌گذاری کنید.

اگر به کمک نیاز دارید، لطفاً با مدیر سایت تماس بگیرید.

با احترام،
دفتر پذیرش';

$string['checksubscriptions'] = 'بررسی اشتراک‌ها';
$string['email_reminder_subject'] = 'اشتراک شما به زودی منقضی می‌شود';
$string['email_reminder_body'] = '
<div style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5;">
    <p>کاربر گرامی {$a->firstname}،</p>
    <p>این یک یادآوری است که ثبت‌نام شما در برنامه "{$a->programname}" در {$a->sitename} تقریباً تا ۷ روز دیگر منقضی می‌شود.</p>
    <p>برای جلوگیری از لغو ثبت‌نام، لطفاً با کلیک بر روی دکمه زیر، پرداخت خود را برای ماه آینده تکمیل کنید.</p>
    <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;" width="100%">
        <tbody>
            <tr>
                <td align="center" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;" valign="top">
                    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                        <tbody>
                            <tr>
                                <td style="font-family: sans-serif; font-size: 14px; vertical-align: top; border-radius: 5px; text-align: center; background-color: #3498db;" valign="top" align="center" bgcolor="#3498db">
                                    <a href="{$a->paymentlink}" target="_blank" style="display: inline-block; color: #ffffff; background-color: #3498db; border: solid 1px #3498db; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #3498db;">پرداخت شهریه ماهانه</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <p>اگر سؤالی دارید، لطفاً با دفتر مدیریت ما تماس بگیرید.</p>
    <p>متشکریم،<br>تیم {$a->sitename}</p>
</div>
';

$string['privacy:metadata'] = 'افزونه مدیریت پذیرش (ایمیل) هیچ داده شخصی را ذخیره نمی‌کند، اما در طول فرآیند ایجاد حساب، آن را پردازش می‌کند.';
$string['auth_emailadminbasicnotification'] = '
<p>سلام،</p>
<p>یک درخواست پذیرش جدید توسط {$a->email} ارسال شده است.</p>
<p>برای مشاهده جزئیات کامل درخواست، از جمله کلاس انتخابی، و برای اتخاذ تصمیم، لطفاً از لینک زیر استفاده کنید:</p>
<p> </p>
<p><a href="{$a->link}" style="color: #007bff; text-decoration: none;"><strong>بررسی درخواست اکنون</strong></a></p>';
$string['auth_emailadminparentnotificationsubject'] = 'حساب کاربری والدین شما در HiMaktab ایجاد شد';
$string['auth_emailadminparentnotificationbody'] = '
<h3 style="font-family: sans-serif; text-align: center;">اطلاع‌رسانی به والدین و ایجاد حساب</h3>
<p style="font-family: sans-serif;">والد گرامی {$a->parentname}،</p>
<p style="font-family: sans-serif;">خوشحالیم به اطلاع برسانیم که {$a->studentname} با موفقیت در کلاس {$a->grade} در HiMaktab ثبت‌نام شد.</p>

<h4 style="font-family: sans-serif;">تأیید ثبت‌نام:</h4>
<ul style="font-family: sans-serif;">
    <li><strong>نام دانش‌آموز:</strong> {$a->studentname}</li>
    <li><strong>کلاس:</strong> {$a->grade}</li>
    <li><strong>تاریخ شروع:</strong> {$a->startdate}</li>
</ul>

<h4 style="font-family: sans-serif;">دسترسی شما به پورتال والدین:</h4>
<p style="font-family: sans-serif;">اکنون می‌توانید برای نظارت بر پیشرفت تحصیلی، حضور و غیاب و ارتباطات به پورتال والدین دسترسی داشته باشید.</p>

<h4 style="font-family: sans-serif;">اطلاعات ورود:</h4>
<ul style="font-family: sans-serif;">
    <li><strong>نام کاربری:</strong> {$a->username}</li>
    <li><strong>رمز عبور:</strong> {$a->password}</li>
</ul>
<p style="font-family: sans-serif;">برای امنیت، در اولین ورود از شما خواسته می‌شود رمز عبور خود را تغییر دهید.</p>
<p style="font-family: sans-serif;"><strong>لینک پورتال:</strong> <a href="{$a->link}">{$a->link}</a></p>

<p style="font-family: sans-serif;">در صورت داشتن هرگونه سوال یا نیاز به پشتیبانی، با ما تماس بگیرید.</p>
<p style="font-family: sans-serif;">
    با احترام،<br>
    دفتر پذیرش<br>
    HiMaktab
</p>';
$string['auth_emailadminuserconfirmationsubject_upload'] = '{$a}: حساب کاربری تأیید شد - لطفاً مدارک خود را بارگذاری کنید';
$string['auth_emailadminuserconfirmationbody_upload'] = '
سلام {$a->firstname}،

خبر خوب! حساب جدیدی برای شما در "{$a->sitename}" ایجاد و توسط تیم مدیریت ما تأیید شده است.

برای تکمیل ثبت‌نام و دسترسی به سایت، لطفاً از لینک زیر برای بارگذاری مدارک مورد نیاز خود استفاده کنید. پس از بارگذاری موفق، به طور خودکار وارد سیستم خواهید شد.

{$a->link}

در اکثر برنامه‌های ایمیل، این لینک باید به صورت یک لینک آبی قابل کلیک نمایش داده شود. اگر اینطور نیست، آدرس را کپی کرده و در نوار آدرس مرورگر خود وارد کنید.

در صورت نیاز به کمک، لطفاً با مدیر سایت تماس بگیرید.
دفتر پذیرش';
$string['parent_notification_subject'] = 'اطلاعیه والدین و ایجاد حساب برای {$a->studentname}';
$string['parent_notification_html'] = '
<div style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5;">
    <h2 style="font-size: 18px; color: #000; border-bottom: 1px solid #ddd; padding-bottom: 10px;">اطلاعیه والدین و ایجاد حساب</h2>
    <p>والد گرامی {$a->parentname}،</p>
    <p>خوشحالیم به اطلاع برسانیم که <strong>{$a->studentname}</strong> با موفقیت در کلاس <strong>{$a->grade}</strong> در {$a->schoolname} ثبت‌نام شد.</p>
    <h3 style="font-size: 16px; margin-top: 25px; color: #000;">تأیید ثبت‌نام:</h3>
    <div style="padding-left: 15px; border-left: 3px solid #eee;">
        <p style="margin: 5px 0;"><strong>نام دانش‌آموز:</strong> {$a->studentname}</p>
        <p style="margin: 5px 0;"><strong>کلاس:</strong> {$a->grade}</p>
        <p style="margin: 5px 0;"><strong>تاریخ شروع:</strong> {$a->startdate}</p>
    </div>
    <h3 style="font-size: 16px; margin-top: 25px; color: #000;">دسترسی شما به پورتال والدین:</h3>
    <p>اکنون می‌توانید برای نظارت بر پیشرفت تحصیلی، حضور و غیاب و ارتباطات به پورتال والدین دسترسی داشته باشید.</p>
    <h4 style="font-size: 15px; margin-top: 20px;">اطلاعات ورود:</h4>
    <div style="padding-left: 15px; border-left: 3px solid #eee;">
        <p style="margin: 5px 0;"><strong>نام کاربری:</strong> {$a->username}</p>
        <p style="margin: 5px 0;"><strong>رمز عبور:</strong> {$a->password}</p>
    </div>
    <p>برای امنیت، در اولین ورود از شما خواسته می‌شود که رمز عبور خود را تغییر دهید.</p>
    <p style="margin-top: 30px;">در صورت داشتن هرگونه سوال یا نیاز به پشتیبانی، با ما تماس بگیرید.</p>
    <p>با احترام،<br>{$a->office}<br>{$a->schoolname}</p>
</div>';