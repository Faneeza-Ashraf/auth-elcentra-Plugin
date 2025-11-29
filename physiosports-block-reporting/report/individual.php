<?php

/**
 * Individual Report for the Reporting Block.
 *
 * This script generates a detailed report for a single user.
 * It has been fully upgraded to Moodle 4.x standards, removing PDO,
 * using parameterized queries, and following Moodle API conventions.
 */

// Moodle bootstrap.
require_once('../../../config.php');

// Custom block libraries.
require_once($CFG->dirroot .'/blocks/reporting/report/smarty/Smarty.class.php');
require_once('lib.php');

// Moodle globals.
global $USER, $DB, $PAGE, $SITE, $OUTPUT, $CFG;

// --- Page Setup ---
require_login(0, false);
$context_system = context_system::instance();
$PAGE->set_context($context_system);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_url('/blocks/reporting/report/individual.php');
$PAGE->navbar->add("Reporting", new moodle_url('/blocks/reporting/report/general.php'));
$PAGE->navbar->add("Individual Report");

// --- Page Requirements (JS/CSS) ---
$PAGE->requires->js('/lib/mindatlas/jquery/jquery.min.js', true);
$PAGE->requires->js('/lib/mindatlas/jquery/ui/jquery-ui.min.js', true);
$PAGE->requires->css('/lib/mindatlas/jquery/ui/jquery-ui.min.css');
$PAGE->requires->js('/blocks/reporting/report/js/chosen.js', true);
$PAGE->requires->js('/blocks/reporting/report/js/jquery.tablesorter.js', true);
$PAGE->requires->js('/blocks/reporting/report/resource/chosen.jquery.js', true);
$PAGE->requires->js('/blocks/reporting/report/js/Chartjs/Chart.js', true);
$PAGE->requires->css('/blocks/reporting/report/css/chosen.css');


// --- Get Parameters ---
$report      = optional_param('report', false, PARAM_BOOL);
$uid         = optional_param('uid', 0, PARAM_INT);
$report_type = optional_param('type', 'HTML', PARAM_ALPHA);


// --- Custom Block Functions & Setup ---
remove_deleted_fields();
$hierarchy = is_user_in_hierarchy($USER->id);
$has_capability = has_capability('block/reporting:viewreports', $context_system);

// --- Smarty Setup ---
$smarty = new Smarty;
$smarty->compile_dir = get_reporting_compile_folder();
$smarty->template_dir = realpath(".") . '/template' ;


// --- Display Filter Form if not generating a report ---
if (!$report) {
    if (!$has_capability && !is_vendor($USER->id) && !$hierarchy) {
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('notallowtoaccess', 'block_reporting'));
        echo $OUTPUT->footer();
        exit(0);
    }

    $list_user_options = get_individual_user_options($USER->id, $hierarchy, $has_capability, is_vendor($USER->id));
    $smarty->assign('user_fullname_options', $list_user_options);
    
    // Assign language strings for the modern template
    $smarty->assign('str_heading', get_string('individual_reports', 'block_reporting'));
    $smarty->assign('str_fullname', get_string('fullname'));
    $smarty->assign('str_display_type', get_string('display_type', 'block_reporting'));
    $smarty->assign('str_go', get_string('go', 'block_reporting'));
    
    // PDF option is handled in the template, assuming `lib.php` has the get_report_pdf_functionality_enable function
    // For safety, we add the variable here to prevent errors.
    if (function_exists('get_report_pdf_functionality_enable')) {
        $smarty->assign('report_pdf', get_report_pdf_functionality_enable());
    } else {
        $smarty->assign('report_pdf', 0); // Default to disabled if function doesn't exist
    }

    echo $OUTPUT->header();
    $smarty->display('interface_individual.html.tpl');
    echo $OUTPUT->footer();
    exit(0);
}

// ========================================================================================
// --- REPORT GENERATION LOGIC ---
// ========================================================================================

if ($report_type == 'HTML') {
    echo $OUTPUT->header();
}

// Validate that a user was selected.
if (empty($uid)) {
    echo $OUTPUT->notification(get_string('error:selectusername', 'block_reporting'));
    echo html_writer::link(new moodle_url('/blocks/reporting/report/individual.php'), get_string('back'), ['class' => 'btn btn-primary']);
    echo $OUTPUT->footer();
    exit;
}

// --- Build SQL Query Conditions ---
$sql_params = ['userid' => $uid];
$sql_conditions = ["u.id = :userid"];

// Apply security restrictions.
if (!$has_capability) {
    if (is_vendor($USER->id)) {
        $vendor_course_ids = get_vendor_course_ids($USER->id);
        if (empty($vendor_course_ids)) {
            $sql_conditions[] = "1 = 0"; // No courses, no results.
        } else {
            list($in_sql, $in_params) = $DB->get_in_or_equal($vendor_course_ids);
            $sql_conditions[] = "c.id $in_sql";
            $sql_params += $in_params;
        }
    } else if ($hierarchy) {
        $current_node_id = $DB->get_field('hierarchy_user', 'node_id', ['user_id' => $USER->id]);
        $all_users_under_thisuser = get_all_users_from_nodes($current_node_id);
        $user_ids_array = explode(',', $all_users_under_thisuser);
        if (!in_array($uid, $user_ids_array)) {
            echo $OUTPUT->notification(get_string('noresult', 'block_reporting'));
            echo html_writer::link(new moodle_url('/blocks/reporting/report/individual.php'), get_string('back'), ['class' => 'btn btn-primary']);
            echo $OUTPUT->footer();
            exit();
        }
    }
}

// --- Get Dynamic and General Filters Info (for table headers) ---
$filters_array = [];
$filter_records = $DB->get_records_sql('SELECT rf.*, uif.name, uif.shortname, uif.datatype from {reporting_filter} rf JOIN {user_info_field} uif ON rf.user_info_field_id=uif.id ORDER BY uif.sortorder ASC');
if ($filter_records) {
    foreach ($filter_records as $record) {
        $filters_array[$record->shortname] = $record;
    }
}

$array_dynamic_query = get_reporting_filter_query(true); // Get definitions only
$array_plugin = get_plugin_installed();
$hierarchy_query = $hierarchy ? get_hierarchy_query($uid, true) : ['fields' => '', 'table' => ''];

$wheres = "WHERE " . implode(' AND ', $sql_conditions);
$query = "
    SELECT CONCAT(u.id, '-', cm.id) AS uniqueid,
           u.id AS userid, u.firstname, u.lastname, cm.id AS coursemoduleid, e.courseid, c.fullname AS coursename,
           m.name AS module, cm.completion, cmc.completionstate AS completionstatus, ue.timecreated AS enrolleddate,
           cmc.timemodified AS completiondate, cm.instance AS instance,
           scormtrack.value as scormstatus
           {$array_plugin['fields']} {$array_dynamic_query['fields']} {$hierarchy_query['fields']}
      FROM {user} u
      JOIN {user_enrolments} ue ON u.id = ue.userid
      JOIN {enrol} e ON e.id = ue.enrolid
      JOIN {course} c ON c.id = e.courseid
      LEFT JOIN {course_modules} cm ON c.id = cm.course
      LEFT JOIN {modules} m ON m.id = cm.module
      LEFT JOIN {course_modules_completion} cmc ON (cmc.userid = u.id AND cmc.coursemoduleid = cm.id)
      LEFT JOIN (
          SELECT sst.userid, sst.scormid, sst.value
          FROM {scorm_scoes_track} sst
          WHERE sst.id IN (
              SELECT MAX(t.id)
              FROM {scorm_scoes_track} t
              WHERE t.element = 'cmi.core.lesson_status'
              GROUP BY t.userid, t.scormid, t.attempt
          )
      ) scormtrack ON u.id = scormtrack.userid AND cm.module = (SELECT id FROM {modules} WHERE name = 'scorm') AND cm.instance = scormtrack.scormid
      {$array_dynamic_query['table']} {$array_plugin['tables']} {$hierarchy_query['table']}
      $wheres
  ORDER BY c.fullname, u.firstname, u.lastname, u.id, m.name
";

$rows = $DB->get_records_sql($query, $sql_params);

if (empty($rows)) {
    echo $OUTPUT->notification(get_string('noresult', 'block_reporting'));
    if ($report_type == 'HTML') {
        echo $OUTPUT->footer();
    }
    exit;
}

// --- Process Results ---
$userinfo_row = [];
$num_completed = 0;
$num_activities = 0;

foreach ($rows as $row) {
    if (empty($row->completion)) {
        continue;
    }
    $num_activities++;

    $uid_key = $row->userid;
    $cmid_key = $row->coursemoduleid;

    if (isset($userinfo_row[$uid_key][$cmid_key])) continue;

    $userinfo_row[$uid_key][$cmid_key] = [
        "firstname" => $row->firstname,
        "lastname" => $row->lastname,
        "enrolleddate" => $row->enrolleddate,
        "coursemoduleid" => $row->coursemoduleid,
        "coursename" => $row->coursename,
        "moduletype" => $row->module,
        "courseid" => $row->courseid,
        "completiondate" => ($row->completionstatus == 1 || $row->completionstatus == 2) ? $row->completiondate : "",
        "scormstatus" => $row->scormstatus,
        "completionstatus" => $row->completionstatus,
        "modulename" => getModulename($row->module, $row->courseid, $cmid_key, $row->instance),
        "profile_result" => []
    ];

    if ($hierarchy) {
        $userinfo_row[$uid_key][$cmid_key]["node_name"] = $row->node_name ?? null;
        $userinfo_row[$uid_key][$cmid_key]["leveldescription"] = $row->leveldescription ?? null;
        $userinfo_row[$uid_key][$cmid_key]["nodedescription"] = $row->nodedescription ?? null;
    }

    foreach ($filters_array as $key => $val) {
        $fieldvalue = $row->{$key} ?? null;
        if ($val->datatype == "checkbox") $fieldvalue = $fieldvalue ? 'Yes' : 'No';
        $userinfo_row[$uid_key][$cmid_key]["profile_result"][$key] = ['type' => $val->datatype, 'value' => $fieldvalue];
    }
    
    if ($row->completionstatus == 1 || $row->completionstatus == 2 || $row->scormstatus == "passed" || $row->scormstatus == "completed") {
        $num_completed++;
    }
}

// --- Calculate Pie Chart Data ---
if ($num_activities == 0) {
    $percentage_true = 0;
} else {
    $percentage_true = ($num_completed / $num_activities) * 100;
}
$total_overall_diagram_value = [
    'true' => number_format($percentage_true, 2, '.', ''),
    'false' => number_format(100 - $percentage_true, 2, '.', '')
];

$pie_colors = get_default_colors(); // Assuming this returns an array of 4 colors.

// --- Assign to Smarty ---
$smarty->assign('userinfo_row', $userinfo_row);
$smarty->assign('filters_array', $filters_array);
$smarty->assign('total_overall_diagram_value_true', $total_overall_diagram_value['true']);
$smarty->assign('total_overall_diagram_value_false', $total_overall_diagram_value['false']);
$smarty->assign('pie_color_completed', $pie_colors[0]);
$smarty->assign('pie_color_not_completed', $pie_colors[1]);
$smarty->assign('pie_highlightcolor_completed', $pie_colors[2]);
$smarty->assign('pie_highlightcolor_not_completed', $pie_colors[3]);

// --- Render Output ---
if ($report_type == 'CSV') {
    header("Content-type: application/csv");
    header("Content-Disposition: attachment; filename=individual_report.csv");
    header("Pragma: no-cache");
    header("Expires: 0");
    $csv_tpl = $hierarchy ? 'report_hierarchy.csv.tpl' : 'report.csv.tpl';
    $smarty->display($csv_tpl);
} else { // Default to HTML
    $html_tpl = $hierarchy ? 'report_individual_hierarchy.html.tpl' : 'report_individual.html.tpl';
    $smarty->display($html_tpl);
    echo $OUTPUT->footer();
}