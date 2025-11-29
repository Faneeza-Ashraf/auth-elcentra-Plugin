<?php

/**
 * General Report for the Reporting Block.
 *
 * This script provides a filterable report on course and user activity.
 * It has been updated to use Moodle 4.x APIs, including the standard
 * database layer for security and compatibility. All logic has been moved
 * out of the template's {php} tags and into this file.
 */

// Moodle bootstrap.
require_once('../../../config.php');

// The wkhtmltopdf library is a third-party dependency.
require_once('lib_pdf.php');
use mikehaertl\tmp\File;
use mikehaertl\wkhtmlto\Pdf;

// Custom block libraries.
require_once($CFG->dirroot .'/blocks/reporting/report/smarty/Smarty.class.php');
require_once('lib.php');

// Get Moodle globals.
global $USER, $DB, $PAGE, $SITE, $OUTPUT, $CFG;

// --- Page Setup ---
require_login(0, false);

$context_system = context_system::instance();
$PAGE->set_context($context_system);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_url('/blocks/reporting/report/general.php');
$PAGE->navbar->add("Reporting", new moodle_url('/blocks/reporting/report/general.php'));

// --- Page Requirements (JS/CSS) ---
$PAGE->requires->js('/lib/mindatlas/jquery/jquery.min.js', true);
$PAGE->requires->js('/lib/mindatlas/jquery/ui/jquery-ui.min.js', true);
$PAGE->requires->css('/lib/mindatlas/jquery/ui/jquery-ui.min.css');
$PAGE->requires->js('/blocks/reporting/report/js/jquery.tablesorter.js', true);
$PAGE->requires->js('/blocks/reporting/report/js/jstree/dist/jstree.min.js', true);
$PAGE->requires->js('/blocks/reporting/report/resource/chosen.jquery.js', true);
$PAGE->requires->css('/blocks/reporting/report/css/chosen.css');
$PAGE->requires->css('/blocks/reporting/report/js/jstree/dist/themes/default/style.min.css');


// --- Get Parameters ---
$report = optional_param('report', false, PARAM_BOOL);
$mine = optional_param('mine', false, PARAM_BOOL); // Assuming 'mine' is a flag.
$report_type = optional_param('type', 'HTML', PARAM_ALPHA);

// Get form values only if a report is being generated.
if ($report && !$mine) {
    $course = optional_param_array('course', [], PARAM_RAW);
    $completionstatus = optional_param('completionstatus', '', PARAM_INT);
    $suspendedusers = optional_param('suspendedusers', 'all', PARAM_ALPHA);
    $enrolleddate_from = optional_param('enrolleddate_from', '', PARAM_RAW); // Raw, will be converted to timestamp.
    $enrolleddate_to = optional_param('enrolleddate_to', '', PARAM_RAW);
    $completiondate_from = optional_param('completiondate_from', '', PARAM_RAW);
    $completiondate_to = optional_param('completiondate_to', '', PARAM_RAW);
}

// --- Custom Block Functions ---
$PDF_ENABLE = get_report_pdf_functionality_enable();
remove_deleted_fields();
$hierarchy = is_hierarchy_installed();

// --- Smarty Setup ---
$smarty = new Smarty;
$path = realpath(".");
$smarty->compile_dir = get_reporting_compile_folder();
$smarty->template_dir = $path . '/template' ;

// --- Display Filter Form OR Generate Report ---

if (!$report) {
    // --- Display the filter interface ---
    echo $OUTPUT->header();

    if ($hierarchy) {
        $courses = getCourses_Category_User();
    } else {
        $courses = getCourses_Category();
    }
    $user_profile_filters_array = get_reporting_filter_array($hierarchy);
    $general_filters_array = get_general_filter_array();
    $datepicker_fields = get_reporting_date_picker_script();

    $smarty->assign('datepicker_fields', $datepicker_fields);
    if (!empty($general_filters_array)) $smarty->assign('general_filters_array', $general_filters_array);
    if (!empty($user_profile_filters_array)) $smarty->assign('user_profile_filters_array', $user_profile_filters_array);

    if ($PDF_ENABLE) $smarty->assign('report_pdf', $PDF_ENABLE);

    $smarty->assign('courses', $courses);

    if ($hierarchy) {
        // If hierarchy plugin exists, show hierarchy-specific interface.
        $userid = $USER->id;
        $hierarchy_nodes = get_hierarchy_tree($userid);
        $root_node_id = get_root_hierarchy($userid);
        $smarty->assign('hierarchy_nodes', $hierarchy_nodes);
        $smarty->assign('root_node_id', $root_node_id);
        $smarty->display('interface_general_hierarchy.html.tpl');
    } else {
        // Show the general report interface.
        $smarty->display('interface_general.html.tpl');
    }

    echo $OUTPUT->footer();
    exit(0);
}

// ========================================================================================
// --- REPORT GENERATION LOGIC ---
// ========================================================================================

raise_memory_limit(MEMORY_HUGE);

// --- Build SQL Query Conditions ---
$sql_params = [];
$sql_conditions = [];

// Handle suspended user filter.
switch ($suspendedusers) {
    case 'none':
        $sql_conditions[] = "u.suspended = 0";
        break;
    case 'only':
        $sql_conditions[] = "u.suspended = 1";
        break;
    // 'all' or default case does nothing.
}

// Handle hierarchy filter.
$hierarchy_query = array("fields" => '', 'table' => '', 'where' => '');
if ($hierarchy) {
    $selectednodes = optional_param_array('selectednodes', [], PARAM_INT);
    $list_hierarchy_users = get_all_users_from_nodes($selectednodes);
    if (empty($list_hierarchy_users)) {
        // No users in selected hierarchy, so no results.
        echo $OUTPUT->header();
        echo get_string('noresult', 'block_reporting');
        echo $OUTPUT->footer();
        exit(0);
    }
    $hierarchy_query = get_hierarchy_query($list_hierarchy_users);
    if (!empty($hierarchy_query['where'])) {
        $sql_conditions[] = substr(trim($hierarchy_query['where']), 4); // Remove "AND "
    }
}

// Handle Course/Category filter.
$coursename = "";
if (!empty($course)) {
    $category_arr = [];
    $course_arr = [];
    $course_name_arr = [];
    $category_name_arr = [];
    $course_category_conditions = [];
    $course_cat_names = [];

    foreach ($course as $value) {
        if (strpos($value, 'category') === false) {
            $course_arr[] = (int)$value;
        } else {
            $selected_catid = (int)trim(str_replace('{category}', '', $value));
            $category_arr[] = $selected_catid;
            $sub_catids = get_all_sub_categories($selected_catid);
            if (!empty($sub_catids)) {
                $category_arr = array_merge($category_arr, $sub_catids);
            }
        }
    }

    if (!empty($course_arr)) {
        list($c_sql, $c_params) = $DB->get_in_or_equal($course_arr, SQL_PARAMS_NAMED, 'c');
        $course_category_conditions[] = "c.id $c_sql";
        $sql_params += $c_params;
        $course_name_arr = $DB->get_fieldset_sql("SELECT fullname FROM {course} WHERE id $c_sql", $c_params);
    }
    if (!empty($category_arr)) {
        list($cat_sql, $cat_params) = $DB->get_in_or_equal($category_arr, SQL_PARAMS_NAMED, 'cat');
        $course_category_conditions[] = "c.category $cat_sql";
        $sql_params += $cat_params;
        $category_name_arr = $DB->get_fieldset_sql("SELECT name FROM {course_categories} WHERE id $cat_sql", $cat_params);
    }

    if (!empty($course_category_conditions)) {
        $sql_conditions[] = "(" . implode(' OR ', $course_category_conditions) . ")";
    }

    if (!empty($course_name_arr)) $course_cat_names[] = get_string('course_name', 'block_reporting') . " - <strong>" . implode(', ', $course_name_arr) . "</strong>";
    if (!empty($category_name_arr)) $course_cat_names[] = get_string('category', 'block_reporting') . " - <strong>" . implode(', ', $category_name_arr) . "</strong>";
    if (!empty($course_cat_names)) $coursename = implode(", ", $course_cat_names);

} else if (is_vendor($USER->id)) {
    $arr_course_ids = get_vendor_course_ids($USER->id);
    if (!empty($arr_course_ids)) {
        list($v_sql, $v_params) = $DB->get_in_or_equal($arr_course_ids);
        $sql_conditions[] = "c.id $v_sql";
        $sql_params = array_merge($sql_params, $v_params);
    } else {
        $sql_conditions[] = "1 = 0";
    }
}

// Handle Completion Status filter.
if (!empty($completionstatus)) {
    if ($completionstatus == 1) $sql_conditions[] = "(cmc.completionstate = 0 OR cmc.completionstate = 3 OR cmc.completionstate IS NULL)";
    else if ($completionstatus == 2) $sql_conditions[] = "(cmc.completionstate = 1 OR cmc.completionstate = 2)";
}

// Handle Date filters.
if ($enrolleddate_from) {
    $sql_conditions[] = "ue.timecreated >= ?";
    $sql_params[] = strtotime(str_replace('/', '-', $enrolleddate_from) . ' 00:00:00');
}
if ($enrolleddate_to) {
    $sql_conditions[] = "ue.timecreated <= ?";
    $sql_params[] = strtotime(str_replace('/', '-', $enrolleddate_to) . ' 23:59:59');
}
if ($completiondate_from) {
    $sql_conditions[] = "cmc.timemodified >= ?";
    $sql_params[] = strtotime(str_replace('/', '-', $completiondate_from) . ' 00:00:00');
}
if ($completiondate_to) {
    $sql_conditions[] = "cmc.timemodified <= ?";
    $sql_params[] = strtotime(str_replace('/', '-', $completiondate_to) . ' 23:59:59');
}

// Handle Dynamic User Profile Field filters.
$array_dynamic_query = get_reporting_filter_query();
if (!empty($array_dynamic_query['where'])) {
    $sql_conditions[] = substr(trim($array_dynamic_query['where']), 4); // Remove "AND "
}
if (!empty($array_dynamic_query['p_dynamic'])) {
    $sql_params = array_merge($sql_params, $array_dynamic_query['p_dynamic']);
}

// For dynamic fields only - get their definitions for display
$filters_array = [];
$filter_records = $DB->get_records_sql('SELECT rf.*, uif.name, uif.shortname, uif.datatype from {reporting_filter} rf JOIN {user_info_field} uif ON rf.user_info_field_id=uif.id ORDER BY uif.sortorder ASC');
if ($filter_records) {
    foreach ($filter_records as $record) {
        $filters_array[$record->shortname] = $record;
    }
}

// Handle General filters (e.g., city, country, lastaccess).
$general_filter_records = $DB->get_records('general_filter', ['status' => 'Y']);
$arr_user_profile_labels = get_list_user_profile_labels();
$general_filters_array2 = [];
if (!empty($general_filter_records) && is_siteadmin()) {
    foreach ($general_filter_records as $gfr) {
        $filtername = $gfr->filtername;
        if ($filtername == 'lastaccess') {
            if (optional_param('lastaccess_from', '', PARAM_RAW)) {
                $sql_conditions[] = "u.lastaccess >= ?";
                $sql_params[] = strtotime(str_replace('/', '-', optional_param('lastaccess_from', '', PARAM_RAW)) . ' 00:00:00');
            }
            if (optional_param('lastaccess_to', '', PARAM_RAW)) {
                $sql_conditions[] = "u.lastaccess <= ?";
                $sql_params[] = strtotime(str_replace('/', '-', optional_param('lastaccess_to', '', PARAM_RAW)) . ' 23:59:59');
            }
        } else {
            if ($param_value = optional_param_array($filtername, [], PARAM_RAW)) {
                list($g_sql, $g_params) = $DB->get_in_or_equal($param_value);
                $sql_conditions[] = "u.{$filtername} $g_sql";
                $sql_params = array_merge($sql_params, $g_params);
            }
        }
        $general_filters_array2[$filtername] = $gfr;
        $general_filters_array2[$filtername]->filterdesc = $arr_user_profile_labels[$filtername] ?? $filtername;
    }
}

// --- Finalize and Execute Query ---
$wheres = "WHERE c.visible=1 AND u.username != 'guest' AND c.fullname != ''";
if (!empty($sql_conditions)) {
    $wheres .= " AND " . implode(" AND ", $sql_conditions);
}

$array_plugin = get_plugin_installed();

$query = "
    SELECT CONCAT(u.id, '-', cm.id) AS uniqueid,
           u.id AS userid, u.firstname, u.lastname, u.username, u.email,
           u.city, u.country, u.lastaccess, cm.id AS coursemoduleid,
           ue.enrolid, e.courseid, c.fullname AS coursename, m.name AS module, cm.completion,
           cmc.completionstate AS completionstatus, ue.timecreated AS enrolleddate, cmc.timemodified AS completiondate,
           cm.instance AS instance, labelname.name AS labelname, bookname.name AS bookname, resourcename.name AS resourcename,
           urlname.name AS urlname, choicename.name AS choicename, foldername.name AS foldername, pagename.name AS pagename,
           scormname.sid AS scormid, scormname.name AS scormname, scormtrack.value AS scormstatus
           {$array_plugin['fields']} {$array_dynamic_query['fields']} {$hierarchy_query['fields']}
      FROM {user} u
      JOIN {user_enrolments} ue ON u.id = ue.userid
      JOIN {enrol} e ON e.id = ue.enrolid
      JOIN {course} c ON c.id = e.courseid
      LEFT JOIN {course_modules} cm ON c.id = cm.course
      LEFT JOIN {modules} m ON m.id = cm.module
      LEFT JOIN {course_modules_completion} cmc ON (cmc.userid = u.id AND cmc.coursemoduleid=cm.id)
      LEFT JOIN (SELECT DISTINCT l.name, l.course, l.id FROM {label} l) labelname ON (labelname.course = c.id AND cm.instance = labelname.id AND m.name = 'label')
      LEFT JOIN (SELECT DISTINCT f.name, f.course, f.id FROM {folder} f) foldername ON (foldername.course = c.id AND cm.instance = foldername.id AND m.name = 'folder')
      LEFT JOIN (SELECT DISTINCT p.name, p.course, p.id FROM {page} p) pagename ON (pagename.course = c.id AND cm.instance = pagename.id AND m.name = 'page')
      LEFT JOIN (SELECT DISTINCT b.name, b.course, b.id FROM {book} b) bookname ON (bookname.course = c.id AND cm.instance = bookname.id AND m.name = 'book')
      LEFT JOIN (SELECT DISTINCT r.name, r.course, r.id FROM {resource} r) resourcename ON (resourcename.course = c.id AND cm.instance = resourcename.id AND m.name = 'resource')
      LEFT JOIN (SELECT DISTINCT url.name, url.course, url.id FROM {url} url) urlname ON (urlname.course = c.id AND cm.instance = urlname.id AND m.name = 'url')
      LEFT JOIN (SELECT DISTINCT ch.name, ch.course, ch.id FROM {choice} ch) choicename ON (choicename.course = c.id AND cm.instance = choicename.id AND m.name = 'choice')
      LEFT JOIN (SELECT DISTINCT q.name, q.course, q.id FROM {quiz} q) quizname ON (quizname.course = c.id AND cm.instance = quizname.id AND m.name = 'quiz')
      LEFT JOIN (SELECT DISTINCT a.name, a.course, a.id FROM {assign} a) assignname ON (assignname.course = c.id AND cm.instance = assignname.id AND m.name = 'assign')
      LEFT JOIN (SELECT s.id as sid, s.name, s.course FROM {scorm} s) scormname ON (scormname.course = c.id AND cm.instance = scormname.sid AND m.name = 'scorm')
      LEFT JOIN (SELECT sst.userid, sst.scormid, sst.value FROM {scorm_scoes_track} sst WHERE sst.id IN (SELECT MAX(id) FROM {scorm_scoes_track} WHERE element = 'cmi.core.lesson_status' GROUP BY userid, scormid, attempt)) scormtrack ON (u.id = scormtrack.userid AND scormname.sid = scormtrack.scormid)
      {$array_dynamic_query['table']} {$array_plugin['tables']} {$hierarchy_query['table']}
      $wheres
  ORDER BY c.fullname, u.firstname, u.lastname, u.id, m.name
";

$rows = $DB->get_records_sql($query, $sql_params);

if (!$rows) {
    if ($report_type == 'HTML') echo $OUTPUT->header();
    echo get_string('generalresult','block_reporting');
    echo get_string('noresult','block_reporting');
    if ($report_type == 'HTML') echo $OUTPUT->footer();
} else {
    // --- Process Results ---
    $userinfo_row = [];
    foreach ($rows as $row) {
        if (empty($row->completion)) continue;
        if (empty($row->coursemoduleid)) continue;

        $uid = $row->userid;
        $cmid = $row->coursemoduleid;

        if (isset($userinfo_row[$uid][$cmid])) continue;

        $userinfo_row[$uid][$cmid] = [];
        $userinfo_row[$uid][$cmid]["firstname"] = $row->firstname;
        $userinfo_row[$uid][$cmid]["lastname"] = $row->lastname;
        $userinfo_row[$uid][$cmid]["enrolleddate"] = $row->enrolleddate;
        $userinfo_row[$uid][$cmid]["coursemoduleid"] = $row->coursemoduleid;
        $userinfo_row[$uid][$cmid]["coursename"] = $row->coursename;
        $userinfo_row[$uid][$cmid]["moduletype"] = $row->module;
        $userinfo_row[$uid][$cmid]["courseid"] = $row->courseid;
        $userinfo_row[$uid][$cmid]["completiondate"] = (($row->completionstatus == 1) || ($row->completionstatus == 2)) ? $row->completiondate : "";
        $userinfo_row[$uid][$cmid]["scormstatus"] = $row->scormstatus;
        $userinfo_row[$uid][$cmid]["completionstatus"] = $row->completionstatus;
        $userinfo_row[$uid][$cmid]["modulename"] = getModulename($row->module, $row->courseid, $cmid, $row->instance);

        if ($hierarchy) {
            $userinfo_row[$uid][$cmid]["node_name"] = $row->node_name ?? null;
            $userinfo_row[$uid][$cmid]["leveldescription"] = $row->leveldescription ?? null;
            $userinfo_row[$uid][$cmid]["nodedescription"] = $row->nodedescription ?? null;
        }

        $userinfo_row[$uid][$cmid]["profile_result"] = [];
        foreach ($filters_array as $key => $val) {
            $fieldvalue = $row->{$key} ?? null;
            if ($val->datatype == "checkbox") $fieldvalue = $fieldvalue ? 'Yes' : 'No';
            if ($val->datatype == "datetime" && !empty($fieldvalue)) $fieldvalue = date('d/m/Y', $fieldvalue);
            $userinfo_row[$uid][$cmid]["profile_result"][$key]['value'] = $fieldvalue;
            $userinfo_row[$uid][$cmid]["profile_result"][$key]["type"] = $val->datatype;
        }
        foreach ($general_filters_array2 as $key => $val) {
            $fieldvalue = $row->{$key} ?? null;
            if ($key == 'lastaccess' && !empty($fieldvalue)) $fieldvalue = date('d/m/Y H:i', $fieldvalue);
            $userinfo_row[$uid][$cmid]["profile_result"][$key]['value'] = $fieldvalue;
            $userinfo_row[$uid][$cmid]["profile_result"][$key]['type'] = $key;
        }
    }

    // --- Build Data for the Template (Replaces {php} tags) ---
    $applied_filters_summary = [];
    if (!empty($coursename)) $applied_filters_summary[] = $coursename;
    if (!empty($completionstatus)) {
        $status = ($completionstatus == 1) ? 'Not Completed' : 'Completed';
        $applied_filters_summary[] = "Status - <strong>" . $status . "</strong>";
    }
    if (!empty($enrolleddate_from)) $applied_filters_summary[] = "Enrolled date from - <strong>" . s($enrolleddate_from) . "</strong>";
    if (!empty($enrolleddate_to)) $applied_filters_summary[] = "Enrolled date to - <strong>" . s($enrolleddate_to) . "</strong>";
    if (!empty($completiondate_from)) $applied_filters_summary[] = "Completion date from - <strong>" . s($completiondate_from) . "</strong>";
    if (!empty($completiondate_to)) $applied_filters_summary[] = "Completion date to - <strong>" . s($completiondate_to) . "</strong>";

    foreach ($filters_array as $filter_name => $filter_data) {
        if ($filter_data->datatype == 'datetime') {
            if ($from = optional_param($filter_name . '_from', '', PARAM_RAW)) $applied_filters_summary[] = s($filter_data->name) . " from - <strong>" . s($from) . "</strong>";
            if ($to = optional_param($filter_name . '_to', '', PARAM_RAW)) $applied_filters_summary[] = s($filter_data->name) . " to - <strong>" . s($to) . "</strong>";
        } else {
            if ($value = optional_param_array($filter_name, [], PARAM_RAW)) $applied_filters_summary[] = s($filter_data->name) . " - <strong>" . s(implode(', ', $value)) . "</strong>";
        }
    }
    foreach ($general_filters_array2 as $filtername => $filter_data) {
        if ($filtername == 'lastaccess') {
            if ($from = optional_param('lastaccess_from', '', PARAM_RAW)) $applied_filters_summary[] = "Last access from - <strong>" . s($from) . "</strong>";
            if ($to = optional_param('lastaccess_to', '', PARAM_RAW)) $applied_filters_summary[] = "Last access to - <strong>" . s($to) . "</strong>";
        } else {
            if ($value = optional_param_array($filtername, [], PARAM_RAW)) $applied_filters_summary[] = s($filter_data->filterdesc) . " - <strong>" . s(implode(', ', $value)) . "</strong>";
        }
    }

    // --- Assign variables to Smarty ---
    $smarty->assign('applied_filters_summary', implode('; ', $applied_filters_summary));
    $smarty->assign('str_generalresult', get_string('generalresult', 'block_reporting'));
    $smarty->assign('str_sortingtip', get_string('sorting_tip', 'block_reporting'));
    $smarty->assign('str_coursename', get_string('course_name', 'block_reporting'));
    $smarty->assign('base_url', $CFG->wwwroot);
    $smarty->assign('userinfo_row', $userinfo_row);
    $smarty->assign('filters_array', $filters_array);
    $smarty->assign('general_filters_array2', $general_filters_array2);

    // --- Render Output ---
    if ($report_type == 'CSV') {
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=general_report.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        $tpl = $hierarchy ? 'report_hierarchy.csv.tpl' : 'report.csv.tpl';
        $smarty->display($tpl);
    } else if ($report_type == 'PDF' && $PDF_ENABLE) {
        $pdf_settings = get_report_pdf_options();
        $pdf_configures = get_report_pdf_configuration($pdf_settings);
        $pdf = new Pdf($pdf_configures);
        ob_start();
        $smarty->display('report_pdf.html.tpl');
        $pdf_html = ob_get_clean();
        $html_tmp_file = new File($pdf_html, '.html');
        $pdf->addPage($html_tmp_file->getFileName());
        if (!$pdf->send('general_report.pdf')) {
            throw new \Exception('Could not create PDF: ' . $pdf->getError());
        }
    } else { // Default to HTML
        echo $OUTPUT->header();
        $tpl = $hierarchy ? 'report_hierarchy.html.tpl' : 'report.html.tpl';
        $smarty->display($tpl);
        echo $OUTPUT->footer();
    }
}