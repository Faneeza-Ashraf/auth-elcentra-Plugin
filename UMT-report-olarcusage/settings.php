<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Settings for the Custom Report plugin.
 *
 * @package    report_olarcusage
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $ADMIN->add('reports', new admin_externalpage('reportuseolarc',
        get_string('pluginname', 'report_olarcusage'),
        new moodle_url('/report/olarcusage/index.php'),
        'report/olarcusage:view'
    ));
    $settings = new admin_settingpage('report_olarcusage_settings_page', get_string('settings', 'report_olarcusage'));
    $ADMIN->add('reports', $settings);
    $formats = core_plugin_manager::instance()->get_plugins_of_type('format');
    $formatoptions = [];
    foreach ($formats as $formatname => $formatobj) {
        $formatoptions[$formatname] = get_string('pluginname', 'format_' . $formatname);
    }
    $name = 'report_olarcusage/patternformat';
    $title = get_string('patternformat', 'report_olarcusage');
    $description = get_string('patternformat_desc', 'report_olarcusage');
    $default = 'onetopic';
    $setting = new admin_setting_configselect($name, $title, $description, $default, $formatoptions);
    $settings->add($setting);
}