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
</style>
{* This summary is now built in PHP and passed as a single variable *}
<p>Applied filters: {$applied_filters_summary}</p>
	
{$str_generalresult}

<p>{$str_sortingtip}</p>
<div style='float:right;margin-bottom:10px'><a href="#" class="export btn btn-primary">Export Excel/CSV</a> </div>
<div id='dvData'>
<table id="report" class="tablesorter">
 	<thead>
		<tr>
			<th>Full name</th>
			<th>{$str_coursename}</th>
			<th>Module</th>
			<th>Completion</th>
			<th>Enrolled Date</th>
			<th>Completion Date</th>
			{foreach from=$filters_array key=filter_name item=is_result_applied}
				<th>{$is_result_applied->name}</th>
	  		{/foreach}
	  		{foreach from=$general_filters_array2 key=filtername item=is_result_applied}
				<th>{$is_result_applied->filterdesc}</th>
	  		{/foreach}
		</tr>
	</thead>
	<tbody>
	{foreach from=$userinfo_row item=user key=user_id}
		{foreach from=$user item=course_module key=course_module_id}
			<tr>
				<td style="border-width:1px;">
				<a href="{$base_url}/user/profile.php?id={$user_id}">
					{$course_module.firstname} {$course_module.lastname}
				</a>
				</td>
				<td style="border-width:1px;">
					<a href="{$base_url}/course/view.php?id={$course_module.courseid}">
						{$course_module.coursename|truncate:50:"...":true}
					</a>
				</td>
				<td style="border-width:1px;">
					{$course_module.modulename}
				</td>
				<td style="border-width:1px;">
						{if $course_module.completionstatus == 1 or $course_module.completionstatus == 2 or $course_module.scormstatus == 'passed' or $course_module.scormstatus == 'completed'}
							Completed
						{elseif $course_module.scormstatus == 'incomplete'}
							In Progress
						{else}
							Not Completed
						{/if}
				</td>
				<td style="border-width:1px;">
					{if $course_module.enrolleddate}
						{$course_module.enrolleddate|date_format:"%d/%m/%Y"}
					{/if}
				</td>
				<td style="border-width:1px;">
		        	{if $course_module.completiondate}
						{$course_module.completiondate|date_format:"%d/%m/%Y"}
					{/if}
			  	</td>
				{foreach from = $course_module.profile_result key=abc item=result}
			  		{if $result.type=='lastaccess'}
						<td style="border-width:1px;">
						{if $result.value}
							{$result.value|date_format:"%d/%m/%Y"}
						{/if}
						</td>
					{else}
						<td style="border-width:1px;">{$result.value}</td>
					{/if}
				{/foreach}
			</tr>
		{/foreach}
	{/foreach}
  </tbody>

</table>
</div>
<div style='text-align:right'><a href="#" class="export btn btn-primary">Export Excel/CSV</a> </div>
{literal}
 <script>

$(document).ready(function () {

    function exportTableToCSV($table, filename) {

        var $rows = $table.find('tr'),
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character
            colDelim = '","',
            rowDelim = '"\r\n"',
            csv = '"' + $rows.map(function (i, row) {
                var $row = $(row);
                var $cols = $row.find('th, td'); // Simplified selector

                return $cols.map(function (j, col) {
                    var $col = $(col),
                        text = $col.text();
                        text = text.trim();

                    return text.replace(/"/g, '""'); // escape double quotes

                }).get().join(tmpColDelim);

            }).get().join(tmpRowDelim)
                .split(tmpRowDelim).join(rowDelim)
                .split(tmpColDelim).join(colDelim) + '"';

        $(this)
            .attr({
            'download': filename,
                'href': 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv),
                'target': '_blank'
        });
    }

    // This must be a hyperlink
    $(".export").on('click', function (event) {
        exportTableToCSV.apply(this, [$('#dvData>table'), 'report.csv']);
    });
});

 (function($){
	$.tablesorter.addParser({
            id: "customDate",
            is: function(s) {
                return false; // Let tablesorter auto-detect
            },
            format: function(s) {
                if (!s) return 0;
                var date = s.split('/');
                // new Date(year, month-1, day)
                return new Date(date[2], date[1] - 1, date[0]).getTime() || 0;
            },
            type: "numeric"
        });

    $(document).ready(function(){
        $("#report").tablesorter({
            headers:
                {
                    4: { sorter: "customDate" },
                    5: { sorter: "customDate" }
                },
            widgets: ['zebra']
        });
    });
})(jQuery);
</script>
{/literal}