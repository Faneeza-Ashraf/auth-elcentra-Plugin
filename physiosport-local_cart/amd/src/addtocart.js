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

define(['jquery', 'core/str', 'core/templates', 'theme_boost/bootstrap/modal', 'local_cart/cart_icon_update'], function($, Str, Templates, Modal, cartIconUpdate) {
    var _errorModalPromise;
    var _successModalPromise;

    // --- Function to render the ERROR modal ---
    var fetchAndRenderErrorModal = function() {
        if (!_errorModalPromise) {
            _errorModalPromise = Templates.render('local_cart/error_modal', {})
                .then(function(html) {
                    var $modal = $(html);
                    $('body').append($modal);
                    return $modal[0];
                });
        }
        return _errorModalPromise;
    };

    // --- NEW: Function to render the SUCCESS modal ---
    var fetchAndRenderSuccessModal = function(context) {
        // We don't cache the success modal promise because its content is dynamic.
        return Templates.render('local_cart/success_modal', context)
            .then(function(html) {
                // Remove any previous success modal to prevent duplicates.
                $('#addToCartSuccessModal').remove();
                var $modal = $(html);
                $('body').append($modal);
                return $modal[0];
            });
    };

    var init = function() {
        $('.add-to-cart-btn').on('click', function(event) {
            event.preventDefault();
            var $button = $(this);
            var courseId = $button.data('course-id');
            $button.prop('disabled', true).text('ADD TO CART');

            $.ajax({
                url: M.cfg.wwwroot + '/local/cart/ajax.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    course_id: courseId,
                    action: 'add_to_cart',
                    sesskey: M.cfg.sesskey
                }
            }).done(function(response) {
                if (response.status === 'error' && response.code === 'already_in_cart') {
                    // --- Handle ERROR with custom modal ---
                    fetchAndRenderErrorModal().then(function(modalElement) {
                        var modalInstance = new Modal(modalElement);
                        modalInstance.show();
                    });
                    $button.prop('disabled', false).text('ADD TO CART');

                } else if (response.status === 'success') {
                    // --- MODIFIED: Handle SUCCESS with new custom modal ---
                    var successContext = {
                        title: 'Success', // You can make this a lang string
                        body: response.message,
                        //viewcarturl: M.cfg.wwwroot + '/local/cart/index.php',
                        //continueurl: window.location.href // This will refresh the current page
                    };
                    fetchAndRenderSuccessModal(successContext).then(function(modalElement) {
                        var modalInstance = new Modal(modalElement);
                        modalInstance.show();
                    });
                    $button.text('ADD TO CART').prop('disabled', true);
                    cartIconUpdate.update();
                    // --- END MODIFICATION ---

                } else {
                    alert(response.message || 'Unknown error occurred.');
                    $button.prop('disabled', false).text('ADD TO CART');
                }
            }).fail(function(xhr) {
                alert('AJAX Failed! ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unknown error.'));
                $button.prop('disabled', false).text('ADD TO CART');
            });
        });
    };

    return {
        init: init
    };
});