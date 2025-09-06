@extends('layouts.dashboard')

@section('title', 'Order Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">{{ $order->title }}</h1>
                    <p class="text-gray-600 mt-1">{{ $order->order_number }} • Created on {{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                    @if($order->quoteRequest)
                        <p class="text-gray-600">From Quote Request: {{ $order->quoteRequest->request_number }}</p>
                    @endif
                </div>
                
                <span class="px-4 py-2 rounded-full text-sm font-medium
                    @if($order->isActive()) bg-blue-100 text-blue-800
                    @elseif($order->isDelivered()) bg-green-100 text-green-800
                    @elseif($order->isCompleted()) bg-purple-100 text-purple-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Details -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Project Details</h2>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 whitespace-pre-line">{{ $order->instructions }}</p>
                    </div>
                </div>

                <!-- Original Files -->
                @if($order->original_files && count($order->original_files) > 0)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Original Files</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($order->original_files as $index => $file)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            @if(in_array($file['type'], ['image/jpeg', 'image/jpg', 'image/png']))
                                                <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            @else
                                                <svg class="h-8 w-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $file['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($file['size'] / 1024 / 1024, 2) }} MB</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('orders.download-original-file', [$order, $index]) }}" 
                                       class="text-blue-600 hover:text-blue-800">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Delivery Section -->
                @if($order->isDelivered() || $order->isCompleted())
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Delivery</h2>
                        
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <svg class="h-6 w-6 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-green-800">Order Delivered</h3>
                            </div>
                            
                            @if($order->delivered_at)
                                <p class="text-sm text-green-700 mb-4">
                                    Delivered on {{ $order->delivered_at->format('M d, Y \a\t g:i A') }}
                                </p>
                            @endif
                            
                            @if($order->delivery_notes)
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-green-800 mb-2">Delivery Notes:</h4>
                                    <p class="text-sm text-green-700">{{ $order->delivery_notes }}</p>
                                </div>
                            @endif
                            
                            @if($order->delivery_files && count($order->delivery_files) > 0)
                                <div>
                                    <h4 class="text-sm font-medium text-green-800 mb-3">Delivery Files:</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        @foreach($order->delivery_files as $index => $file)
                                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-green-200">
                                                <div class="flex items-center space-x-3">
                                                    <div class="flex-shrink-0">
                                                        @if(in_array($file['type'], ['image/jpeg', 'image/jpg', 'image/png']))
                                                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                        @else
                                                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $file['name'] }}</p>
                                                        <p class="text-xs text-gray-500">{{ number_format($file['size'] / 1024 / 1024, 2) }} MB</p>
                                                    </div>
                                                </div>
                                                <a href="{{ route('orders.download-delivery-file', [$order, $index]) }}" 
                                                   class="text-green-600 hover:text-green-800 font-medium">
                                                    Download
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Customer Feedback -->
                @if($order->isCompleted() && ($order->customer_feedback || $order->rating))
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Your Feedback</h2>
                        
                        @if($order->rating)
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Rating:</h4>
                                <div class="flex items-center space-x-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 {{ $i <= $order->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                    <span class="ml-2 text-sm text-gray-600">({{ $order->rating }}/5)</span>
                                </div>
                            </div>
                        @endif
                        
                        @if($order->customer_feedback)
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Feedback:</h4>
                                <p class="text-gray-700">{{ $order->customer_feedback }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount:</span>
                            <span class="font-semibold text-green-600">${{ number_format($order->amount, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Delivery:</span>
                            <span class="text-gray-900">{{ $order->delivery_days }} days</span>
                        </div>
                        
                        @if($order->due_date)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Due Date:</span>
                                <span class="text-gray-900 {{ $order->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                                    {{ $order->due_date->format('M d, Y') }}
                                </span>
                            </div>
                        @endif
                        
                        @if($order->isActive() && $order->due_date)
                            <div class="pt-3 border-t border-gray-200">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Time Remaining:</span>
                                    <span class="{{ $order->isOverdue() ? 'text-red-600 font-semibold' : 'text-blue-600' }}">
                                        @if($order->isOverdue())
                                            Overdue
                                        @else
                                            {{ $order->due_date->diffForHumans() }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Status Tracker -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Progress</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="ml-3 text-sm text-gray-700">Order Created</span>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-6 h-6 {{ $order->isActive() || $order->isDelivered() || $order->isCompleted() ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                @if($order->isActive() || $order->isDelivered() || $order->isCompleted())
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                @endif
                            </div>
                            <span class="ml-3 text-sm text-gray-700">Work in Progress</span>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-6 h-6 {{ $order->isDelivered() || $order->isCompleted() ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                @if($order->isDelivered() || $order->isCompleted())
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                @endif
                            </div>
                            <span class="ml-3 text-sm text-gray-700">Delivered</span>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-6 h-6 {{ $order->isCompleted() ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                @if($order->isCompleted())
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                @endif
                            </div>
                            <span class="ml-3 text-sm text-gray-700">Completed</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                @if($order->isCompleted() && $order->invoice)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Invoice</h3>
                        <div class="flex space-x-4">
                            <a href="{{ route('invoices.show', $order->invoice) }}" 
                               class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition inline-flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                View Invoice
                            </a>
                            <a href="{{ route('invoices.download', [$order->invoice, 'pdf']) }}" 
                               class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition inline-flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3"></path>
                                </svg>
                                Download PDF
                            </a>
                            <a href="{{ route('invoices.download', [$order->invoice, 'csv']) }}" 
                               class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition inline-flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3"></path>
                                </svg>
                                Download CSV
                            </a>
                        </div>
                    </div>
                @endif

                @if($order->isDelivered() && !$order->isCompleted())
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Complete Order</h3>
                        
                        <form action="{{ route('orders.complete', $order) }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="rating" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rate this order *
                                </label>
                                <select id="rating" name="rating" required 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select rating</option>
                                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                    <option value="4">⭐⭐⭐⭐ Good</option>
                                    <option value="3">⭐⭐⭐ Average</option>
                                    <option value="2">⭐⭐ Poor</option>
                                    <option value="1">⭐ Very Poor</option>
                                </select>
                                @error('rating')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label for="customer_feedback" class="block text-sm font-medium text-gray-700 mb-2">
                                    Feedback (Optional)
                                </label>
                                <textarea id="customer_feedback" name="customer_feedback" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Share your experience with this order...">{{ old('customer_feedback') }}</textarea>
                                @error('customer_feedback')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <button type="submit" 
                                    class="w-full bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition"
                                    onclick="return confirm('Are you sure you want to complete this order? This action cannot be undone.')">
                                Complete Order
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-8">
            <a href="{{ route('orders.index') }}" 
               class="text-gray-600 hover:text-gray-800">
                ← Back to Orders
            </a>
        </div>
    </div>
</div>
@endsection
