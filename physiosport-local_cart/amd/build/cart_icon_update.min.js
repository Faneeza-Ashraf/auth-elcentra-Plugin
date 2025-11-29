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

define(['jquery', 'core/ajax'], function($, ajax) {
    console.log('local_cart/cart_icon_update.js: Module loaded.');

    var CART_AJAX_URL = M.cfg.wwwroot + '/local/cart/ajax.php';

    var updateCartIcon = function() {
        console.log('local_cart/cart_icon_update.js: updateCartIcon() function called.');

        $.ajax({
            url: CART_AJAX_URL,
            type: 'POST',
            data: {
                action: 'get_cart_count',
                sesskey: M.cfg.sesskey
            },
            dataType: 'json',
            success: function(response) {
                console.log('local_cart/cart_icon_update.js: AJAX success. Response:', response);

                var $cartLink = $('a.nav-link[href*="/local/cart/index.php"]');
                
                if (!$cartLink.length) {
                    console.error('local_cart/cart_icon_update.js: CRITICAL - Could not find the cart link in the navbar. Selector used: a.nav-link[href*="/local/cart/index.php"]');
                    return; 
                } else {
                    console.log('local_cart/cart_icon_update.js: Found cart link element:', $cartLink);
                }

                var $navItem = $cartLink.closest('.nav-item');
                $navItem.css('position', 'relative');
                $navItem.find('.cart-item-count-badge').remove();

                if (response.status === 'success' && response.count > 0) {
                    var count = response.count;
                    var badge = '<span class="cart-item-count-badge">' + count + '</span>';
                    $navItem.append(badge);
                    console.log('local_cart/cart_icon_update.js: Appended badge with count:', count);
                } else {
                     console.log('local_cart/cart_icon_update.js: Cart is empty or response status was not "success". No badge will be shown.');
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error('local_cart/cart_icon_update.js: AJAX Failed!', { status: textStatus, error: errorThrown, response: xhr.responseText });
            }
        });
    };

    return {
        update: updateCartIcon,
        init: function() {
            console.log('local_cart/cart_icon_update.js: init() called. Waiting for document ready.');
            $(document).ready(function() {
                console.log('local_cart/cart_icon_update.js: Document is ready. Calling updateCartIcon().');
                updateCartIcon();
            });
        }
    };
});