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
 * @package    local
 * @subpackage signup
 *@copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'فرم ثبت‌نام سفارشی';
$string['signuppage'] = 'ایجاد حساب کاربری جدید';
$string['accountdetails'] = 'جزئیات حساب';
$string['personalinformation'] = 'اطلاعات شخصی';
$string['contactinformation'] = 'اطلاعات تماس';
$string['healthinformation'] = 'اطلاعات سلامتی';
$string['additionalinformation'] = 'اطلاعات اضافی';
$string['programselection'] = 'انتخاب برنامه';
$string['applicationdetails'] = 'اطلاعات سفارشی متقاضی';
$string['studentsfirstname'] = 'نام دانش‌آموز';
$string['studentslastname'] = 'نام خانوادگی دانش‌آموز';
$string['dateofbirth'] = 'تاریخ تولد';
$string['homeaddress'] = 'آدرس منزل';
$string['parentname'] = 'نام کامل والد/سرپرست';
$string['emergencycontactname'] = 'نام فرد تماس اضطراری';
$string['emergencyphone'] = 'تلفن تماس اضطراری';
$string['parentemail'] = 'ایمیل والد/سرپرست';
$string['phone2'] = 'شماره تلفن جایگزین';
$string['healthinfo_details'] = 'لطفاً جزئیات هرگونه شرایط پزشکی، آلرژی یا داروها را وارد کنید.';
$string['specialneeds_details'] = 'لطفاً هرگونه نیاز آموزشی ویژه، مشکلات یادگیری یا سایر نیازها را توضیح دهید.';
$string['desiredgrade'] = 'پایه تحصیلی مورد نظر';
$string['createaccount'] = 'ایجاد حساب کاربری جدید من';
$string['select'] = 'انتخاب...';
$string['username_exists'] = 'این نام کاربری قبلاً گرفته شده است. لطفاً یکی دیگر انتخاب کنید.';
$string['email_exists'] = 'این آدرس ایمیل قبلاً ثبت شده است.';
$string['errorcreatinguser'] = 'خطای سرور رخ داده است و کاربر ایجاد نشد.';
