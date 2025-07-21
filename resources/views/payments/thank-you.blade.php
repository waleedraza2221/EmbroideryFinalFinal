@extends('layouts.app')

@section('title', 'Payment Complete')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        @if($status === 'success')
            <div class="bg-green-50 border border-green-200 rounded-lg p-8 text-center">
                <div class="flex justify-center mb-4">
                    <svg class="h-16 w-16 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-green-800 mb-4">Payment Successful!</h1>
                <p class="text-lg text-green-700 mb-6">{{ $message }}</p>
                
                @if(isset($quote_request))
                    <div class="bg-white border border-green-200 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Order Details</h3>
                        <p class="text-gray-600"><strong>Project:</strong> {{ $quote_request->title }}</p>
                        <p class="text-gray-600"><strong>Amount:</strong> ${{ number_format($quote_request->quoted_amount, 2) }}</p>
                        <p class="text-gray-600"><strong>Delivery:</strong> {{ $quote_request->delivery_days }} days</p>
                    </div>
                @endif
                
                <div class="space-y-3">
                    <a href="{{ route('orders.index') }}" 
                       class="inline-block bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700 transition">
                        View My Orders
                    </a>
                    <br>
                    <a href="{{ route('dashboard') }}" 
                       class="inline-block text-green-600 hover:text-green-800">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        @elseif($status === 'pending')
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-8 text-center">
                <div class="flex justify-center mb-4">
                    <svg class="h-16 w-16 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-yellow-800 mb-4">Payment Processing</h1>
                <p class="text-lg text-yellow-700 mb-6">{{ $message }}</p>
                
                <div class="space-y-3">
                    <a href="{{ route('quote-requests.index') }}" 
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
