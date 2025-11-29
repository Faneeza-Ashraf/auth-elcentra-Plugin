{* 
  Smarty Template: report_hierarchy.html.tpl
  Upgraded to Moodle 4.x standards.
  - Removes all insecure {php} tags and direct `$_REQUEST` access.
  - Uses Moodle's {str} tag for all language strings.
  - Uses a simpler data structure provided by the PHP controller.
  - Safeguarded against "Undefined array key" warnings.
*}
<style>
    /* Your original CSS is fine and has been preserved */
    table.tablesorter {
        font-family:arial; background-color: #CDCDCD; margin:10px 0pt 15px; font-size: 8pt; width: 100%; text-align: left;
    }
    table.tablesorter thead tr th, table.tablesorter tfoot tr th {
        background-color: #e6EEEE; border: 1px solid #000; font-size: 8pt; padding: 4px;
    }
    table.tablesorter thead tr .header {
        background-image: url(css/bg.gif); background-repeat: no-repeat; background-position: center right; cursor: pointer;
    }
    table.tablesorter tbody td {
        color: #3D3D3D; padding: 4px; background-color: #FFF; vertical-align: top;
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
</style>

{* --- Display Applied Filters (This string is now safely generated in PHP) --- *}
{if !empty($filter_summary_html)}
    <p>Applied filters: {$filter_summary_html}</p>
{/if}

<h3>{str tag='generalresult' section='block_reporting'}</h3>
<p>{str tag='sorting_tip' section='block_reporting'}</p>
<div style='text-align:right; margin-bottom:10px'>
    <a href="#" class="export btn btn-primary">{str tag='export_exel_csv' section='block_reporting'}</a>
</div>

<table id="report" class="tablesorter">
 	<thead>
		<tr>
			<th>{str tag='fullname'}</th>
			<th>{str tag='course_name' section='block_reporting'}</th>
			<th>{str tag='module' section='block_reporting'}</th>
			<th>{str tag='completion' section='block_reporting'}</th>
			<th>{str tag='enrolled_date' section='block_reporting'}</th>
			<th>{str tag='completion_date' section='block_reporting'}</th>
			
            {* Dynamically add headers for the extra profile fields *}
			{if !empty($filters_array)}
                {foreach from=$filters_array item=filter}
                    <th>{$filter->record->name|escape:'html'}</th>
                {/foreach}
            {/if}
            {if !empty($general_filters_array2)}
                {foreach from=$general_filters_array2 item=filter}
                    <th>{$filter->filterdesc|escape:'html'}</th>
                {/foreach}
            {/if}
		</tr>
	</thead>
	<tbody>
    {* This now uses the same clean data structure as the other modern templates *}
    {if !empty($users)}
        {foreach from=$users item=user}
            <tr>
                <td style="border-width:1px;">
                    <a href="{$smarty.const.WWWROOT}/user/profile.php?id={$user.id}">
                        {$user.fullname|escape:'html'}
                    </a>
                </td>
                
                {* NOTE: This template assumes a different data structure than report_activity.tpl *}
                {* You may need to adjust the PHP to provide these fields if they don't exist in the $user object *}
                <td>{$user.coursename|default:'N/A'|truncate:50:"...":true}</td>
                <td>{$user.modulename|default:'N/A'}</td>
                <td>{$user.completionstatus_str|default:'N/A'}</td>
                <td>{$user.enrolleddate|default:'-'}</td>
                <td>{$user.completiondate_str|default:'-'}</td>

                {* 
                  PRIMARY FIX:
                  Safely loop through the profile_result for this user.
                  This check prevents the "Undefined array key" warning.
                *}
                {if isset($user.profile_result) and $user.profile_result}
                    {foreach from=$user.profile_result item=result}
                        <td style="border-width:1px;">
                            {if $result.type == 'lastaccess' and $result.value}
                                {$result.value|date_format:"%d/%m/%Y"}
                            {else}
                                {$result.value}
                            {/if}
                        </td>
                    {/foreach}
                {/if}
            </tr>
        {/foreach}
    {else}
        <tr>
            <td colspan="100%">{str tag='noresult' section='block_reporting'}</td>
        </tr>
    {/if}
  </tbody>
</table>
<div style='text-align:right'>
    <a href="#" class="export btn btn-primary">{str tag='export_exel_csv' section='block_reporting'}</a>
</div>

<script>
// Using Moodle's modern AMD JavaScript loader
require(['jquery', 'jquery-ui', 'block_reporting/jquery.tablesorter'], function($) {

    // Export button functionality now uses the server-side CSV generator for consistency and reliability.
    $(".export").on('click', function (event) {
        event.preventDefault();
    	const url = new URL(window.location.href);
        // Ensure we don't duplicate the type parameter if it's already there
        if (url.searchParams.get('type') === 'CSV') {
            window.open(url.href);
        } else {
            url.searchParams.set('type', 'CSV');
            window.open(url.href);
        }
    });

    // Custom date parser for tablesorter
	$.tablesorter.addParser({
        id: "customDate",
        is: function(s) { return false; }, // Disable auto-detection
        format: function(s) {
            const date = s.split('/');
            // Format as YYYYMMDD for correct sorting
            if (date.length === 3) { return date[2] + date[1] + date[0]; } 
            return s;
        },
        type: "numeric"
    });

    // Initialize the tablesorter widget
    $(document).ready(function(){
        $("#report").tablesorter({
            headers: { 
                4: { sorter: "customDate" }, // Enrolled Date
                5: { sorter: "customDate" }  // Completion Date
            },
            widgets: ['zebra']
        });
    });
});
</script>