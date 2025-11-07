<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Payment | PayPal</title>

<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=AV7e6OP3djKi3Fth62XHLGr5p1HVV9AZEtAj5jWpCORMLDfMt5xhVEqh026_z-8So-M5x1sF2J8r9Yc9&currency=USD"></script>

<!-- Google Pay JS -->
<script async src="https://pay.google.com/gp/p/js/pay.js" onload="onGooglePayLoaded()"></script>

<style>
body {
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Inter', sans-serif;
}
.pay-card {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  padding: 2rem;
  width: 400px;
  max-width: 90%;
  animation: fadeIn 0.5s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.gpay-custom-btn {
  background-color: #000;
  color: white;
  font-weight: 500;
  border: none;
  border-radius: 0.5rem;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  width: 100%;
  cursor: pointer;
  transition: 0.2s ease-in-out;
}
.gpay-custom-btn:hover {
  background-color: #1f1f1f;
  transform: scale(1.02);
}
.gpay-custom-btn img {
  width: 24px;
  height: 24px;
}
.input-box {
  width: 100%;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
  text-align: center;
  margin-bottom: 20px;
}
</style>
</head>

<body>
<div class="pay-card text-center">
  <h2 class="text-2xl font-semibold text-gray-800 mb-2">Phone Activation</h2>
  <p class="text-gray-500 mb-6">Enter your device ID and payment amount</p>

  <!-- Device Token Input -->
  <input type="text" id="device_token" class="input-box" placeholder="Enter your Device ID">

  <!-- Amount Input -->
  <input type="number" id="amount" class="input-box" placeholder="Enter amount in USD" min="1" step="0.01">

  <!-- PayPal button -->
  <div id="paypal-button-container" class="mb-6"></div>

  <div class="relative flex items-center justify-center mb-6">
    <div class="flex-grow border-t border-gray-300"></div>
    <span class="mx-3 text-gray-400">or</span>
    <div class="flex-grow border-t border-gray-300"></div>
  </div>

  <!-- Google Pay button -->
  <!-- <button id="custom-google-pay" class="gpay-custom-btn mb-4">
    <img src="googlepay.png" alt="Google Pay logo">
    <span>Pay with Google Pay</span>
  </button> -->

  <p class="text-xs text-gray-400 mt-6">
    Payments are processed securely via PayPal & Google Pay
  </p>
</div>

<script>
/* --------------------------
 * Utility: get user inputs
 * -------------------------- */
function getInputs() {
  const deviceToken = document.getElementById('device_token').value.trim();
  const amountValue = parseFloat(document.getElementById('amount').value);

  if (!deviceToken) {
    alert("Please enter your device token!");
    return null;
  }
  if (!amountValue || amountValue <= 0) {
    alert("Please enter a valid amount!");
    return null;
  }
  return { deviceToken, amount: amountValue.toFixed(2) };
}

/* --------------------------
 * PAYPAL PAYMENT
 * -------------------------- */
paypal.Buttons({
  style: { layout: 'vertical', color: 'gold', shape: 'pill', label: 'paypal' },
  createOrder: function(data, actions) {
    const inputs = getInputs();
    if (!inputs) return actions.reject();
    return actions.order.create({ purchase_units: [{ amount: { value: inputs.amount } }] });
  },
  // onApprove: function(data, actions) {
  //   const inputs = getInputs();
  //   return actions.order.capture().then(function(details) {
  //     alert('✅ PayPal payment completed by ' + details.payer.name.given_name);
  //     window.location.href = `success.php?orderID=${data.orderID}&amount=${inputs.amount}&device_token=${encodeURIComponent(inputs.deviceToken)}`;
  //   });
  // },
  onApprove: function(data, actions) {
  const inputs = getInputs();
  return actions.order.capture().then(function(details) {
    const payerName = details.payer.name.given_name + " " + details.payer.name.surname;
    const payerEmail = details.payer.email_address;

    alert('✅ PayPal payment completed by ' + payerName);

    // Redirect and include payer details
    window.location.href = `success.php?orderID=${data.orderID}&amount=${inputs.amount}&device_token=${encodeURIComponent(inputs.deviceToken)}&payer_name=${encodeURIComponent(payerName)}&payer_email=${encodeURIComponent(payerEmail)}&method=paypal`;
  });
},

  onError: function(err) {
    console.error(err);
    alert('❌ PayPal payment failed.');
  }
}).render('#paypal-button-container');

/* --------------------------
 * GOOGLE PAY PAYMENT
 * -------------------------- */
const baseRequest = { apiVersion: 2, apiVersionMinor: 0 };
const allowedCardNetworks = ["VISA", "MASTERCARD"];
const allowedCardAuthMethods = ["PAN_ONLY", "CRYPTOGRAM_3DS"];
const tokenizationSpecification = {
  type: 'PAYMENT_GATEWAY',
  parameters: { 'gateway': 'example', 'gatewayMerchantId': 'exampleMerchantId' }
};
const baseCardPaymentMethod = {
  type: 'CARD',
  parameters: { allowedAuthMethods: allowedCardAuthMethods, allowedCardNetworks: allowedCardNetworks }
};
const cardPaymentMethod = Object.assign({ tokenizationSpecification }, baseCardPaymentMethod);
let paymentsClient = null;
function getGooglePaymentsClient() {
  if (!paymentsClient) paymentsClient = new google.payments.api.PaymentsClient({ environment: 'TEST' });
  return paymentsClient;
}
function getPaymentDataRequest(amount) {
  const request = Object.assign({}, baseRequest);
  request.allowedPaymentMethods = [cardPaymentMethod];
  request.transactionInfo = { totalPriceStatus: 'FINAL', totalPrice: amount, currencyCode: 'USD' };
  request.merchantInfo = { merchantName: 'Demo Merchant' };
  return request;
}
function onGooglePayLoaded() {
  const client = getGooglePaymentsClient();
  client.isReadyToPay({ apiVersion: 2, apiVersionMinor: 0, allowedPaymentMethods: [baseCardPaymentMethod] })
    .then(response => {
      if (!response.result) document.getElementById('custom-google-pay').style.display = 'none';
    }).catch(err => console.error('Error loading Google Pay:', err));
}

document.getElementById('custom-google-pay').addEventListener('click', function() {
  const inputs = getInputs();
  if (!inputs) return;
  const request = getPaymentDataRequest(inputs.amount);
  const client = getGooglePaymentsClient();
  client.loadPaymentData(request)
    .then(paymentData => {
      console.log("Payment data:", paymentData);
      alert('✅ Google Pay Payment Successful!');
      window.location.href = `success.php?method=googlepay&amount=${inputs.amount}&device_token=${encodeURIComponent(inputs.deviceToken)}`;
    })
    .catch(err => {
      console.error('Google Pay failed:', err);
      alert('⚠️ Google Pay canceled or failed.');
    });
});
</script>
</body>
</html>
