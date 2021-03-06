/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
import * as bootstrap from 'bootstrap';

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

let stripe;
let elements;

const initJSScript = function (src, resolve, reject) {
  const script = document.createElement('script');
  script.src = src;
  script.type = 'text/javascript';
  script.onload = () => resolve();
  script.onerror = (e) => reject(e);
  document.head.appendChild(script);
}

const initStripe = new Promise((resolve, reject) => {
  if (typeof stripe === "undefined") {
    initJSScript('https://js.stripe.com/v3/', resolve, reject);
  } else {
    resolve();
  }
});

const initPayPal = new Promise((resolve, reject) => {
  if (typeof paypal === "undefined") {
    initJSScript(`https://www.paypal.com/sdk/js?client-id=${SETTINGS.PAYPAL_CLIENT_ID}`, resolve, reject);
  } else {
    resolve();
  }
});

const selectStep = document.getElementById('select-payment-provider');
const stripeFormStep = document.getElementById('stripe-form');
const successStep = document.getElementById('payment-success');

const initPayPalButton = function () {
  return paypal.Buttons({
    style: {shape: 'rect', color: 'blue', layout: 'horizontal', label: 'paypal'},
    createOrder: function (data, actions) {
      return actions.order.create({
        purchase_units: [{"description": "Boost post", "amount": {"currency_code": "USD", "value": 0.1}}]
      });
    },
    onApprove: function (data, actions) {
      return actions.order.capture().then(function (orderData) {
        // Full available details
        console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));
        // Show a success message within this page, e.g.
        const element = document.getElementById('paypal-button-container');
        element.innerHTML = '';
        element.innerHTML = '<h3>Thank you for your payment!</h3>';
      });
    },
    onError: function (err) {
      console.log(err);
    }
  });
}

const initStripeForm = function (clientSecret) {
  elements = stripe.elements({clientSecret}, {
    theme: 'stripe',
    labels: 'floating',
    fields: {address: 'never'}
  });
  const style = {
    base: {
      color: "#32325d",
      fontFamily: 'Arial, sans-serif',
      fontSmoothing: "antialiased",
      fontSize: "16px",
      "::placeholder": {
        color: "#32325d"
      }
    },
    invalid: {
      fontFamily: 'Arial, sans-serif',
      color: "#fa755a",
      iconColor: "#fa755a"
    }
  };
  const cardNumber = elements.create('cardNumber', {style});
  cardNumber.mount('#cardNumber');
  const cardExpiry = elements.create('cardExpiry', {style});
  cardExpiry.mount('#cardExpiry');
  const cardCvc = elements.create('cardCvc', {style});
  cardCvc.mount('#cardCvc');
  cardNumber.on('change', function (event) {
    $('#stripe-pay-now').attr('disable', event.empty);
    $('#error-message').empty().html(event.error ? event.error.message : '');
  });
  $('#stripe-pay-now').on('click', function (e) {
    e.preventDefault();
    handleSubmit(clientSecret, cardNumber);
  });

  cardNumber.blur();
}

const handleSubmit = function (clientSecret, cardNumber) {
  stripe.confirmCardPayment(clientSecret, {payment_method: {card: cardNumber}}).then((data) => {
    console.log(data);
    if (data.error) {
      $('#error-message').empty().html(data.error ? data.error.message : 'Error occurred');
    } else {
      $.post(SETTINGS.BASE_URI + 'payment-intent/confirm', {paymentIntentId: data.paymentIntent.id})
        .done(function (response) {
          console.log(['response', response]);
        })
        .fail((error) => {
          $('#error-message').empty().html(error ? error : 'Error occurred');
        });
    }
  });
}

const showButtons = function (option) {
  $('#back-to-payment-choice, #continue-to-stripe, #paypal-button-container').hide();
  if (option === 'paypal') {
    $('#paypal-button-container').show();
  } else if (option === 'stripe') {
    $('#continue-to-stripe').show();
  } else if (option === 'stripe-pay-now') {
    $('#stripe-pay-now').show();
  }
}

const PaymentDialog = {
  dialog: null,
  target: $('#donate'),
  changeTitle: function (title) {
    this.target.find('.modal-title').empty().html(title);
    return this;
  },
  init: function () {
    this.dialog = new bootstrap.Modal($('#donate').get(0));
    return this;
  },
  open: function () {
    this.dialog.show();
    return this;
  },
  close: function () {
    this.dialog.close();
    return this;
  }
}

document.getElementById('donate').addEventListener('show.bs.modal', function (e) {
  const paymentType = $('input[name=payment-type]');
  paymentType.on('change', function (e) {
    const option = $(this).val();
    showButtons(option);
  });
  showButtons(paymentType.val());
});

document.getElementById('donate').addEventListener('hide.bs.modal', function (e) {
  const paymentType = $('input[name=payment-type]');
  paymentType.on('change', function (e) {
    const option = $(this).val();
    showButtons(option);
  });
  showButtons(paymentType.val());
});

initPayPal.then(() => {
  initPayPalButton().render('#paypal-button-container');
}).catch(e => console.error(e));

initStripe.then(() => {
  stripe = Stripe(SETTINGS.STRIPE_PUBLISH_KEY);
}).catch(e => console.error(e));

$(function () {
  'use strict';
  $('.btn-donate').on('click', function (e) {
    e.preventDefault();
    PaymentDialog.init().changeTitle('Donate').open();
  });
  $('.btn-boost').on('click', function (e) {
    e.preventDefault();
    PaymentDialog.init().changeTitle('Boost').open();
  });
  $('#continue-to-stripe').on('click', function (e) {
    e.preventDefault();
    $.post(SETTINGS.BASE_URI + 'payment-intent', {amount: '100'}, function (response) {
      const {clientSecret} = response;
      initStripeForm(clientSecret);
      $('.payment-steps').hide();
      $('#stripe-form').show();
      showButtons('stripe-pay-now');
    }, 'json');
  });
});
