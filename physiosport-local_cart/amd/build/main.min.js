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

define(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification) {
    var CART_AJAX_URL = M.cfg.wwwroot + '/local/cart/ajax.php';
    var SESSKEY = M.cfg.sesskey; 

    /**
     * Helper to get a string from Moodle's string manager.
     * This ensures the string 'local_cart' is always used as the component.
     */
    function getString(stringname, component = 'local_cart') {
        return M.str[component] && M.str[component][stringname] ? M.str[component][stringname] : `[[${stringname}, ${component}]]`;
    }
    function toggleCheckoutButtons(isZeroTotal) {
        if (isZeroTotal) {
            $('#payment-btn').hide();
            $('#free-checkout-btn').show();
        } else {
            $('#free-checkout-btn').hide();
            $('#payment-btn').show();
        }
    }
    var debounceTimeout;
    function debounce(func, delay) {
        return function() {
            var context = this, args = arguments;
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(function() {
                func.apply(context, args);
            }, delay);
        };
    }

    /**
     * Updates a cart item via AJAX.
     * @param {int} cartitemid
     * @param {string} licensetype
     * @param {int} quantity
     */
    function updateCartItem(cartitemid, licensetype, quantity) {
        var $card = $('.local-cart-item-card[data-cartitemid="' + cartitemid + '"]');
        $card.find('input, button').prop('disabled', true);

        $.ajax({
            url: CART_AJAX_URL,
            type: 'POST',
            data: {
                action: 'update_cart_item',
                cartitemid: cartitemid,
                licensetype: licensetype,
                quantity: quantity,
                sesskey: SESSKEY
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#item-total-' + cartitemid).text('$' + response.itemtotalformatted);
                    $card.find('.local-cart-quantity-input').val(response.newquantity);
                    $card.find('.local-cart-individual-quantity').text(response.newquantity);
                    $('#overall-total-display').text('$' + response.totalpriceformatted);
                    var $summaryitem = $('#order-summary-items').find('.summary-item[data-cartitemid="' + cartitemid + '"]');
                    $summaryitem.find('.summary-item-quantity').text(response.newquantity);
                    $summaryitem.find('.summary-item-total').text('$' + response.itemtotalformatted);
                    $card.find('input, button').prop('disabled', false);
                } else {
                    notification.alert(response.message, 'alert-danger');
                    $card.find('input, button').prop('disabled', false);
                }
            },
            error: function() {
                notification.alert(getString('failedtoupdatecartitem'), 'alert-danger');
                $card.find('input, button').prop('disabled', false);
            }
        });
    }
    function applyVisibility(card) {
        var $card = $(card);
        var $quantitycontainer = $card.find('.local-cart-quantity-container');
        var $couponmessage = $card.find('.local-cart-coupon-message');
        var $individualprice = $card.find('.local-cart-individual-price');
        var licensetype = $card.find('.licensetype-radio:checked').val();

        $quantitycontainer.removeClass('shown-by-js-flex').addClass('hidden-by-js');
        $couponmessage.removeClass('shown-by-js-block').addClass('hidden-by-js');
        $individualprice.removeClass('shown-by-js-block').addClass('hidden-by-js');

        if (licensetype === 'coupon') {
            $quantitycontainer.removeClass('hidden-by-js').addClass('shown-by-js-flex');
            $couponmessage.removeClass('hidden-by-js').addClass('shown-by-js-block');
        } else {
            $individualprice.removeClass('hidden-by-js').addClass('shown-by-js-block');
        }
    }
    var debouncedUpdateQuantity = debounce(function($input) {
        var cartitemid = $input.data('cartitemid');
        var newquantity = parseInt($input.val(), 10);
        var $card = $input.closest('.local-cart-item-card');
        var licensetype = $card.find('.licensetype-radio:checked').val();

        if (isNaN(newquantity) || newquantity < 1) {
            newquantity = 1;
            $input.val(1);
        }
        updateCartItem(cartitemid, licensetype, newquantity);
    }, 500);

    return {
        init: function() {
            $(document).on('change', '.licensetype-radio', function() {
                var $this = $(this);
                var cartitemid = $this.data('cartitemid');
                var licensetype = $this.val();
                var $card = $this.closest('.local-cart-item-card');
                var $quantityinput = $card.find('.local-cart-quantity-input');
                var currentquantity = parseInt($quantityinput.val(), 10);

                applyVisibility($card);

                if (licensetype === 'coupon') {
                    if (currentquantity < 1) {
                        $quantityinput.val(1);
                    }
                } else {
                    $quantityinput.val(1);
                }

                updateCartItem(cartitemid, licensetype, parseInt($quantityinput.val(), 10));
            });

            $(document).on('input', '.local-cart-quantity-input', function() {
                debouncedUpdateQuantity($(this));
            });

            $(document).on('change', '.local-cart-quantity-input', function() {
                clearTimeout(debounceTimeout);
                debouncedUpdateQuantity($(this));
            });

            $('.local-cart-item-card').each(function() {
                applyVisibility(this);
            });
            const applyCode = (action, code) => {
                const messageContainer = $('#discount-messages');
                $('#apply-discount-btn, #apply-reseller-btn').prop('disabled', true);
                messageContainer.text('Applying...').removeClass('text-danger text-success');

                $.ajax({
                    url: CART_AJAX_URL,
                    type: 'POST',
                    data: {
                        action: action,
                        code: code,
                        sesskey: SESSKEY
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#overall-total-display').text(response.newtotalformatted);
                            $('#discount-amount-display').text(response.discountformatted);
                            $('#applied-code-name').text(response.codename);
                            $('#discount-section').hide();
                            $('#discount-display').css('display', 'flex');
                            messageContainer.text(response.message).addClass('text-success');
                            toggleCheckoutButtons(response.iszerototal);
                            // --- MODIFIED: Update the payment button's data-cost attribute ---
                            $('#payment-btn').attr('data-cost', response.newtotalraw);
                        } else {
                            messageContainer.text(response.message).addClass('text-danger');
                        }
                        $('#apply-discount-btn, #apply-reseller-btn').prop('disabled', false);
                    },
                    error: function() {
                        notification.alert(getString('failedtoupdatecartitem'), 'alert-danger');
                        messageContainer.text(getString('failedtoupdatecartitem')).addClass('text-danger');
                        $('#apply-discount-btn, #apply-reseller-btn').prop('disabled', false);
                    }
                });
            };

            const removeDiscount = () => {
                 $.ajax({
                    url: CART_AJAX_URL,
                    type: 'POST',
                    data: {
                        action: 'remove_discount',
                        sesskey: SESSKEY
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#overall-total-display').text(response.newtotalformatted);
                            $('#discount-display').hide();
                            $('#discount-section').show();
                            $('#discount-messages').text('');
                            $('#discountcode-input').val('');
                            $('#resellercode-input').val('');
                            toggleCheckoutButtons(response.iszerototal);
                            // --- MODIFIED: Update the payment button's data-cost attribute ---
                            $('#payment-btn').attr('data-cost', response.newtotalraw);
                        }
                    },
                    error: function() {
                         notification.alert(getString('failedtoupdatecartitem'), 'alert-danger');
                    }
                });
            };
            $('#apply-discount-btn').on('click', function() {
                const code = $('#discountcode-input').val().trim();
                if (code) {
                    applyCode('apply_discount_code', code);
                }
            });
            $('#apply-reseller-btn').on('click', function() {
                const code = $('#resellercode-input').val().trim();
                if (code) {
                    applyCode('apply_reseller_code', code);
                }
            });
            $('#remove-discount-link').on('click', function(e) {
                e.preventDefault();
                removeDiscount();
            });
        }
    };
});