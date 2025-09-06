@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h1 class="text-2xl font-bold text-gray-900">Complete Your Payment</h1>
                <p class="mt-1 text-sm text-gray-600">Secure payment processing powered by 2Checkout</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Order Summary -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Order Summary</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Project Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quoteRequest->project_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Instructions</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ Str::limit($quoteRequest->instructions, 200) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Delivery Time</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quoteRequest->delivery_days }} days</dd>
                        </div>
                        @if($quoteRequest->quote_notes)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $quoteRequest->quote_notes }}</dd>
                            </div>
                        @endif
                    </dl>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <dt class="text-lg font-medium text-gray-900">Total Amount</dt>
                            <dd class="text-2xl font-bold text-green-600">${{ number_format($payment->amount, 2) }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Payment Information</h3>
                    <p class="mt-1 text-sm text-gray-600">Your payment is secured with SSL encryption</p>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <form id="payment-form" action="{{ route('payment.process', $payment) }}" method="POST">
                        @csrf
                        <input type="hidden" id="token" name="token" />
                        
                        <div class="space-y-6">
                            <div>
                                <label for="ccNo" class="block text-sm font-medium text-gray-700">Card Number</label>
                                <input type="text" id="ccNo" placeholder="4111111111111111" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="expMonth" class="block text-sm font-medium text-gray-700">Expiry Month</label>
                                    <select id="expMonth" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                        <option value="">Month</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label for="expYear" class="block text-sm font-medium text-gray-700">Expiry Year</label>
                                    <select id="expYear" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                        <option value="">Year</option>
                                        @for($i = date('Y'); $i <= date('Y') + 10; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label for="cvv" class="block text-sm font-medium text-gray-700">Security Code (CVV)</label>
                                <input type="text" id="cvv" placeholder="123" maxlength="4"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-6">
                                <button type="submit" id="submit-payment" 
                                        class="w-full bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 font-medium">
                                    <span id="payment-text">Pay ${{ number_format($payment->amount, 2) }}</span>
                                    <span id="payment-loading" class="hidden">Processing...</span>
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <p class="text-xs text-gray-500">
                                    <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                    Your payment information is encrypted and secure
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Back Link -->
        <div class="mt-6 text-center">
            <a href="{{ route('quote-requests.show', $quoteRequest) }}" 
               class="text-gray-600 hover:text-gray-900 text-sm">
                ← Back to Quote Request
            </a>
        </div>
    </div>
</div>

<!-- 2Checkout JavaScript -->
<script src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configure 2Checkout
    TCO.loadPubKey('{{ config('services.twocheckout.sandbox') ? 'sandbox' : 'production' }}', function() {
        console.log('2Checkout public key loaded');
    });

    // Handle form submission
    document.getElementById('payment-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitButton = document.getElementById('submit-payment');
        const paymentText = document.getElementById('payment-text');
        const paymentLoading = document.getElementById('payment-loading');
        
        // Disable button and show loading
        submitButton.disabled = true;
        paymentText.classList.add('hidden');
        paymentLoading.classList.remove('hidden');
        
        // Get token from 2Checkout
        TCO.requestToken(function(data) {
            if (data.response.responseCode === 'APPROVED') {
                document.getElementById('token').value = data.response.token;
                document.getElementById('payment-form').submit();
            } else {
                alert('Payment failed: ' + data.response.responseMsg);
                // Re-enable button
                submitButton.disabled = false;
                paymentText.classList.remove('hidden');
                paymentLoading.classList.add('hidden');
            }
        }, function(error) {
            alert('Payment error: ' + error.errorMsg);
            // Re-enable button
            submitButton.disabled = false;
            paymentText.classList.remove('hidden');
            paymentLoading.classList.add('hidden');
        }, {
            sellerId: "{{ config('services.twocheckout.account_number') }}",
            publishableKey: "{{ config('services.twocheckout.account_number') }}",
            ccNo: document.getElementById('ccNo').value,
            cvv: document.getElementById('cvv').value,
            expMonth: document.getElementById('expMonth').value,
            expYear: document.getElementById('expYear').value
        });
    });
});
</script>
@endsection
