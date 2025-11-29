<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('tablechecker_report');

require_capability('local/tablechecker:viewreport', context_system::instance());

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_tablechecker'));

$sql = "SELECT lts.id, c.fullname AS coursename, cm.id AS cmid, l.name AS activityname, lts.status, lts.timechecked, lts.timemodified
        FROM {local_tablechecker_status} lts
        JOIN {course_modules} cm ON lts.cmid = cm.id
        JOIN {course} c ON cm.course = c.id
        JOIN {label} l ON cm.instance = l.id
        ORDER BY c.fullname, l.name";

$results = $DB->get_records_sql($sql);

$table = new html_table();
$table->head = [
    get_string('coursename', 'local_tablechecker'),
    get_string('activityname', 'local_tablechecker'),
    get_string('status', 'local_tablechecker'),
    get_string('lastupdated', 'local_tablechecker'), // New column header
    get_string('lastchecked', 'local_tablechecker'),
];

foreach ($results as $result) {
    $row = [
        html_writer::link(new moodle_url('/course/view.php', ['id' => $DB->get_field('course_modules', 'course', ['id' => $result->cmid])]), $result->coursename),
        html_writer::link(new moodle_url('/mod/label/view.php', ['id' => $result->cmid]), $result->activityname),
        get_string($result->status, 'local_tablechecker'),
        $result->timemodified ? userdate($result->timemodified) : '-', // Display the last updated time
        userdate($result->timechecked),
    ];
    $table->data[] = $row;
}

echo html_writer::table($table);

echo $OUTPUT->footer();