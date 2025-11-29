{*
  Smarty Template: interface_individual.html.tpl
  This is the filter form for the individual report.
  - Removes all insecure {php} tags.
  - Uses variables passed from PHP for all strings and data.
*}

<h3>{$str_heading|escape:'html'}</h3>

<form action="individual.php" method="POST" name="search">
    <input type="hidden" name="report" value="1" />
    <table cellpadding="5">
        <tr>
            <td class="py-2"><strong>{$str_fullname|escape:'html'}</strong></td>
            <td class="py-2">
                {* The options are now generated in PHP for better performance and security *}
                <select name='uid' id='uid' data-placeholder='{$str_fullname|escape:'html'}' class='chosen-select'>
                    {$user_fullname_options}
                </select>
            </td>
        </tr>
        <tr>
            <td class="py-2"><strong>{$str_display_type|escape:'html'}</strong></td>
            <td class="py-2">
                <input type="radio" name="type" value="HTML" checked="checked" /> HTML<br/>
                <input type="radio" name="type" value="CSV" /> Excel/CSV (Select this option to print)<br/>
                
                {* The check for PDF availability is now safe *}
                {if $report_pdf == 2}
                    <input type="radio" name="type" value="PDF" /> PDF
                {/if}
            </td>
        </tr>
        <tr>
            <td class="py-2"></td>
            <td class="py-2">
                <input type="submit" class="btn btn-primary" value="{$str_go|escape:'html'}"/>
            </td>
        </tr>
    </table>
</form>

{literal}
<script type="text/javascript">
(function($) {
    $(document).ready(function() {
        var config = {
            '.chosen-select'           : {width: '100%'},
            '.chosen-select-deselect'  : {allow_single_deselect:true, width: '100%'},
            '.chosen-select-no-single' : {disable_search_threshold:10, width: '100%'},
            '.chosen-select-no-results': {no_results_text:'Could not find any!', width: '100%'},
            '.chosen-select-width'     : {width: '100%'}
        };
        for (var selector in config) {
            $(selector).chosen(config[selector]);
        }
    });
})(jQuery);
</script>
{/literal}