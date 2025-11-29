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

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'local/cart:purchase_local_cart' => array(
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'riskbitmask'  => RISK_SPAM | RISK_PERSONAL,
        'archetypes'   => array(
            'user' => CAP_ALLOW, 
        ),
    ),

    'local/cart:managecart' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/site:manageblocks',
    ),

    'local/cart:additem' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
             'guest'          => CAP_ALLOW,
            'user'           => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacher'        => CAP_ALLOW,
        ),
    ),

    'local/cart:viewcart' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'guest'          => CAP_ALLOW, 
            'user'           => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacher'        => CAP_ALLOW,
        ),
    ),
    'local/cart:viewreceipt' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW
        ],
    ], // Added by Endush Fairy
);