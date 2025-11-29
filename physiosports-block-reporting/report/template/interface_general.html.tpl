{php} global $OUTPUT; echo $OUTPUT->heading(ucwords(get_string('general_reports', 'block_reporting'))) {/php}
<p>Only fill out fields you wish to filter with.</p>
<form action="general.php" method="get" name="search">
	<input type="hidden" name="report" value="1" />
	<table>
	  <tr>
		<td class="py-2"><strong>Course Name</strong></td>
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
		<td class="py-2"><strong>Completion</strong></td>
		<td class="py-2">
		  <select name="completionstatus" class="form-control">
			<option label="" value="0"></option>
			<option label="Not Completed" value="1">Not Completed</option>
			<option label="Completed" value="2">Completed</option>
		  </select>
		</td>
	  </tr>
	  <tr>
		<td class="py-2"><strong>Enrolled date</strong></td>
		<td class="py-2">
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
		</td>
	  </tr>
	  <tr>
		<td class="py-2"><strong>Completion Date</strong></td>
		<td class="py-2">
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
		</td>
	  </tr>

	{if isset($user_profile_filters_array)}
		{foreach from=$user_profile_filters_array item=user_profile_filter}
			<tr>
				<td class="py-2"><strong>{$user_profile_filter->name}</strong></td>
				<td class="py-2">
			  {if $user_profile_filter->type != 'datetime'}
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
				{/if}
				</td>
			</tr>
		{/foreach}
	{/if}

	{if isset($general_filters_array)}
		{foreach from=$general_filters_array item=general_filters}
			<tr>
				<td class="py-2"><strong>{$general_filters->filterdesc}</strong></td>
				<td class="py-2">
					{if $general_filters->filtername == 'lastaccess'}
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
					{elseif $general_filters->filtername == 'username' || $general_filters->filtername == 'email'}
						<span>Show all</span>
					{else}
						<select name="{$general_filters->filtername}[]" id="{$general_filters->filtername}" class="chzn-select" multiple>
							<option value=""></option>
							{foreach from=$general_filters->value item=value}
								<option value="{$value}">{$value}</option>
							{/foreach}
						</select>
					{/if}
				</td>
			</tr>
		{/foreach}
	{/if}

	<tr>
		<td class="py-2"><strong>Suspended users</strong></td>
		<td class="py-2">
			<select name="suspendedusers" class="form-control">
				<option value="none" selected="selected">Exclude Suspended Users</option>
				<option value="all">Include Suspended Users</option>
				<option value="only">Show Suspended Users Only</option>
			</select>
		</td>
	</tr>

	<tr>
		<td class="py-2"><strong>Display Type</strong></td>
		<td class="py-2">
		  <input type="radio" name="type" value="HTML" checked="checked" /> HTML<br/>
		  <input type="radio" name="type" value="CSV" /> Excel/CSV (Select this option to print)<br/>
		  {* FIX: Removed the conditional logic for the PDF option as it is no longer supported. *}
		</td>
	</tr>
	<tr>
		<td class="py-2"></td>
		<td class="py-2">
		  <input type="submit" class="btn btn-primary" value="Go >>"/>
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
  	<script>
	  	//Note: jquery UI - Only use Datepicker and Autocomplete Libraries
		$(function(){
			// General Reports
			$('.chzn-select').chosen({search_contains: true, width: '100%'});
			$(".datepicker").datepicker({ dateFormat: 'dd/mm/yy' });
      		//{/literal}{$datepicker_fields}{literal}

			//Individual Reports
			$('head').append('<style>.ui-autocomplete { max-height: 100px; overflow-y: auto; overflow-x: hidden; padding-right: 20px; } * html .ui-autocomplete { height: 100px; }</style>');
			$(".autocomplete#name").autocomplete({ source:'get_names_list.php' });


			$("#tabs").tabs();

		});
		function isNumberKey(evt){
		    var charCode = (evt.which) ? evt.which : event.keyCode
		    if (charCode > 31 && (charCode < 48 || charCode > 57))
		        return false;
		    return true;
		}

  		function handleChange(input) {
		    if (input.value < 0) input.value = 0;
		    if (input.value > 100) input.value = 100;
		  }
	</script>
{/literal}