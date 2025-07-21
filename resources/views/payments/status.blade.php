@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Payment Status -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 text-center">
                @if($payment->isCompleted())
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
                    <p class="text-gray-600 mb-6">Your payment has been processed and your order has been created.</p>
                @elseif($payment->isFailed())
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                        <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Failed</h1>
                    <p class="text-gray-600 mb-6">There was an issue processing your payment. Please try again.</p>
                @else
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Pending</h1>
                    <p class="text-gray-600 mb-6">Your payment is being processed. Please wait for confirmation.</p>
                @endif
            </div>
            
            <!-- Payment Details -->
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Payment Details</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Payment ID</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $payment->payment_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Amount</dt>
                        <dd class="mt-1 text-sm text-gray-900">${{ number_format($payment->amount, 2) }} {{ $payment->currency }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                @if($payment->isCompleted()) bg-green-100 text-green-800
                                @elseif($payment->isFailed()) bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </dd>
                    </div>
                    @if($payment->paid_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Paid At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $payment->paid_at->format('M j, Y g:i A') }}</dd>
                        </div>
                    @endif
                    @if($payment->payment_method)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</dd>
                        </div>
                    @endif
                    @if($payment->order_id)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Transaction ID</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $payment->order_id }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
            
            <!-- Project Details -->
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Project Details</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Project Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $payment->quoteRequest->project_type }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Delivery Time</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $payment->quoteRequest->delivery_days }} days</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Instructions</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $payment->quoteRequest->instructions }}</dd>
                    </div>
                </dl>
            </div>
            
            <!-- Actions -->
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <div class="flex justify-center space-x-4">
                    @if($payment->isCompleted())
                        <a href="{{ route('orders.index') }}" 
                           class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition">
                            View My Orders
                        </a>
                    @elseif($payment->isFailed())
                        <a href="{{ route('payment.initiate', $payment->quoteRequest) }}" 
                           class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition">
                            Try Again
                        </a>
                    @endif
                    
                    <a href="{{ route('quote-requests.show', $payment->quoteRequest) }}" 
                       class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 transition">
                        Back to Quote
                    </a>
                </div>
            </div>
        </div>
        
        @if($payment->isCompleted())
            <!-- Success Message -->
            <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">Order Created Successfully</h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>Your order has been created and work will begin shortly. You can track the progress in your orders dashboard.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
