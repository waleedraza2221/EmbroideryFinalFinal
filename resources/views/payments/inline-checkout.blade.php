<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secure Payment - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- 2Checkout Inline Cart JavaScript Library -->
    <script>
        (function (document, src, libName, config) {
            var script             = document.createElement('script');
            script.src             = src;
            script.async           = true;
            var firstScriptElement = document.getElementsByTagName('script')[0];
            script.onload          = function () {
                for (var namespace in config) {
                    if (config.hasOwnProperty(namespace)) {
                        window[libName].setup.setConfig(namespace, config[namespace]);
                    }
                }
                window[libName].register();
            };

            firstScriptElement.parentNode.insertBefore(script, firstScriptElement);
        })(document, 'https://secure.2checkout.com/checkout/client/twoCoInlineCart.js', 'TwoCoInlineCart', {
            "app": {
                "merchant": "{{ $paymentData['sellerId'] }}",
                "iframeLoad": "checkout"
            },
            "cart": {
                "host": "{{ config('services.twocheckout.sandbox') ? 'https://sandbox.2checkout.com' : 'https://secure.2checkout.com' }}",
                "customization": "inline-one-step"
            }
        });
    </script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-12">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-6">
            <!-- Header -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Secure Payment</h1>
                <p class="text-gray-600 mt-2">Complete your order payment</p>
            </div>

            <!-- Order Summary -->
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <h3 class="font-semibold text-gray-900 mb-2">Order Summary</h3>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Quote Request #{{ $quoteRequest->id }}</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Service</span>
                    <span class="text-gray-900">{{ $quoteRequest->title }}</span>
                </div>
                <div class="border-t pt-2 mt-2">
                    <div class="flex justify-between items-center font-semibold">
                        <span class="text-gray-900">Total Amount</span>
                        <span class="text-blue-600 text-lg">${{ number_format($payment->amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div id="checkout-container">
                <!-- 2Checkout Inline Checkout will be embedded here -->
                <div id="checkout"></div>
            </div>

            <!-- Loading State -->
            <div id="loading" class="hidden text-center py-4">
                <div class="inline-flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing payment...
                </div>
            </div>

            <!-- Error Display -->
            <div id="error-display" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>

            <!-- Security Info -->
            <div class="mt-6 text-center">
                <div class="flex items-center justify-center space-x-2 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Secured by 2Checkout</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize 2Checkout Inline Cart
        window.onload = function() {
            // Configure the cart
            var cartData = {
                "lineItems": [
                    {
                        "name": "{{ $quoteRequest->title }}",
                        "quantity": 1,
                        "price": {{ $payment->amount }},
                        "type": "product"
                    }
                ],
                "currency": "{{ $payment->currency }}",
                "customer": {
                    "email": "{{ auth()->user()->email }}",
                    "name": "{{ auth()->user()->name }}"
                },
                "merchant": "{{ $paymentData['sellerId'] }}",
                "test": {{ config('services.twocheckout.sandbox') ? 'true' : 'false' }}
            };

            // Initialize the inline checkout
            if (window.TwoCoInlineCart) {
                window.TwoCoInlineCart.cart.setCart(cartData);
                window.TwoCoInlineCart.cart.checkout();
            }

            // Handle payment success
            window.addEventListener('message', function(event) {
                if (event.origin !== '{{ config("services.twocheckout.sandbox") ? "https://sandbox.2checkout.com" : "https://secure.2checkout.com" }}') {
                    return;
                }

                if (event.data.type === 'checkout' && event.data.success) {
                    // Payment successful
                    processPaymentSuccess(event.data);
                }
            });
        };

        function processPaymentSuccess(paymentData) {
            document.getElementById('loading').classList.remove('hidden');
            
            // Send payment confirmation to our server
            fetch('{{ route("payment.process", $payment) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    payment_data: paymentData,
                    order_id: paymentData.order_id || paymentData.orderId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to orders page
                    window.location.href = data.redirect || '{{ route("orders.index") }}';
                } else {
                    showError(data.message || 'Payment verification failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Network error. Please contact support.');
            });
        }

        function showError(message) {
            const loading = document.getElementById('loading');
            const errorDisplay = document.getElementById('error-display');
            
            loading.classList.add('hidden');
            errorDisplay.textContent = message;
            errorDisplay.classList.remove('hidden');
        }
    </script>
</body>
</html>
