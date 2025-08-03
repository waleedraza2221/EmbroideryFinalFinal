@extends('layouts.app')

@section('title', 'Payment Confirmation - Thank You!')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            @if($hasPaymentData)
                <!-- Success State -->
                <div class="mb-6">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Thank You for Your Payment!</h1>
                    <p class="text-lg text-gray-600">Your payment has been received and is being processed.</p>
                </div>

                <!-- Payment Details -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Payment Details</h2>
                    <div class="space-y-3 text-left">
                        @if($orderNumber)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order Number:</span>
                                <span class="font-semibold text-gray-900">#{{ $orderNumber }}</span>
                            </div>
                        @endif
                        
                        @if($invoiceId)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Invoice ID:</span>
                                <span class="font-semibold text-gray-900">{{ $invoiceId }}</span>
                            </div>
                        @endif
                        
                        @if($total)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Amount Paid:</span>
                                <span class="font-semibold text-green-600">${{ number_format($total, 2) }}</span>
                            </div>
                        @endif
                        
                        @if($quoteRequest)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Project:</span>
                                <span class="font-semibold text-gray-900">{{ $quoteRequest->title }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Processing Message -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Payment Processing</h3>
                            <p class="text-sm text-blue-700 mt-1">
                                We are verifying your payment and will create your order shortly. You will receive a confirmation email once everything is processed.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Auto Redirect Script -->
                <script>
                    // Auto redirect to dashboard after 10 seconds if user is logged in
                    @auth
                        let countdown = 10;
                        const countdownElement = document.createElement('p');
                        countdownElement.className = 'text-sm text-gray-500 mt-4';
                        countdownElement.innerHTML = `Redirecting to your dashboard in <span id="countdown">${countdown}</span> seconds...`;
                        document.querySelector('.bg-white').appendChild(countdownElement);
                        
                        const interval = setInterval(() => {
                            countdown--;
                            document.getElementById('countdown').textContent = countdown;
                            
                            if (countdown <= 0) {
                                clearInterval(interval);
                                window.location.href = '{{ route("dashboard") }}';
                            }
                        }, 1000);
                    @endauth
                </script>

            @else
                <!-- No Payment Data State -->
                <div class="mb-6">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.98-.833-2.75 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Status Unknown</h1>
                    <p class="text-lg text-gray-600">We couldn't find payment information for this session.</p>
                </div>

                @if(isset($error))
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <p class="text-red-700">{{ $error }}</p>
                    </div>
                @endif
            @endif

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
                @auth
                    <a href="{{ route('dashboard') }}" 
                       class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        Go to Dashboard
                    </a>
                    
                    @if($quoteRequest)
                        <a href="{{ route('quote-requests.show', $quoteRequest) }}" 
                           class="bg-gray-600 text-white px-6 py-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                            View Quote Request
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" 
                       class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        Login to Your Account
                    </a>
                @endauth
                
                <a href="{{ route('home') }}" 
                   class="bg-gray-300 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                    Return to Home
                </a>
            </div>

            <!-- Additional Information -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">What Happens Next?</h3>
                <div class="text-left space-y-2 text-gray-600">
                    <p>• We will verify your payment with our payment processor</p>
                    <p>• Your order will be created and you'll receive a confirmation email</p>
                    <p>• Our team will begin working on your embroidery digitization</p>
                    <p>• You'll be notified when your order is ready for delivery</p>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600">
                    If you have any questions about your payment or order, please don't hesitate to 
                    <a href="mailto:support@embroiderydigitize.com" class="text-blue-600 hover:text-blue-800">contact our support team</a>.
                </p>
            </div>
        </div>
    </div>
</div>

@if($hasPaymentData)
<!-- Payment verification script -->
<script>
    // Log payment data received for debugging
    console.log('Payment data received:', {
        orderNumber: '{{ $orderNumber ?? "N/A" }}',
        invoiceId: '{{ $invoiceId ?? "N/A" }}',
        merchantOrderId: '{{ $merchantOrderId ?? "N/A" }}',
        total: '{{ $total ?? "N/A" }}',
        key: '{{ $key ?? "N/A" }}'
    });

    // Redirect to localhost with parameters for development
    const params = new URLSearchParams({
        order_number: '{{ $orderNumber ?? "" }}',
        invoice_id: '{{ $invoiceId ?? "" }}',
        merchant_order_id: '{{ $merchantOrderId ?? "" }}',
        total: '{{ $total ?? "" }}',
        key: '{{ $key ?? "" }}'
    });
    
    // Redirect to localhost thank you page with parameters
    setTimeout(() => {
        window.location.href = 'http://localhost:8000/thank-you?' + params.toString();
    }, 2000); // 2 second delay to show the page first
</script>
@endif
@endsection 
                       class="inline-block bg-yellow-600 text-white px-6 py-3 rounded-md hover:bg-yellow-700 transition">
                        View My Quotes
                    </a>
                    <br>
                    <a href="{{ route('dashboard') }}" 
                       class="inline-block text-yellow-600 hover:text-yellow-800">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        @else
            <div class="bg-red-50 border border-red-200 rounded-lg p-8 text-center">
                <div class="flex justify-center mb-4">
                    <svg class="h-16 w-16 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-red-800 mb-4">Payment Error</h1>
                <p class="text-lg text-red-700 mb-6">{{ $message }}</p>
                
                <div class="space-y-3">
                    <a href="{{ route('quote-requests.index') }}" 
                       class="inline-block bg-red-600 text-white px-6 py-3 rounded-md hover:bg-red-700 transition">
                        Try Again
                    </a>
                    <br>
                    <a href="{{ route('dashboard') }}" 
                       class="inline-block text-red-600 hover:text-red-800">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
