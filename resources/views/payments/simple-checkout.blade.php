@extends('layouts.app')

@section('title', 'Payment Checkout')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-2xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h1 class="text-2xl font-bold text-gray-900">Complete Your Payment</h1>
                <p class="mt-1 text-sm text-gray-600">Secure payment processing</p>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Order Summary</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Project</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $quoteRequest->title }}</dd>
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
                        <dd class="text-2xl font-bold text-green-600">${{ number_format($quoteRequest->quoted_amount, 2) }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Options -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Payment Method</h3>
                <p class="mt-1 text-sm text-gray-600">Choose your preferred payment method</p>
            </div>
            <div class="px-4 py-5 sm:p-6 space-y-4">
                
                <!-- 2Checkout Option -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-lg font-medium text-gray-900">Credit/Debit Card</h4>
                            <p class="text-sm text-gray-500">Secure payment via 2Checkout</p>
                        </div>
                        <div class="flex space-x-2">
                            <svg class="h-8 w-12" viewBox="0 0 48 30" fill="none">
                                <rect width="48" height="30" rx="4" fill="#1A1F71"/>
                                <text x="24" y="20" font-family="Arial" font-size="10" font-weight="bold" text-anchor="middle" fill="white">VISA</text>
                            </svg>
                            <svg class="h-8 w-12" viewBox="0 0 48 30" fill="none">
                                <rect width="48" height="30" rx="4" fill="#EB001B"/>
                                <circle cx="18" cy="15" r="9" fill="#EB001B"/>
                                <circle cx="30" cy="15" r="9" fill="#FF5F00"/>
                                <circle cx="24" cy="15" r="9" fill="#F79E1B"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Simple 2Checkout Button -->
                    <button id="pay-with-2checkout" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        Pay ${{ number_format($quoteRequest->quoted_amount, 2) }} with 2Checkout
                    </button>
                </div>

                <!-- Mock Payment for Testing -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-lg font-medium text-gray-900">Test Payment</h4>
                            <p class="text-sm text-gray-500">For development/testing purposes only</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            TEST MODE
                        </span>
                    </div>
                    
                    <form action="{{ route('payment.mock', $quoteRequest) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
                            Complete Test Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Back Link -->
        <div class="text-center">
            <a href="{{ route('quote-requests.show', $quoteRequest) }}" 
               class="text-gray-600 hover:text-gray-900 text-sm">
                ← Back to Quote Request
            </a>
        </div>
    </div>
</div>

<!-- 2Checkout JavaScript -->
<script src="https://secure.2checkout.com/checkout/client/twoCoInlineCart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Simple 2Checkout implementation
    document.getElementById('pay-with-2checkout').addEventListener('click', function() {
        TwoCoInlineCart.products.add({
            code: "74B8E17CC0"
        });
        TwoCoInlineCart.cart.setTest(true);
        TwoCoInlineCart.cart.checkout();
    });
});
</script>
@endsection
