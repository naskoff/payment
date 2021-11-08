/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
import 'bootstrap'

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

(function ($) {

    'use strict';

    $('input[name=payment-type]').on('change', function () {

        // hide all button
        $('.modal-footer .btn').not('#btn-close').hide();

        // selected payment type
        const paymentType = $(this).val();

        // get paypal button container
        const paypalButton = $('#paypal-button-container');

        // prepare error handlers
        const errorMessageContainer = $('#error-message');
        const showError = function (error) {
            errorMessageContainer.find('.alert-message').empty().html(error);
            errorMessageContainer.show();
        }
        const clearError = function() {
            errorMessageContainer.empty().hide();
        }

        // on change handler
        if (paymentType === 'paypal') {
            if (paypalButton.html().trim() === '') {
                paypal.Buttons({
                    env: 'sandbox',
                    style: {label: 'pay', color: 'gold', shape: 'pill', tagline: true, layout: 'horizontal'},
                    onInit: function () {
                        paypalButton.show();
                    },
                    createOrder: function (data, actions) {
                        return actions.order.create({purchase_units: [{amount: {value: '0.01', currency: 'USD'}}]});
                    },
                    onApprove: function (data, actions) {
                        clearError();
                        console.log(data, actions);
                        $.post(BASE_URI + 'order/paypal', {orderId: data.orderID}, function (){
                            $('.modal-footer .btn').not('#btn-close').hide();
                            $('.payment-steps').hide();
                            $('#payment-success').show();
                        }).fail((error) => {
                            showError(error);
                        });
                    },
                    onCancel: function (data) {
                        showError(JSON.stringify(data));
                    }
                }).render('#paypal-button-container');
            } else {
                paypalButton.show();
            }
        } else if (paymentType === 'stripe') {
            $('#continue-to-stripe').show();
        }
    });

    $('input[name=payment-type][value=stripe]').prop('checked', true);
})(jQuery);
