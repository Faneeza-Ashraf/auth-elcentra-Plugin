  <h2>{php} print_string('course_overview_reports', 'block_reporting') {/php}</h2>
  <p>{php} print_string('fill_out_fields_you_wish', 'block_reporting') {/php}</p>
  <form action="courseoverview.php" method="get" name="search">
	<input type="hidden" name="report" value="1" />
	<input type="hidden" name="hierarchy" id="hierarchy"/>
	<input type="hidden" name="selectednodes" id="selectednodes"/>
	<input type="hidden" name="selectednodenames" id="selectednodenames"/>
	<table>
	  <tr>
		<td class="py-2"><strong>{php} print_string('course_name', 'block_reporting') {/php}</strong></td>
		<td class="py-2">
			{* @25/07/2018 *}
		  <select name="course[]" class="chzn-select" multiple>
			<option value=""></option>
			{foreach from=$courses item=course_name key=course_id}
				{html_options values=$course_id output=$course_name}
			{/foreach}
		  </select>
		</td>
	  </tr>
	  <tr>
		<td class="py-2"><strong>{php} print_string('enrolled_date', 'block_reporting') {/php}</strong></td>
		<td class="py-2">
			{* @23/07/2018 new enhancement *}
			<div class="enrolleddate-range d-inline-flex">
				<div class="input-prepend">
					<span class="add-on">From</span>
					<input type="text" name="enrolleddate_from" id="enrolleddate_from" class="py-1 mx-1 datepicker">
				</div>
				<div class="input-prepend">
					<span class="add-on">To</span>
					<input type="text" name="enrolleddate_to" id="enrolleddate_to" class="py-1 mx-1 datepicker">
				</div>
			</div>
			{* <input type="text" name="enrolleddate" id="enrolleddate" value="" class="datepicker"/>
			<input type="radio" name="enrol_date_condition" value="1">{php} print_string('before', 'block_reporting') {/php} &nbsp
			<input type="radio" name="enrol_date_condition" value="2" checked>{php} print_string('after', 'block_reporting') {/php} *}
		</td>
	  </tr>
	  <tr>
		<td class="py-2"><strong>{php} print_string('completion_date', 'block_reporting') {/php}</strong></td>
		<td class="py-2">
			{* @23/07/2018 new enhancement *}
			<div class="completiondate-range d-inline-flex">
				<div class="input-prepend">
					<span class="add-on">From</span>
					<input type="text" name="completiondate_from" id="completiondate_from" class="py-1 mx-1 datepicker">
				</div>
				<div class="input-prepend">
					<span class="add-on">To</span>
					<input type="text" name="completiondate_to" id="completiondate_to" class="py-1 mx-1 datepicker">
				</div>
			</div>
			{* <input type="text" name="completiondate" id="completiondate" value="" class="datepicker"/>
			<input type="radio" name="completion_date_condition" value="1" checked>{php} print_string('before', 'block_reporting') {/php} &nbsp
			<input type="radio" name="completion_date_condition" value="2">{php} print_string('after', 'block_reporting') {/php} *}
		</td>
	  </tr>
	   <tr>
		<td style='vertical-align: top;'><strong>{php} print_string('hierarchy', 'block_reporting') {/php}</strong></td>
		<td class='hierarchy-filter-block'>
			<input type="text" id="hie_search" placeholder="{php} print_string('search_for_node', 'block_reporting') {/php}"/>
			<div id='jstree'></div>
			<p>{php} print_string('current_selected_hierarchy', 'block_reporting') {/php}: <strong id="selection"></strong></p>
		</td>
	  </tr>
	  
	  {foreach from=$user_profile_filters_array item=user_profile_filter}
	  <tr>
		<td class="py-2"><strong>{$user_profile_filter->name}</strong></td>
		<td class="py-2">
		  {if $user_profile_filter->type != 'datetime'}
				{* @25/07/2018 enhancement *}
			  <select name={$user_profile_filter->shortname}[] 
			  	{if $user_profile_filter->type == 'text'}
			  		class='chzn-select' multiple
			  	{/if}>
				<option value=""></option>
				{foreach from=$user_profile_filter->user_profile_values item=user_profile_value}
					{if $user_profile_filter->type == 'checkbox'}
						{if $user_profile_value == '0'}
							<option value='0'>No</option>
						{elseif $user_profile_value == '1'}
							<option value='1'>Yes</option>
						{/if}
					{else}					
						<option value="{$user_profile_value}">{$user_profile_value}</option>
					{/if}
				{/foreach}
			  </select>
			{else}
				{* @23/07/2018 enhancement *}
				<div class="{$user_profile_filter->shortname}-range d-inline-flex">
					<div class="input-prepend">
						<span class="add-on">From</span>
						<input type="text" name="{$user_profile_filter->shortname}_from" id="{$user_profile_filter->shortname}_from" class="py-1 mx-1 datepicker">
					</div>
					<div class="input-prepend">
						<span class="add-on">To</span>
						<input type="text" name="{$user_profile_filter->shortname}_to" id="{$user_profile_filter->shortname}_to" class="py-1 mx-1 datepicker">
					</div>
				</div>
				{* <input type="text" name="{$user_profile_filter->shortname}" id="{$user_profile_filter->shortname}" value="" class="datepicker"/>
				<input type="radio" name="{$user_profile_filter->shortname}_condition" value="1" checked>Before &nbsp
				<input type="radio" name="{$user_profile_filter->shortname}_condition" value="2">After *}
			{/if}
		</td>
	  </tr>
	  {/foreach}

		{foreach from=$general_filters_array item=general_filters}
			<tr>
				<td class="py-2"><strong>{$general_filters->filterdesc}</strong></td>
				<td class="py-2">
					{if $general_filters->filtername == 'lastaccess'}
						{* @23/07/2018 enhancement *}
						<div class="lastaccess-range d-inline-flex">
							<div class="input-prepend">
								<span class="add-on">From</span>
								<input type="text" name="lastaccess_from" id="lastaccess_from" class="py-1 mx-1 datepicker">
							</div>
							<div class="input-prepend">
								<span class="add-on">To</span>
								<input type="text" name="lastaccess_to" id="lastaccess_to" class="py-1 mx-1 datepicker">
							</div>
						</div>
						{* <input type="text" name="lastaccess" id="lastaccess" value="" class="datepicker"/>
						<input type="radio" name="lastaccess_condition" value="1" checked>Before &nbsp
						<input type="radio" name="lastaccess_condition" value="2">After *}
					{elseif $general_filters->filtername == 'username' || $general_filters->filtername == 'email'}
						<span>Show all</span>
					{else}
						{* @25/07/2018 enhancement *}
						<select name="{$general_filters->filtername}" id="{$general_filters->filtername}" class="chzn-select" multiple>
							<option value=""></option>
							{foreach from=$general_filters->value item=value}
								<option value="{$value}">{$value}</option>
							{/foreach}
						</select>
					{/if}
				</td>
			</tr>
		{/foreach} 

	<tr>
		<td class="py-2"><strong>{php} print_string('suspended_users', 'block_reporting') {/php}</strong></td>
		<td class="py-2">
			<select name="suspendedusers">
				<option value="none" selected="selected">{php} print_string('exclude_suspended_users', 'block_reporting') {/php}</option>
				<option value="all">{php} print_string('include_suspended_users', 'block_reporting') {/php}</option>
				<option value="only">{php} print_string('show_suspended_users_only', 'block_reporting') {/php}</option>
			</select>
		</td>
	</tr>

	<tr>
		<td class="py-2"><strong>{php} print_string('display_type', 'block_reporting') {/php}</strong></td>
		<td class="py-2">
		  <input type="radio" name="type" value="HTML" checked="checked" /> HTML<br/>
		  <input type="radio" name="type" value="CSV" /> Excel/CSV ({php} print_string('select_to_print', 'block_reporting') {/php})<br/>
		  {if $report_pdf == 2}
		  	<input type="radio" name="type" value="PDF" /> PDF
		  {/if}
		</td>
	</tr>
	<tr>
		<td class="py-2"></td>
		<td class="py-2">
		  <input type="submit" class="btn btn-primary" value="Go &gt;&gt;"/>
		</td>
	</tr>
</table>
</form>
  {literal}
  	<style>
  		.chosen-container-single .chosen-single {
  			line-height: 30px;
  		}
  		.chosen-container-single .chosen-single {
  			height: 30px;
  		}
  		.hierarchy-filter-block div {
  			padding-top: 7px;
  		}
  		.hierarchy-filter-block p {
  			padding-top: 10px;
  		}
  		.general-report tr > td {
  			vertical-align: top;
  		}
		.chosen-container {
	 		min-width: 220px;
		}   
	</style>
    <script type="text/javascript">
		$(function(){
			$('.datepicker').datepicker({dateFormat: 'dd/mm/yy'});
			//$("#completiondate").datepicker({dateFormat: 'dd/mm/yy'});
			//{/literal}{$datepicker_fields}{literal}
			//$("#lastaccess").datepicker({ dateFormat: 'dd/mm/yy' });
			//$("#enrolleddate").datepicker({
			//	dateFormat: 'dd/mm/yy',
			//	onSelect: function(date){
			//		$("#completiondate").datepicker( "option", "minDate", date );
			//	}
			//});
			
			$(".chzn-select").chosen({search_contains: true});

			// prevent "Enter" key
			$(window).keydown(function(event){
			    if(event.keyCode == 13) {
					event.preventDefault();
					return false;
			    }
			});
			$('head').append('<link rel="stylesheet" href="css/jquery-ui.css" type="text/css" media="all">');
		});

(function($){
		// init jstree
		$('#jstree').jstree({ 
			'core' : {
			    'data' : {/literal}{$hierarchy_nodes}{literal}
			},
			'plugins' : ['search'],
			'search' :  {
				"show_only_matches" : true
			},
		});

		// update hierarchy selection
		$('#jstree').on('changed.jstree', function (e, data) {
			var i, j, r = []; list = [];
			for(i = 0, j = data.selected.length; i < j; i++) {
				r.push(data.instance.get_node(data.selected[i]).text);
				list.push(data.instance.get_node(data.selected[i]).id);
			}
			$('#selection').html(r.join(', '));
			$('#selectednodes').val(list.join(','));
			$('#selectednodenames').val(r.join(', '));
			// console.log("NODE ID: " + data.node.id);
			// console.log(data);
			if(data.node != undefined) {
				if(data.node.id != {/literal}{$root_node_id}{literal})
					$('#hierarchy').val(data.node.id);
				else
					$('#hierarchy').removeAttr('value');
			}

		}).jstree();

		// jstree search
		var to = false;
		$('#hie_search').keyup(function () {
			if(to) { clearTimeout(to); }
			to = setTimeout(function () {
				var v = $('#hie_search').val();
				$('#jstree').jstree(true).search(v);
			}, 250);
		});
})(jQuery)
    </script>

  {/literal}
