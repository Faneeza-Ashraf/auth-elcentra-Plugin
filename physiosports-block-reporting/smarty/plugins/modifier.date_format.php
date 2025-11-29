<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Include the {@link shared.make_timestamp.php} plugin
 */
require_once $smarty->_get_plugin_filepath('shared', 'make_timestamp');
/**
 * Smarty date_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     date_format<br>
 * Purpose:  format datestamps via strftime<br>
 * Input:<br>
 *         - string: input date string
 *         - format: strftime format for output
 *         - default_date: default date if $string is empty
 * @link http://smarty.php.net/manual/en/language.modifier.date.format.php
 *          date_format (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param string
 * @param string
 * @return string|void
 * @uses smarty_make_timestamp()
 */
function smarty_modifier_date_format($string, $format = '%b %e, %Y', $default_date = '')
{
    if ($string != '') {
        $timestamp = smarty_make_timestamp($string);
    } elseif ($default_date != '') {
        $timestamp = smarty_make_timestamp($default_date);
    } else {
        return;
    }
    if (DIRECTORY_SEPARATOR == '\\') {
        $_win_from = array('%D',       '%h', '%n', '%r',          '%R',    '%t', '%T');
        $_win_to   = array('%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S');
        if (strpos($format, '%e') !== false) {
            $_win_from[] = '%e';
            $_win_to[]   = sprintf('%\' 2d', date('j', $timestamp));
        }
        if (strpos($format, '%l') !== false) {
            $_win_from[] = '%l';
            $_win_to[]   = sprintf('%\' 2d', date('h', 'h', $timestamp));
        }
        $format = str_replace($_win_from, $_win_to, $format);
    }
    
    // --- FIX START: Replace deprecated strftime() with date() ---

    // Map strftime formats to date() formats
    $strftime_to_date_map = [
        '%a' => 'D', // An abbreviated textual representation of a day
        '%A' => 'l', // A full textual representation of a day
        '%b' => 'M', // An abbreviated textual representation of a month
        '%B' => 'F', // A full textual representation of a month
        '%d' => 'd', // The day of the month, 2 digits with leading zeros
        '%e' => 'j', // The day of the month without leading zeros
        '%H' => 'H', // 24-hour format of an hour with leading zeros
        '%I' => 'h', // 12-hour format of an hour with leading zeros
        '%m' => 'm', // A numeric representation of a month, with leading zeros
        '%M' => 'i', // Minutes with leading zeros
        '%p' => 'A', // Uppercase AM or PM
        '%S' => 's', // Seconds, with leading zeros
        '%y' => 'y', // A two digit representation of a year
        '%Y' => 'Y', // A full numeric representation of a year, 4 digits
        '%T' => 'H:i:s', // Same as %H:%M:%S
        '%D' => 'm/d/y', // Same as %m/%d/%y
        // Add more conversions as needed
    ];

    // Handle %% literal percent sign
    $format = str_replace('%%', '__PERCENT__', $format);
    
    // Convert strftime format to date format
    $format = str_replace(array_keys($strftime_to_date_map), array_values($strftime_to_date_map), $format);

    // Convert placeholder back to literal percent
    $format = str_replace('__PERCENT__', '%', $format);

    return date($format, $timestamp);
    // --- FIX END ---
}

/* vim: set expandtab: */

?>