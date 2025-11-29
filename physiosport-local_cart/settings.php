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
 * The simple courses block class.
 *
 * Used to produce a master-detail view of a user's enrolled courses and their activities.
 *
 * @package   local_cart
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { 

    $settings = new admin_settingpage('local_cart_settings', get_string('pluginname', 'local_cart'));
    $paymentaccounts = $DB->get_records('payment_accounts', ['enabled' => 1]);
    $options = [];
    if (!empty($paymentaccounts)) {
        foreach ($paymentaccounts as $account) {
            $options[$account->id] = $account->name;
        }
    } else {
        $options[0] = get_string('nopaymentgateways, local_cart');
    }

    $setting = new admin_setting_configselect(
        'local_cart/paymentgatewayaccount', 
        get_string('paymentgatewayaccount', 'local_cart'), 
        get_string('paymentgatewayaccount_desc', 'local_cart'), 
        0, 
        $options 
    );
    $settings->add($setting);
    $ADMIN->add('localplugins', $settings);
}