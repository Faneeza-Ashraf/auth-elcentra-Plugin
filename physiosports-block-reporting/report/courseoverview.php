<?php
	require_once('../../../config.php');

require_once('lib_pdf.php');
use mikehaertl\tmp\File;
use mikehaertl\wkhtmlto\Pdf;
$PDF_ENABLE = get_report_pdf_functionality_enable();

	require_once($CFG->dirroot .'/blocks/reporting/report/smarty/Smarty.class.php');
	require_once('lib.php');

	$PAGE->requires->js('/lib/mindatlas/jquery/jquery.min.js', true);
	$PAGE->requires->js('/lib/mindatlas/jquery/ui/jquery-ui.min.js', true);
	$PAGE->requires->css('/lib/mindatlas/jquery/ui/jquery-ui.min.css');
	$PAGE->requires->js('/blocks/reporting/report/js/chosen.js', true);
	$PAGE->requires->js('/blocks/reporting/report/js/bootstrap.min.js', true);
	$PAGE->requires->js('/blocks/reporting/report/js/jquery.tablesorter.js', true);
	$PAGE->requires->js('/blocks/reporting/report/js/Chartjs/Chart.js', true);
	$PAGE->requires->js('/blocks/reporting/report/js/jstree/dist/jstree.min.js', true);
	$PAGE->requires->js('/blocks/reporting/report/resource/chosen.jquery.js', true);
	$PAGE->requires->css('/blocks/reporting/report/css/chosen.css');
	$PAGE->requires->css('/blocks/reporting/report/js/jstree/dist/themes/default/style.min.css');

	global $USER, $DB;
	$users_ids = $USER->id;
	$DBH = new PDO("mysql:host=$CFG->dbhost;dbname=$CFG->dbname", $CFG->dbuser, $CFG->dbpass);

	if(!isset($userid)) $userid = $USER->id;
	require_login(0, false);
	$context_system = context_system::instance();
	$PAGE->set_context($context_system);

	$report = isset($_GET['report']);
	$mine = isset($_GET['mine']);

//REMOVE ALL DELETED FIELDS TO AVOID ISSUE
remove_deleted_fields();

	$report_type = 'HTML';
// ---------------------------- setup color for the graphs
$color_pie = array();
$color_bar = array();
$color_courseoverview = array();

	$defaultcolor_pie = get_default_colors();
	$defaultcolor_bar = array('0'=>'#cbdde6','1'=>'#eeeeee');
	$defaultcolor_courseoverview = array('0'=>'#cbdde6','1'=>'#eeeeee');

	$pie = $DB->get_record('report_setting',array('name'=>'pie_chart'));
	if(!empty($pie)) $color_pie = explode(',', $pie->setting);
	else $color_pie = $defaultcolor_pie;

	$bar = $DB->get_record('report_setting',array('name'=>'bar_chart'));
	if(!empty($bar)) $color_bar = explode(',', $bar->setting);
	else $color_bar = $defaultcolor_bar;

	$cour = $DB->get_record('report_setting',array('name'=>'courseoverview_chart'));
	if(!empty($cour)) $color_courseoverview = explode(',', $cour->setting);
	else $color_courseoverview = $defaultcolor_courseoverview;


$bgcolor_courseoverview = $color_courseoverview[0];
$percentage_bgcolor_courseoverview = $color_courseoverview[1];


// Check if the hierarchy is exist or not
$hierarchy = is_hierarchy_installed();

// -------------------------------------------------------------get value
	if($report && !$mine) {
		$course = get_get('course');

		$completionstatus = get_get('completionstatus');
		$gradeinputs = get_get('gradeinputs');
		$gradecomparation = get_get('gradecomparation');
		$report_type = get_get('type');
		$suspendedusers = get_get('suspendedusers');
		$enrolleddate_from = get_get('enrolleddate_from');
		$enrolleddate_to = get_get('enrolleddate_to');
		$completiondate_from = get_get('completiondate_from');
		$completiondate_to = get_get('completiondate_to');
	}
// ----------------------------------------------------------------smarty setup
	$smarty = new Smarty;
	$path = realpath(".");

	$smarty->compile_dir = get_reporting_compile_folder();
	$smarty->template_dir = $path . '/template' ;

    // *** CRITICAL FIX IS HERE ***
    // We assign these variables UNCONDITIONALLY right after Smarty is created.
    // This guarantees they are always available to any template, preventing "undefined" warnings.
    $smarty->assign('user_profile_filters_array', []);
    $smarty->assign('general_filters_array', []);
    $smarty->assign('report_pdf', $PDF_ENABLE);
    // *** END OF CRITICAL FIX ***

	$PAGE->set_pagelayout('standard');
	$PAGE->set_title($SITE->fullname);
	$PAGE->set_heading($SITE->fullname);
	$PAGE->set_url('/blocks/reporting/report/courseoverview.php');
	$PAGE->navbar->add("Reporting", new moodle_url('/blocks/reporting/report/courseoverview.php'));

// -------------------------------------------------------------------------header
	if(!$report || $report_type == 'HTML') {
	  echo $OUTPUT->header();
	}
// -------------------------------------------------------get userprofile filter and grade filter
if(!$report) {
		if($hierarchy){
			$courses = getCourses_Category_User();
		} else {
			$courses = getCourses_Category();
		}

		 $user_profile_filters_array = get_reporting_filter_array($hierarchy);
		 $general_filters_array = get_general_filter_array();

		$smarty->assign('courses', $courses);
		// Assign the real data, overwriting the empty defaults set earlier.
		if(!empty($general_filters_array)) $smarty->assign('general_filters_array', $general_filters_array);
		if(!empty($user_profile_filters_array)) $smarty->assign('user_profile_filters_array', $user_profile_filters_array);

		$datepicker_fields = get_reporting_date_picker_script();
		$smarty->assign('datepicker_fields', $datepicker_fields);

        // This line is no longer needed here because it's handled in the critical fix block above.
		// if($PDF_ENABLE) $smarty->assign('report_pdf', PDF_ENABLE);

		if($hierarchy){
			$hierarchy_nodes = get_hierarchy_tree($USER->id);
			$root_node_id = get_root_hierarchy($USER->id);
			$smarty->assign('hierarchy_nodes', $hierarchy_nodes);
			$smarty->assign('root_node_id', $root_node_id);
			$smarty->display('interface_courseoverview_hierarchy.html.tpl');
		} else{
			$smarty->display('interface_courseoverview.html.tpl');
		}

		echo $OUTPUT->footer();
		exit(0);
}
raise_memory_limit(MEMORY_HUGE);
	// @25/07/2018 enhancement
	$global_params = [];
	$wheres = "Where c.visible=1 AND u.username != 'guest' AND c.fullname !='' ";

	switch ($suspendedusers) {
		case 'none':
			$wheres .= " AND u.suspended = '0'". "\n";
			break;
		case 'only':
			$wheres .= " AND u.suspended = '1'". "\n";
			break;
		default:
			break;
	}
	$coursename = "";
	if($course && !empty($course)) {
		$category_arr = [];
		$course_arr = [];
		$course_name_arr = [];
		$category_name_arr = [];
		$cousre_category_conditions = [];
		$course_cat_names = [];
		foreach ($course as $key => $value) {
			if (strpos($value, 'category') === false) {
				$course_arr[] = $value;
				$course_name_arr[] = $DB->get_field('course', 'fullname', array('id' => $value));
			} else {
				$selected_catid = trim(str_replace('{category}', '', $value));
				$category_arr[] = $selected_catid;
				$sub_catids = get_all_sub_categories($selected_catid);
				if(!empty($sub_catids)){
					$category_arr = array_merge($category_arr, $sub_catids);
				}
				$category_name_arr[] = $DB->get_field('course_categories', 'name', array('id' => trim(str_replace('{category}', '', $value))));
			}
		}
		if (!empty($course_arr)) {
			list($q_course, $p_course) = $DB->get_in_or_equal($course_arr);
			$cousre_category_conditions[] = "c.id $q_course";
			$global_params = array_merge($global_params, $p_course);
		}
		if (!empty($category_arr)) {
			list($q_category, $p_category) = $DB->get_in_or_equal($category_arr);
			$cousre_category_conditions[] = "c.category $q_category";
			$global_params = array_merge($global_params, $p_category);
		}
		if (!empty($cousre_category_conditions)) {
			$wheres .= " AND (" . implode(' OR ', $cousre_category_conditions) . ") ";
		}
		if (!empty($course_name_arr)) {
			$course_cat_names[] = " " . get_string('course_name', 'block_reporting') . " - <strong>" . implode(', ', $course_name_arr) . "</strong> ";
		}
		if (!empty($category_name_arr)) {
			$course_cat_names[] = " " . get_string('category', 'block_reporting') . " - <strong>" . implode(', ', $category_name_arr) . "</strong> ";
		}
		if (!empty($course_cat_names)) {
			$coursename = implode(", ", $course_cat_names);
		}
	}else{
		// Add condition to apply for Vendor
		if(is_vendor($USER->id)){
		    $arr_course_ids = get_vendor_course_ids($USER->id);
		    $list = implode(",",$arr_course_ids);
		  	$wheres .= " AND c.id in (" . $list . ") \n";
		}
	}
	$smarty->assign('coursename', $coursename);

	if($completionstatus && !empty($completionstatus)) {
		if ($completionstatus == "1") {
			$wheres .= "AND ((cmc.completionstate == 0 AND cmc.completionstate == 3) OR (cmc.completionstate is NULL))"  ;
		}
		//Completed
		else if($completionstatus == "2") {
			$wheres .= "AND (cmc.completionstate = 1 OR cmc.completionstate = 2) " ;
		}
	}

	// @23/07/2018 enhancement
	if (!empty($enrolleddate_from)) {
		$enrolleddate_from = strtotime(str_replace('/', '-', $enrolleddate_from) . ' 00:00');
		$wheres .= "AND (ue.timecreated >= '" . $enrolleddate_from . "') ";
	}
	if (!empty($enrolleddate_to)) {
		$enrolleddate_to = strtotime(str_replace('/', '-', $enrolleddate_to) . ' 23:59');
		$wheres .= "AND (ue.timecreated <= '" . $enrolleddate_to . "') ";
	}
	if (!empty($completiondate_from)) {
		$completiondate_from = strtotime(str_replace('/', '-', $completiondate_from) . ' 00:00');
		$wheres .= "AND (cmc.timemodified >= '" . $completiondate_from . "') ";
	}
	if (!empty($completiondate_to)) {
		$completiondate_to = strtotime(str_replace('/', '-', $completiondate_to) . ' 23:59');
		$wheres .= "AND (cmc.timemodified <= '" . $completiondate_to . "') ";
	}

	if(isset($gradeinputs) && $gradeinputs !=""){
		$wheres .= "AND finalgrade.finalgrade ". $gradecomparation. "'" . $gradeinputs . "'";
	}

	$groupby ="GROUP BY ";

// Display RESULT =================================================
$hierarchy_query = array("fields"=>'','table'=>'','where'=>'');
$arr_selectednodes = array(); // Children of selected node
if($hierarchy){
	$selectednodes = $_GET['selectednodes'];
	$arr_selectednodes = get_all_users_selectednodes($selectednodes);// Get all users in selected nodes.
	$list_hierarchy_users = get_all_users_from_nodes($selectednodes); // List of users has been added to hierarchy condition
	$hierarchy_query = get_hierarchy_query($list_hierarchy_users);
	$wheres .=$hierarchy_query['where'];
}

// For dynamic fields only
	$array_dynamic_query = array('fields'=>'','table'=>'','where'=>'');

	$filter_records = $DB->get_records_sql('SELECT rf.* from mdl_reporting_filter rf inner join mdl_user_info_field uif ON rf.user_info_field_id=uif.id ORDER BY uif.sortorder ASC');
	$filters_array = array();
	$date_fields = array();
	$date_fields_start = 4; // NUMBER OF DEFAULT FIELDS
	if ($filter_records != false) {
		foreach ($filter_records as $record) {
			$fieldid = $record->user_info_field_id; //userid
			$rs = $DB->get_record('user_info_field', array('id'=>$fieldid));//table tr

			$filters_array[$rs->shortname] = new stdclass();
			$filters_array[$rs->shortname]->record = $rs;
			$filters_array[$rs->shortname]->type = $rs->datatype;
			if($rs->datatype=='datetime') $date_fields [] = $date_fields_start;
			$date_fields_start++;
		}
		// Get all
		$array_dynamic_query = get_reporting_filter_query();
		$wheres .= $array_dynamic_query['where'];
		// @25/07/2018 enhancement
		if (!empty($array_dynamic_query['p_dynamic'])) {
			$global_params = array_merge($global_params, $array_dynamic_query['p_dynamic']);
		}
	}
	// ---------------------------------------------------------------------------------------------add general filter fields
	$general_filter_records = $DB->get_records('general_filter');
	$general_filters_array2 = array();
	$arr_user_profile_labels = get_list_user_profile_labels();

	if (!empty($general_filter_records) && is_siteadmin($USER->id)) {
		// @23/07/2018 enhancement
		foreach ($general_filter_records as $general_filter_record){
			if ($general_filter_record->status =='Y') {
				if ($general_filter_record->filtername == 'lastaccess') {
					$lastaccess_from = get_get('lastaccess_from');
					if (!empty($lastaccess_from)) {
						$lastaccess_from = strtotime(str_replace('/', '-', $lastaccess_from) . ' 00:00');;
						$wheres .= " AND (lastaccess >= '" . $lastaccess_from . "') ";
					}
					$lastaccess_to = get_get('lastaccess_to');
					if (!empty($lastaccess_to)) {
						$lastaccess_to = strtotime(str_replace('/', '-', $lastaccess_to) . ' 23:59');;
						$wheres .= " AND (lastaccess <= '" . $lastaccess_to . "') ";
					}
				} else {
					if (!empty($_GET[$general_filter_record->filtername])) {
						// @25/07/2018 enhancement
						list($q_general, $p_general) = $DB->get_in_or_equal($_GET[$general_filter_record->filtername]);
						$wheres .= " AND (" . $general_filter_record->filtername . " " . $q_general . ") ";
						$global_params = array_merge($global_params, $p_general);
					}
				}
				// --------------------------------------------------general_filters_array2
				$general_filters_array2[$general_filter_record->filtername]= new stdclass();
				$general_filters_array2[$general_filter_record->filtername]->id = $general_filter_record->id;
				$general_filters_array2[$general_filter_record->filtername]->filtername = $general_filter_record->filtername;
				$general_filters_array2[$general_filter_record->filtername]->filterdesc =
				$arr_user_profile_labels[$general_filter_record->filtername];
				if($general_filter_record->filtername=='lastaccess') $date_fields [] = $date_fields_start;
				$date_fields_start++;
			}
		}
	}
	$date_field_script = get_reporting_sort_date_script($date_fields);
	$smarty->assign('date_field_script', $date_field_script);
	$smarty->assign('filters_array', $filters_array);
	$smarty->assign('general_filters_array2', $general_filters_array2);

// ================ GET ALL PLUGIN DATA. ==================
// Add new plugin, go to lib.php, finding function: get_plugin_installed()
	$array_plugin = get_plugin_installed();
// ----------------------------------------------------------CURRENT QUERY-----------------------------------------------
		$query ="
		 Select u.id as userid, u.firstname as firstname, u.lastname as lastname, u.username as username, u.email as email, u.city as city, u.country as country, u.lastaccess as lastaccess, cm.id as coursemoduleid, ue.enrolid,e.courseid,c.fullname as coursename,c.id as courseid, m.name as module,cm.completion,cmc.completionstate as completionstatus,ue.timecreated as enrolleddate, cmc.timemodified as completiondate,cm.instance as instance,labelname.name as labelname, bookname.name as bookname,resourcename.name as resourcename, urlname.name as urlname,choicename.name as choicename, foldername.name as foldername, pagename.name as pagename ,scormname.sid as scormid,
		 scormname.name as scormname,scormtrack.value as scormstatus
		 ".$array_plugin['fields'].$array_dynamic_query['fields'].$hierarchy_query['fields']."
		 FROM mdl_user as u
		 LEFT JOIN mdl_user_enrolments ue On u.id = ue.userid
		 LEFT JOIN mdl_enrol e On e.id = ue.enrolid
		 LEFT JOIN mdl_course c On c.id = e.courseid
		 LEFT JOIN mdl_course_modules cm On c.id = cm.course
		 LEFT JOIN mdl_modules m On m.id = cm.module
		 LEFT JOIN mdl_course_modules_completion cmc On (cmc.userid = u.id AND cmc.coursemoduleid=cm.id)
		 LEFT OUTER JOIN (
		 	SELECT Distinct mdl_label.name name, mdl_label.course cid, mdl_label.id id
		 	FROM mdl_label, mdl_course
		 	Where mdl_label.course = mdl_course.id
		 	) as labelname
			ON ( labelname.cid = c.id and cm.instance = labelname.id  and m.name = 'label')
		LEFT OUTER JOIN (
		 	SELECT Distinct mdl_folder.name name, mdl_folder.course cid, mdl_folder.id id
		 	FROM mdl_folder, mdl_course
		 	Where mdl_folder.course = mdl_course.id
		 	) as foldername
			ON ( foldername.cid = c.id and cm.instance = foldername.id  and m.name = 'folder')
		LEFT OUTER JOIN (
		 	SELECT Distinct mdl_page.name name, mdl_page.course cid, mdl_page.id id
		 	FROM mdl_page, mdl_course
		 	Where mdl_page.course = mdl_course.id
		 	) as pagename
			ON ( pagename.cid = c.id and cm.instance = pagename.id  and m.name = 'page')
		 LEFT OUTER JOIN (
		 	SELECT Distinct mdl_book.name name, mdl_book.course cid, mdl_book.id id
		 	FROM mdl_book, mdl_course
		 	Where mdl_book.course = mdl_course.id
		 	) as bookname
			ON ( bookname.cid = c.id and cm.instance = bookname.id and m.name = 'book')
		 LEFT OUTER JOIN (
		 	SELECT Distinct mdl_resource.name name, mdl_resource.course cid, mdl_resource.id id
		 	FROM mdl_resource, mdl_course
		 	Where mdl_resource.course = mdl_course.id
		 	) as resourcename
			ON ( resourcename.cid = c.id and cm.instance = resourcename.id and m.name = 'resource')
		 LEFT OUTER JOIN (
		 	SELECT Distinct mdl_url.name name, mdl_url.course cid, mdl_url.id id
		 	FROM mdl_url, mdl_course
		 	Where mdl_url.course = mdl_course.id
		 	) as urlname
			ON ( urlname.cid = c.id and cm.instance = urlname.id and m.name = 'url')
		 LEFT OUTER JOIN (
		 	SELECT Distinct mdl_choice.name name, mdl_choice.course cid, mdl_choice.id id
		 	FROM mdl_choice, mdl_course
		 	Where mdl_choice.course = mdl_course.id
		 	) as choicename
			ON ( choicename.cid = c.id and cm.instance = choicename.id and m.name = 'choice')
		 LEFT OUTER JOIN (
		 	SELECT Distinct mdl_quiz.name name, mdl_quiz.course cid, mdl_quiz.id id
		 	FROM mdl_quiz, mdl_course
		 	Where mdl_quiz.course = mdl_course.id
		 	) as quizname
			ON ( quizname.cid = c.id and cm.instance = quizname.id and m.name = 'quiz')
		 LEFT OUTER JOIN (
		 	SELECT Distinct mdl_assign.name name, mdl_assign.course cid, mdl_assign.id id
		 	FROM mdl_assign, mdl_course
		 	Where mdl_assign.course = mdl_course.id
		 	) as assignname
			ON ( assignname.cid = c.id and cm.instance = assignname.id and m.name = 'assign')
		LEFT OUTER JOIN (
		 	SELECT Distinct mdl_scorm.id sid, mdl_scorm.name name, mdl_scorm.course cid,mdl_scorm.id id
		 	FROM mdl_scorm, mdl_course, mdl_modules m
		 	Where mdl_scorm.course = mdl_course.id
		 	Group by mdl_scorm.id
		 	) as scormname
			ON ( scormname.cid = c.id and cm.instance = scormname.id  and m.name='scorm')
		LEFT OUTER JOIN (
			SELECT mdl_scorm_scoes_track.userid userid, mdl_scorm_scoes_track.scormid scormid, mdl_scorm_scoes_track.value value, mdl_scorm_scoes_track.attempt attempt
			FROM mdl_scorm_scoes_track
			 WHERE mdl_scorm_scoes_track.element = 'cmi.core.lesson_status'
			GROUP BY mdl_scorm_scoes_track.userid, mdl_scorm_scoes_track.scormid
			ORDER BY mdl_scorm_scoes_track.attempt
			) as scormtrack
			ON (u.id = scormtrack.userid
			AND scormname.sid = scormtrack.scormid)
		 "
		 ;
		 $limitation =" ";
// -------------------------------------------------------------------------------------------------------------EXECUTE QUERY
	$orderby=" ORDER BY c.fullname, u.firstname,u.lastname,u.id ";
	if(trim($groupby)=="GROUP BY") $groupby="";

	$query = sprintf($query.$array_dynamic_query['table'].$array_plugin['tables'].$hierarchy_query['table']. $wheres .$orderby.$groupby);
	$STH = $DBH->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$STH->execute($global_params);

	if ($STH) {
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		$rows = $STH->fetchall();
		$defaultid =0;

// ------------------get the data for rows and combine them into an array

		if(count($rows)<1){
			echo get_string('courseoverviewresult','block_reporting');
			echo get_string('noresult','block_reporting');
		}else{
			$userinfo_row = array();
			$modules = array();
			$totalno_module_course = 0;
			$tempcourseid = 0;
			$percentage = 0;
			$no_of_completionmodule = 0;
			$tempuserid = 0;
			$initcounter = 0;
			$totaltrue = 0;
			$totalfalse = 0;
			$counter = 0;
			$total_course_proportion = 0;
			// For graph
			$arr_courseinfo = array();

			foreach($rows as $row){
				if($row['completion']==0) continue;
				if(($row['completionstatus'] != 1)&&($row['completionstatus'] !=2)){
					$row["completiondate"]="";
				}
				$userinfo_row[$row["userid"]][$row["courseid"]]["firstname"] = $row["firstname"];
				$userinfo_row[$row["userid"]][$row["courseid"]]["lastname"] = $row["lastname"];
				$userinfo_row[$row["userid"]][$row["courseid"]]["coursename"] = $row["coursename"];
				$userinfo_row[$row["userid"]][$row["courseid"]]["enrolleddate"] = date('d/m/Y',$row["enrolleddate"]);
				if($row["completiondate"] != '' && $row["completiondate"]!="0"){
					$userinfo_row[$row["userid"]][$row["courseid"]]["completiondate"] = date('d/m/Y',$row["completiondate"]);
				}else{
					$userinfo_row[$row["userid"]][$row["courseid"]]["completiondate"] ='';
				}

				// Only being used by hierarchy
				if($hierarchy){
					$userinfo_row[$row["userid"]][$row["courseid"]]["node_name"] = $row['node_name'];
					$userinfo_row[$row["userid"]][$row["courseid"]]["leveldescription"] = $row['leveldescription'];
					$userinfo_row[$row["userid"]][$row["courseid"]]["nodedescription"] = $row['nodedescription'];
				}

				$rowmoduletype = $row["module"];
				$rowcoursemoduleid = $row["coursemoduleid"];
				$rowinstance = $row["instance"];
				$rowcourseid = $row["courseid"];
				$rowmodulename = getModulename($rowmoduletype,$rowcourseid,$rowcoursemoduleid,$rowinstance);
				$userinfo_row[$row["userid"]][$row["courseid"]]["module"] = $rowmodulename;


			//calculation of courseoverview
				if(isset($userinfo_row[$row["userid"]][$row["courseid"]]["module"]))
					$totalno_module_course = count($userinfo_row[$row["userid"]][$row["courseid"]]["module"]);
				else $totalno_module_course=0;

					if(!isset($userinfo_row[$row["userid"]][$row["courseid"]]["num_activities"]))
						$userinfo_row[$row["userid"]][$row["courseid"]]["num_activities"] =1;
					else $userinfo_row[$row["userid"]][$row["courseid"]]["num_activities"]++;

				if(!isset($userinfo_row[$row["userid"]][$row["courseid"]]["percentage"])){
					if(($row['completionstatus'] == 1)||($row['completionstatus']==2)||($row["scormstatus"]=="passed")||($row["scormstatus"]=="completed")){
						$userinfo_row[$row["userid"]][$row["courseid"]]["percentage"] = "100%";
						$userinfo_row[$row["userid"]][$row["courseid"]]["completed"] = 1;
					} else{
						$userinfo_row[$row["userid"]][$row["courseid"]]["percentage"] = "0%";
						$userinfo_row[$row["userid"]][$row["courseid"]]["completed"] = 0;
					}
				}else {
					if(($row['completionstatus'] == 1)||($row['completionstatus']==2)||($row["scormstatus"]=="passed")||($row["scormstatus"]=="completed")){
						if(!isset($userinfo_row[$row["userid"]][$row["courseid"]]["completed"])) $userinfo_row[$row["userid"]][$row["courseid"]]["completed"] = 1;
						else $userinfo_row[$row["userid"]][$row["courseid"]]["completed"]++;

						$percentage = (int)(($userinfo_row[$row["userid"]][$row["courseid"]]["completed"]/$userinfo_row[$row["userid"]][$row["courseid"]]["num_activities"])*100);
						$userinfo_row[$row["userid"]][$row["courseid"]]["percentage"] = $percentage."%";

					} else{
						$percentage = (int)(($userinfo_row[$row["userid"]][$row["courseid"]]["completed"]/$userinfo_row[$row["userid"]][$row["courseid"]]["num_activities"])*100);
						$userinfo_row[$row["userid"]][$row["courseid"]]["percentage"] = $percentage."%";
					}
				}


				foreach($filters_array as $key=>$val){
					if($val->type=="checkbox"){
						if($row[$key]=='1') $row[$key] = 'Yes';
						if($row[$key]=='0') $row[$key] = 'No';
					}
					if($val->type=="datetime"){
						if(($row[$key]==0)||($row[$key]=="")) $row[$key]="";
						else $row[$key] = date('d/m/Y',$row[$key]);
					}
					$userinfo_row[$row["userid"]][$row["courseid"]]["profile_result"][$key]['value'] = $row[$key];
					$userinfo_row[$row["userid"]][$row["courseid"]]["profile_result"][$key]["type"] = $val->type;

				}
				foreach($general_filters_array2 as $key=>$val){
					$userinfo_row[$row["userid"]][$row["courseid"]]["profile_result"][$key]['value']= $row[$key];
					$userinfo_row[$row["userid"]][$row["courseid"]]["profile_result"][$key]['type']= $key;
				}

				$coursenamefilter = $row['coursename'];

			// For graph getting information
				$arr_courseinfo[$row["courseid"]]["coursename"] = $row["coursename"];
				if(!isset($arr_courseinfo[$row["courseid"]]["num_completed"])) $arr_courseinfo[$row["courseid"]]["num_completed"] =0;

				if(!isset($arr_courseinfo[$row["courseid"]]["num_users_activities"])) $arr_courseinfo[$row["courseid"]]["num_users_activities"] = 1;
				else $arr_courseinfo[$row["courseid"]]["num_users_activities"] ++;

				if(($row['completionstatus'] == 1)||($row['completionstatus'] ==2)||($row["scormstatus"]=="passed")){
					if(!isset($arr_courseinfo[$row["courseid"]]["num_completed"])) $arr_courseinfo[$row["courseid"]]["num_completed"] = 1;
					else $arr_courseinfo[$row["courseid"]]["num_completed"] ++;
				}
				$counter++;
			}
// Finish the loop
		// Bar chart:
			$course_completion_diagram_value_string = "";
			$course_not_completion_diagram_value_string = "";
			$course_completion_diagram_value_totalenrolled = "";
			$course_completion_diagram_value_coursename_string="";
			foreach ($arr_courseinfo as $key => $course_row) {
				$percentage = ($course_row["num_completed"]/$course_row["num_users_activities"])*100; // percentage
				if(!$percentage) $percentage =0;
				$course_completion_diagram_value_string .= number_format($percentage,2,'.','').',';
				$course_not_completion_diagram_value_string .= number_format((100-$percentage),2,'.','').',';
				$course_completion_diagram_value_totalenrolled.= '100,';
				$course_completion_diagram_value_coursename_string .= shortern_course_name2($course_row["coursename"]).','; // percentage
			}


			$total_true = 0;
			$total_users_activities = 0;
			foreach ($arr_courseinfo as $key => $course_row) {
				$total_true = $total_true + $course_row["num_completed"];
				$total_users_activities = $total_users_activities + $course_row["num_users_activities"];
			}


			//Pie chart:
			$percentage_true = ($total_users_activities > 0) ? ($total_true/$total_users_activities) * 100 : 0;
			$percentage_false = (100 - $percentage_true);
			$total_overall_diagram_value['true'] = number_format($percentage_true,2,'.','');
			$total_overall_diagram_value['false'] = number_format($percentage_false,2,'.','');


			if($report_type == 'HTML') {

				// For assigning color of graph
				$smarty->assign('bgcolor_courseoverview',$bgcolor_courseoverview);
				$smarty->assign('percentage_bgcolor_courseoverview',$percentage_bgcolor_courseoverview);

				$smarty->assign('pie_color_completed',$color_pie[0]);
				$smarty->assign('pie_color_not_completed',$color_pie[1]);
				$smarty->assign('pie_highlightcolor_completed',$color_pie[2]);
				$smarty->assign('pie_highlightcolor_not_completed',$color_pie[3]);
				$smarty->assign('bar_color_completed',$color_bar[0]);
				$smarty->assign('bar_color_not_completed',$color_bar[1]);
				// End of assigning color

				$smarty->assign('userinfo_row',$userinfo_row);
				$smarty->assign('modules',$modules);
				$smarty->assign('course_completion_diagram_value_totalenrolled',$course_completion_diagram_value_totalenrolled);
				$smarty->assign('course_completion_diagram_value_string',rtrim($course_completion_diagram_value_string,","));
				$smarty->assign('course_completion_diagram_value_coursename',rtrim($course_completion_diagram_value_coursename_string,","));

				$smarty->assign('total_overall_diagram_value_true',$total_overall_diagram_value['true']);
				$smarty->assign('total_overall_diagram_value_false',$total_overall_diagram_value['false']);
				if($hierarchy){
					$arr_selected_results = get_selectednodes_name($arr_selectednodes);
					if(!empty($arr_selected_results)){
						$brand_labels = array();
						$brand_data = array();
						foreach ($arr_selectednodes as $nodeid => $list_users) {
							$arr_users = explode(",",$list_users);
							foreach ($arr_users as $uid) {
								if(isset($userinfo_row[$uid])){
									$arr_user_courses = $userinfo_row[$uid];
									$point = 0;
									$count = 0;
									foreach ($arr_user_courses as $cid => $u) {
										$point = $point + round($u['completed']/$u['num_activities'],2);
										$count++;
									}
									if ($count > 0) {
										$arr_selected_results[$nodeid]['completed'] +=  round($point/$count,2);
									}
									$arr_selected_results[$nodeid]['num_users']++;
								}
							}
							if($arr_selected_results[$nodeid]['num_users']==0) $arr_selected_results[$nodeid]['com_percent']=0;
							else $arr_selected_results[$nodeid]['com_percent'] = round(($arr_selected_results[$nodeid]['completed']/$arr_selected_results[$nodeid]['num_users'])*100,2);
							$brand_labels [] = $arr_selected_results[$nodeid]['label'];
							$brand_data [] = $arr_selected_results[$nodeid]['com_percent'];
						}
						$brand_l = implode(",", $brand_labels);
						$brand_d = implode(",", $brand_data);
						$smarty->assign('course_brand_completion_diagram_value_names',$brand_l);
						$smarty->assign('course_brand_completion_diagram_value_string',$brand_d);
					}
			 		$smarty->display('report_courseoverview_hierarchy.html.tpl');
			 	}else{
			 		$smarty->display('report_courseoverview.html.tpl');
			 	}

			 }else if($report_type == 'CSV') {
			 	$smarty->assign('userinfo_row',$userinfo_row);
				$smarty->assign('modules',$modules);
				header("Content-type: application/csv");
				header("Content-Disposition: attachment; filename=report.csv");
				header("Pragma: no-cache");
				header("Expires: 0");
				if($hierarchy){
					$smarty->display('report_courseoverview_hierarchy.csv.tpl');
				}else{
					$smarty->display('report_courseoverview.csv.tpl');
				}
			}else if($report_type=='PDF' && $PDF_ENABLE) {
				$pdf_settings = get_report_pdf_options();
				$pdf_configures = get_report_pdf_configuration($pdf_settings);
				$pdf = new Pdf($pdf_configures);
				$smarty->assign('userinfo_row', $userinfo_row);
				$smarty->assign('modules',$modules);
				$pdf_html = $smarty->fetch('report_courseoverview_pdf.html.tpl');
				$html_tmp_file = new File($pdf_html, '.html', Pdf::TMP_PREFIX, null);
				$pdf->addPage($html_tmp_file->getFileName());
				ob_end_clean();
				if(!$pdf->send()){
					print_r($pdf->getError());
				}
			}
		}
		if(!$report || $report_type == 'HTML') {
			echo $OUTPUT->footer();
		}
	}
?>