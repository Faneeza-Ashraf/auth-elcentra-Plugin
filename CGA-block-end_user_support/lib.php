<?php
/**
 * Created by PhpStorm.
 * User: syedmahtab
 * Date: 5/7/2019
 * Time: 7:38 AM
 */
require_once(__DIR__.'/../../config.php');
function block_end_user_support_modal() {
    global $OUTPUT, $USER, $PAGE, $CFG;

    $templatecontext = [
        'wwwroot' => $CFG->wwwroot,
        'pageurl' => $PAGE->url,
        'loggedin' => isloggedin(),
        'fullname' => fullname($USER),
        'email' => $USER->email
    ];

    return $OUTPUT->render_from_template('block_end_user_support/modal', $templatecontext);
}


function end_user_support_displaylinks($data){
    global $USER, $CFG, $COURSE;
    $output = '';
    $context = context_course::instance($COURSE->id);
    $output .= html_writer::tag('p', $data['description']);
    $output .= '<hr>';
    if(isloggedin() && !is_guest($context)){
            $output .= html_writer::tag('span', 'Name: ', array('style' =>'font-weight: bold;'));
            $output .= html_writer::tag('a', fullname($USER), array('href' => $CFG->wwwroot . '/user/profile.php?id=' . $USER->id));
            $output .= html_writer::tag('span', '<b>Email:</b> '.$data['email'], array('style' => 'float: right;'));
    }else{
        $output .= html_writer::tag('span', 'Name: ', array('style' => 'font-weight: bold;'));
        $output .= html_writer::tag('span', $data['username']);
        $output .= html_writer::tag('span', '<b>Email:</b> '.$data['email'], array('style' => 'float: right;'));
    }
    $output .= '<br>';
    $output .= html_writer::tag('p', '<b>Submitted From:</b> '.$data['url']);
    if(isloggedin() && !is_guest($context)) {
        $table = new html_table();
        $table->id = 'user_support';
        $table->head = array(get_string('enrolled_courses', 'block_end_user_support'), get_string('course_grades', 'block_end_user_support'));
        $table->align = array('left', 'center');
        $table->data = array();

        // user course link
        $mycourses = enrol_get_all_users_courses($USER->id, true, null, 'visible DESC, sortorder ASC');
        //print_object($mycourses);
        foreach ($mycourses as $course) {
            $row = array();
            $row[] = html_writer::tag('a', $course->fullname, array('href' => $CFG->wwwroot . '/course/view.php?id=' . $course->id));
            $row[] = html_writer::tag('a', get_string('course_grades', 'block_end_user_support'), array('href' => new moodle_url('/course/user.php', array('id' => $course->id, 'user' => $USER->id, 'mode' => 'grade'))));
            $table->data[] = $row;
        }

        $output .= html_writer::table($table);
    }

    return $output;

}