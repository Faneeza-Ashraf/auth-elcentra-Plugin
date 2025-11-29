  {php} global $OUTPUT; echo $OUTPUT->heading(ucwords(get_string('individual_reports', 'block_reporting'))) {/php}
  <form action="individual.php" method="POST" name="search">
	<input type="hidden" name="report" value="1" />
	<table cellpadding="5">
	  <tr>
		<td class="py-2"><strong>{php} print_string('fullname') {/php}</strong></td>
		<td class="py-2"><select name='uid' id='uid' data-placeholder='{php} print_string('fullname') {/php}' class='chosen-select'>{$user_fullname_options}</select></td>
	  </tr>
	  <tr>
		<td class="py-2"><strong>{php} print_string('display_type', 'block_reporting') {/php}</strong></td>
		<td class="py-2">
		  <input type="radio" name="type" value="HTML" checked="checked" /> HTML<br/>
		  <input type="radio" name="type" value="CSV" /> Excel/CSV (Select this option to print)<br/>
		  {if $report_pdf == 2}
		  	<input type="radio" name="type" value="PDF" /> PDF
		  {/if}
		</td>
	  </tr>
	  <tr>
		<td class="py-2"></td>
		<td class="py-2">
		  <input type="submit" class="btn btn-primary" value="{php} print_string('go', 'block_reporting') {/php}"/>
		</td>
	  </tr>
	</table>
  </form>

 {literal}

<script type="text/javascript">
var config = {
    '.chosen-select'           : {width: '100%'},
    '.chosen-select-deselect'  : {allow_single_deselect:true, width: '100%'},
    '.chosen-select-no-single' : {disable_search_threshold:10, width: '100%'},
    '.chosen-select-no-results': {no_results_text:'Could not find any!', width: '100%'},
    '.chosen-select-width'     : {width: '100%'}
}
for (var selector in config) {
    $(selector).chosen(config[selector]);
}
</script>

  {/literal}
