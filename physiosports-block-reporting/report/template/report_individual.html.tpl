<style id="css">
	{literal}
		/* tables */
		table.tablesorter {
			font-family:arial;
			background-color: #CDCDCD;
			margin:10px 0pt 15px;
			font-size: 8pt;
			width: 100%;
			text-align: left;
		}
		table.tablesorter thead tr th, table.tablesorter tfoot tr th {
			background-color: #e6EEEE;
			border: 1px solid #000;
			font-size: 8pt;
			padding: 4px;
		}
		table.tablesorter thead tr .header {
			background-image: url(css/bg.gif);
			background-repeat: no-repeat;
			background-position: center right;
			cursor: pointer;
		}
		table.tablesorter tbody td {
			color: #3D3D3D;
			padding: 4px;
			background-color: #FFF;
			vertical-align: top;
		}
		table.tablesorter tbody tr.odd td {
			background-color:#F0F0F6;
		}
		table.tablesorter thead tr .headerSortUp {
			background-image: url(css/asc.gif);
		}
		table.tablesorter thead tr .headerSortDown {
			background-image: url(css/desc.gif);
		}
		table.tablesorter thead tr .headerSortDown, table.tablesorter thead tr .headerSortUp {
		background-color: #8dbdd8;
		}
	{/literal}
	{literal}
		.progress_bar{
		    border: 0px solid white;
		    height: 21px;
		    display: inline-block;
		    width: 100%;
		    background:{/literal} {$bgcolor_courseoverview} {literal};  // big background
		    -webkit-border-radius: 20px;
		    -moz-border-radius: 20px;
		    border-radius: 20px;
		    padding: 0px;
			}
		.progress_text {
		    height: 20px;
		    color: #fff;
		    display: inline-block;
		    line-height: 20px;
		    width: 100%;
		    text-align: center;
		}
		.progress_percentage {
		    height: 19px;
		    margin-top: -19px;
		    background-color: {/literal} {$percentage_bgcolor_courseoverview} {literal}; // percentage
		    -webkit-border-radius: 20px;
		    -moz-border-radius: 20px;
		    border-radius: 20px;
		}
		#canvas-holder{
			position:relative;
			width: 100%;
		}
		#canvas-holder canvas{
			float: left;
			clear: right;
		}
		.abc{
			float: left;
			width: 300px;
			text-align: center;
			clear: left;
		}
        /* FIX: Corrected CSS syntax */
		#diagramleft{
            position: relative;
            width: 400px;
            height: 400px;
		}

		#overallcompletion{

		}
		#coursecompletion{
			padding-right:50px;
			float:right;
		}
		#coursecompleted{
		    background-color: {/literal} {$pie_color_completed} {literal};
		    width: 20px;
		    height: 20px;
			display: inline-block;
    		margin-left: 10px;
    		margin-right: 2px;
    	}
    	#coursenotcompleted{
		    background-color: {/literal} {$pie_color_not_completed} {literal};
		    width: 20px;
		    height: 20px;
			display: inline-block;
    		margin-left: 10px;
    		margin-right: 2px;
    	}
		@media(max-width: 957px){
		#coursecompletion{
			padding-right:50px;
			float:inherit;
		}
		}
	{/literal}
</style>
<div id="course_completion_diagram_value_totalenrolled"style="display:none">{$course_completion_diagram_value_totalenrolled}</div>
<div id="course_completion_diagram_value" style="display:none">{$course_completion_diagram_value}</div>
<div id="course_completion_diagram_value_string"style="display:none">{$course_completion_diagram_value_string}</div>
<div id="course_completion_diagram_value_coursename"style="display:none">{$course_completion_diagram_value_coursename}</div>
<div id="total_overall_diagram_value_true" name="Completed"style="display:none">{$total_overall_diagram_value_true}</div>
<div id="total_overall_diagram_value_false"name="Not Completed"style="display:none">{$total_overall_diagram_value_false}</div>

<div id="pie_color_completed"name="pie_color_completed"style="display:none">{$pie_color_completed}</div>
<div id="pie_color_not_completed"name="pie_color_not_completed"style="display:none">{$pie_color_not_completed}</div>
<div id="pie_highlightcolor_completed"name="pie_highlightcolor_completed"style="display:none">{$pie_highlightcolor_completed}</div>
<div id="pie_highlightcolor_not_completed"name="pie_highlightcolor_not_completed"style="display:none">{$pie_highlightcolor_not_completed}</div>

<div id="canvas-holder">

</div>

{* FIX: Removed semicolons from echo statements *}
{php}
	echo"<p>Applied filters: ";
	if(isset($_REQUEST["course"]) && $_REQUEST["course"]!="") echo" Course - <strong>" . $_REQUEST["course"] . "</strong> ";
	if(isset($_REQUEST["completionstatus"]) && $_REQUEST["completionstatus"]!="" && $_REQUEST["completionstatus"]!= 0) {
		switch ($_REQUEST["completionstatus"]) {
			case 1: $status = 'Not Completed'; break;
			case 2: $status = 'Completed'; break;
		}
		echo" Status - <strong>" . $status . "</strong> ";
	}
	if(isset($_REQUEST["enrolleddate"]) && $_REQUEST["enrolleddate"]!="") echo" Enrolled date - <strong>" . $_REQUEST["enrolleddate"] . "</strong> ";
	if(isset($_REQUEST["completiondate"]) && $_REQUEST["completiondate"]!="") echo" Completion date - <strong>" . $_REQUEST["completiondate"] . "</strong> ";
	
    if ($this->get_template_vars('filters_array')) {
        $filters_array = $this->get_template_vars('filters_array');
    	foreach ($filters_array as $filter_name=>$is_result_applied) {
    		$actual_filter_name = strtolower(str_replace(" ","_", $filter_name));
    		if(isset($_REQUEST[$actual_filter_name]) && $_REQUEST[$actual_filter_name]!="") {
    			echo " " . $filter_name . " - <strong>" . $_REQUEST[$actual_filter_name] . "</strong> ";
    		}
    	}
    }

	if(isset($_REQUEST["name"]) && ($_REQUEST["name"]!="")) {
		echo" User - <strong>" . $_REQUEST["name"] . "</strong> ";
	}
	$gradecomparation = get_get('gradecomparation');
	$gradeinputs = get_get('gradeinputs');

	if ($this->get_template_vars('general_filters_array2')) {
	    $general_filters_array = $this->get_template_vars('general_filters_array2');
    	if((isset($gradeinputs)||!empty($gradeinputs)) && $gradeinputs != "" && is_array($general_filters_array)){
    		foreach($general_filters_array as $filteritem){
    		    echo $filteritem->filtername ." ".$gradecomparation ." ". $gradeinputs ." ";
    		}
    	}
	}
	echo"</p>";
{/php}

<h1>Individual Results</h1>
<div id="canvas-holder">
	{* <div id='coursenotcompleted'> </div> Not completed <div id='coursecompleted'> </div>Completed *}
	<div id="diagramleft">
		<div class='abc'><br>Course Overview Diagram</div>
		<canvas id="overallcompletion"></canvas>
	</div>
	<div class="clearfix"></div>
</div>

<div style='float:left'>
<br>
<p style='display: inline-block;'>Tip: Sort multiple columns simultaneously by holding down the shift key and clicking a second, third or even fourth column header!</p>
</div>
<div style='float:right;margin-bottom:10px'><a href="#" class="export btn btn-primary">Export Excel/CSV</a> </div>
<div class="clearfix"></div>
<div id='dvData'>
<table id="report" class="tablesorter">
 	<thead>
		<tr>
			<th>Full name</th>
			<th>{php}print_string('course_name', 'block_reporting'){/php}</th>
			<th>Module</th>
			<th>Completion</th>
			<th>Enrolled Date</th>
			<th>Completion Date</th>
			{if isset($filters_array)}
				{foreach from=$filters_array key=filter_name item=is_result_applied}
					<th>{$is_result_applied->record->name}</th>
				{/foreach}
			{/if}
	  		{if isset($general_filters_array2)}
				{foreach from=$general_filters_array2 key=filtername item=is_result_applied}
					<th>{$is_result_applied->filterdesc}</th>
				{/foreach}
			{/if}
		</tr>
	</thead>
	<tbody>
	{if isset($userinfo_row)}
		{foreach from=$userinfo_row item=user key=user_id}
			{foreach from=$user item=course_module key=course_module_id}
				<tr>
					<td style="border-width:1px;">
					<a href="../../../user/profile.php?id={$user_id}">
						{$course_module.firstname} {$course_module.lastname}
					</a>
					</td>
					<td style="border-width:1px;">
						<a href="../../../course/view.php?id={$course_module.courseid}">
							{$course_module.coursename|truncate:50:"...":true}
						</a>
					</td>
					<td style="border-width:1px;">
						{if isset($course_module.modulename) and is_array($course_module.modulename)}
							{foreach from=$course_module.modulename item=name}
								{$name}
							{/foreach}
						{/if}
					</td>
					<td style="border-width:1px;">
							{if isset($course_module.completionstatus) and $course_module.completionstatus == 1}
									Completed
							{elseif isset($course_module.completionstatus) and $course_module.completionstatus == 2}
									Completed
							{elseif isset($course_module.scormstatus) and $course_module.scormstatus == 'passed'}
									Completed
							{elseif isset($course_module.scormstatus) and $course_module.scormstatus == 'completed'}
									Completed
							{elseif isset($course_module.scormstatus) and $course_module.scormstatus == 'incomplete'}
									In Progress
							{else}
								Not Completed
							{/if}

					</td>
					<td style="border-width:1px;">
						{if isset($course_module.enrolleddate) and $course_module.enrolleddate != ''}
							{$course_module.enrolleddate|date_format:"%d/%m/%Y"}
						{/if}
					</td>
					<td style="border-width:1px;">
						{if isset($course_module.completiondate) and $course_module.completiondate != ''}
							{$course_module.completiondate|date_format:"%d/%m/%Y"}
						{/if}
					</td>

					{if isset($course_module.profile_result) and is_array($course_module.profile_result)}
						{foreach from=$course_module.profile_result key=abc item=result}
							{if $result.type === 'datetime'}
								<td style="border-width:1px;">{$result.value|date_format:"%d/%m/%Y"}</td>
							{else}
								<td style="border-width:1px;">{$result.value}</td>
							{/if}
						{/foreach}
					{/if}

					{if isset($course_module.grade) and $course_module.grade !== ''}
						 <td style="border-width:1px;">
						{$course_module.grade|string_format:"%.2f"}
						</td>
					{/if}
				</tr>
			{/foreach}
		{/foreach}
	{/if}
  </tbody>

</table>
</div>
<div style='text-align:right'><a href="#" class="export btn btn-primary">Export Excel/CSV</a> </div>
{literal}
<script>
    var total_overall_diagram_value_true = document.getElementById("total_overall_diagram_value_true").innerHTML;
    var total_overall_diagram_value_false = document.getElementById("total_overall_diagram_value_false").innerHTML;
    var pie_color_completed = document.getElementById("pie_color_completed").innerHTML;
    var pie_highlightcolor_completed = document.getElementById("pie_highlightcolor_completed").innerHTML;
    var pie_color_not_completed = document.getElementById("pie_color_not_completed").innerHTML;
    var pie_highlightcolor_not_completed = document.getElementById("pie_highlightcolor_not_completed").innerHTML;

    var data = {
        datasets: [{
            data: [total_overall_diagram_value_true, total_overall_diagram_value_false],
            backgroundColor: [pie_color_completed, pie_color_not_completed],
            hoverBackgroundColor: [pie_highlightcolor_completed, pie_highlightcolor_not_completed]
        }],
        labels: ['Completed', 'Not Completed']
    };

    var ctx = $("#overallcompletion").get(0).getContext("2d");
    $(window).on('load',function(){
        setTimeout(function() {
            if ($("#overallcompletion").length) {
                window.myPie = new Chart(ctx, {
                    type: 'pie',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            position: 'top',
                            onClick: (e) => e.stopPropagation()
                        }
                    }
                });
            }
        }, 100);
    });
</script>

{/literal}
{literal}
 <script>
 (function($){
	$(document).ready(function(){
		function exportTableToCSV($table, filename) {

		    var $rows = $table.find('tr'),
		        tmpColDelim = String.fromCharCode(11),
		        tmpRowDelim = String.fromCharCode(0),
		        colDelim = '","',
		        rowDelim = '"\r\n"',
		        csv = '"' + $rows.map(function (i, row) {
		            var $row = $(row);
		            var $cols;
		            if($row.find('th').length > 0) $cols = $row.find('th');
		            else $cols = $row.find('td');

		            return $cols.map(function (j, col) {
		                var $col = $(col),
		                    text = $col.text();
		                    text = text.trim();

		                return text.replace(/"/g, '""');

		            }).get().join(tmpColDelim);

		        }).get().join(tmpRowDelim)
		            .split(tmpRowDelim).join(rowDelim)
		            .split(tmpColDelim).join(colDelim) + '"',
		        csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

		    $(this)
		        .attr({
		        'download': filename,
		            'href': csvData,
		            'target': '_blank'
		    });
		}

	    $(".export").on('click', function (event) {
	        exportTableToCSV.apply(this, [$('#dvData>table'), 'report.csv']);
	    });

	    if ($("#report").length) {
            $("#report").tablesorter({
                headers:
                    {
                        4: { sorter: "customDate" },
                        5: { sorter: "customDate" },
                        {/literal} {$date_field_script} {literal}
                    },
                widgets: ['zebra']
            });

            $.tablesorter.addParser({
                id: "customDate",
                is: function(s) {
                    return false;
                },
                format: function(s) {
                    var date = s.split('/');
                    return date.length === 3 ? new Date(date[2], date[1] - 1, date[0]).getTime() || 0 : 0;
                },
                type: "numeric"
            });
	    }
	});
})(jQuery)
</script>
{/literal}