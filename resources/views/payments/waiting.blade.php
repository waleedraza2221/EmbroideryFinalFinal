@extends('layouts.app')

@section('title', 'Payment Processing')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            
            @if($status === 'processing')
                <!-- Payment Confirmed -->
                <div class="mb-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-green-800 mb-2">Payment Confirmed!</h1>
                    <p class="text-gray-600">{{ $message }}</p>
                </div>

                <!-- Payment Details -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-green-800 mb-4">Payment Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                        <div>
                            <p class="text-sm text-green-600">Quote Request</p>
                            <p class="font-semibold">{{ $quoteRequest->title }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-green-600">Amount Paid</p>
                            <p class="font-semibold">${{ number_format($quoteRequest->quoted_amount, 2) }}</p>
                        </div>
                        @isset($orderNumber)
                        <div>
                            <p class="text-sm text-green-600">Order Number</p>
                            <p class="font-semibold">{{ $orderNumber }}</p>
                        </div>
                        @endisset
                        @isset($invoiceId)
                        <div>
                            <p class="text-sm text-green-600">Invoice ID</p>
                            <p class="font-semibold">{{ $invoiceId }}</p>
                        </div>
                        @endisset
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="text-left mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">What happens next?</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-600">
                        <li>Your order has been created and is now active</li>
                        <li>Our team will start working on your embroidery project</li>
                        <li>You'll receive email updates about your order progress</li>
                        <li>Estimated delivery: {{ $quoteRequest->delivery_days }} business days</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('orders.index') }}" 
                       class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition">
                        View My Orders
                    </a>
                    <a href="{{ route('quote-requests.index') }}" 
                       class="bg-gray-600 text-white px-6 py-3 rounded-md hover:bg-gray-700 transition">
                        Back to Quotes
                    </a>
                </div>

            @else
                <!-- Waiting for Payment -->
                <div class="mb-6">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-yellow-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-yellow-800 mb-2">Processing Payment</h1>
                    <p class="text-gray-600">{{ $message }}</p>
                </div>

                <!-- Quote Details -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quote Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                        <div>
                            <p class="text-sm text-gray-600">Project</p>
                            <p class="font-semibold">{{ $quoteRequest->title }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Amount</p>
                            <p class="font-semibold">${{ number_format($quoteRequest->quoted_amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Delivery</p>
                            <p class="font-semibold">{{ $quoteRequest->delivery_days }} business days</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="font-semibold">{{ ucfirst($quoteRequest->status) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="text-left mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Please wait...</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-600">
                        <li>Your payment is being processed by 2Checkout</li>
                        <li>This page will automatically update when payment is confirmed</li>
                        <li>Please do not close this window or go back</li>
                        <li>If you experience any issues, please contact our support team</li>
                    </ul>
                </div>

                <!-- Auto-refresh -->
                <script>
                    // Auto-refresh the page every 10 seconds to check for payment confirmation
                    setTimeout(function() {
                        window.location.reload();
                    }, 10000);
                </script>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button onclick="window.location.reload()" 
                            class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition">
                        Check Payment Status
                    </button>
                    <a href="{{ route('quote-requests.show', $quoteRequest) }}" 
                       class="bg-gray-600 text-white px-6 py-3 rounded-md hover:bg-gray-700 transition">
                        Back to Quote
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
