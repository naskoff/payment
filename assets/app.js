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

let stripe;

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

document.getElementById('donate').addEventListener('show.bs.modal', function (e) {
    console.log('loaded dialog', stripe, paypal);
});

$(function () {
  'use strict';
  initStripe.then(() => {
    stripe = Stripe(SETTINGS.STRIPE_PUBLISH_KEY);
  }).catch(e => console.error(e));
  initPayPal.then(() => {
  }).catch(e => console.error(e));
});
