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
 * The simple courses block class.
 *
 * Used to produce a master-detail view of a user's enrolled courses and their activities.
 *
 * @package   local_cart
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
global $CFG, $DB, $OUTPUT, $PAGE, $USER;

require_login();

require_once($CFG->dirroot . '/enrol/manual/lib.php');


$url = new moodle_url('/local/cart/coupon.php');
$PAGE->set_context(context_system::instance()); 
$PAGE->set_url($url);
$PAGE->set_title(get_string('redeemyourcoupon', 'local_cart'));
$PAGE->set_heading(get_string('redeemyourcoupon', 'local_cart'));
$PAGE->set_pagelayout('standard'); 

$template_data = [
    'show_success_box' => false,// Added by Endush Fairy
    'title' => get_string('redeemyourcoupon', 'local_cart'),
    'haveacoursecoupon' => get_string('haveacoursecoupon', 'local_cart'),
    'couponcode_label' => get_string('couponcode', 'local_cart'),
    'redeem_button' => get_string('redeem', 'local_cart'),
    'coupondescription' => get_string('coupondescription', 'local_cart'),
    'sesskey' => sesskey(),
    'message' => '',
    'errormessage' => '',
];

if (data_submitted()) {
    try {
        require_sesskey();
        $couponcode_input = trim(required_param('couponcode', PARAM_TEXT));        
        $coupon = $DB->get_record('coupon', ['couponcode' => $couponcode_input]);
        if (!$coupon) {
            // FIXED: Passed the coupon code as the 4th parameter to fill {$a}
            throw new moodle_exception('invalidcouponcode', 'local_cart', '', $couponcode_input);
        }        
        if ($coupon->status == 1) { 
            // FIXED: Passed the coupon code as the 4th parameter to fill {$a}
            throw new moodle_exception('couponalreadyredeemed', 'local_cart', '', $couponcode_input);
        }
        if ($coupon->status != 0) {
            // FIXED: Passed the coupon code as the 4th parameter to fill {$a}
            throw new moodle_exception('couponnotactive', 'local_cart', '', $couponcode_input);
        }        
        $course = $DB->get_record('course', ['id' => $coupon->courseid], '*', MUST_EXIST);
        $context = context_course::instance($course->id);
        if (is_enrolled($context, $USER)) {
            // FIXED: Passed the course name as the 4th parameter to fill {$a}
            throw new moodle_exception('useralreadyenrolled', 'local_cart', '', format_string($course->fullname));
        }        
        $enrolmanual = enrol_get_plugin('manual');
        if (!$enrolmanual) {
            throw new moodle_exception('enrolmanualnotfound', 'local_cart');
        }        
        $roleid = $DB->get_field('role', 'id', ['archetype' => 'student'], MUST_EXIST);
        $instance = $DB->get_record('enrol', ['enrol' => 'manual', 'courseid' => $course->id], '*', MUST_EXIST);        
        $enrolmanual->enrol_user($instance, $USER->id, $roleid, time(), 0);        
        $coupon->status = 1; 
        $coupon->userid = $USER->id; 
        $coupon->timemodified = time();        
        $DB->update_record('coupon', $coupon);
        //redirect(new moodle_url('/local/cart/coupon.php', ['message' => get_string('couponredeemedsuccessfully', 'local_cart', $couponcode_input)]));
        $template_data['show_success_box'] = true;
        $template_data['coursename'] = $course->fullname;
        $template_data['courseurl'] = (new moodle_url('/course/view.php', ['id' => $course->id]))->out();
        $template_data['backurl'] = $url->out(); // Updated by Endush Fairy

    } catch (moodle_exception $e) {
        $template_data['errormessage'] = $e->getMessage();
    }
}

/*if ($message = optional_param('message', '', PARAM_RAW)) {
    $template_data['message'] = $message;
}
if (!empty($template_data['message'])) {
    echo $OUTPUT->notification($template_data['message'], 'notifysuccess');
} */ //Updated by Endush Fairy

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_cart/coupon_redeem', $template_data);
echo $OUTPUT->footer();