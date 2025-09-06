@extends('layouts.dashboard')

@section('title', 'My Orders')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">My Orders</h1>
        <a href="{{ route('quote-requests.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
            New Quote Request
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Orders</h3>
        <form method="GET" action="{{ route('orders.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date From Filter -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Date To Filter -->
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                    Filter
                </button>
                <a href="{{ route('orders.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Filter Results Summary -->
    @if(request()->hasAny(['status', 'date_from', 'date_to']))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-blue-800">Active Filters:</h4>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @if(request('status'))
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                Status: {{ ucfirst(request('status')) }}
                            </span>
                        @endif
                        @if(request('date_from'))
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                From: {{ request('date_from') }}
                            </span>
                        @endif
                        @if(request('date_to'))
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                To: {{ request('date_to') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="text-sm text-blue-600">
                    {{ $orders->total() }} {{ $orders->total() === 1 ? 'order' : 'orders' }} found
                </div>
            </div>
        </div>
    @endif

    @if($orders->count() > 0)
        <div class="grid gap-6">
            @foreach($orders as $order)
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 
                    @if($order->isActive()) border-blue-500
                    @elseif($order->isDelivered()) border-green-500
                    @elseif($order->isCompleted()) border-purple-500
                    @else border-gray-500
                    @endif">
                    
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-800">{{ $order->title }}</h3>
                            <p class="text-sm text-gray-600">{{ $order->order_number }} • Created {{ $order->created_at->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-600">From Quote Request: {{ $order->quoteRequest->request_number ?? 'N/A' }}</p>
                        </div>
                        
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @if($order->isActive()) bg-blue-100 text-blue-800
                            @elseif($order->isDelivered()) bg-green-100 text-green-800
                            @elseif($order->isCompleted()) bg-purple-100 text-purple-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                            <span class="text-sm font-semibold text-green-600">${{ number_format($order->amount, 2) }}</span>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm text-gray-600">{{ $order->delivery_days }} days delivery</span>
                        </div>
                        
                        @if($order->due_date)
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 {{ $order->isOverdue() ? 'text-red-600' : 'text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm {{ $order->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                    Due {{ $order->due_date->format('M d, Y') }}
                                    @if($order->isOverdue())
                                        (Overdue)
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>

                    @if($order->original_files && count($order->original_files) > 0)
                        <div class="mb-4">
                            <span class="text-sm text-gray-600">
                                📎 {{ count($order->original_files) }} original file(s)
                            </span>
                        </div>
                    @endif

                    @if($order->isDelivered() && $order->delivery_files && count($order->delivery_files) > 0)
                        <div class="mb-4 p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center space-x-2 mb-2">
                                <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-medium text-green-800">Order Delivered!</span>
                            </div>
                            <p class="text-sm text-green-700">✅ {{ count($order->delivery_files) }} delivery file(s) available</p>
                            @if($order->delivered_at)
                                <p class="text-xs text-green-600 mt-1">Delivered on {{ $order->delivered_at->format('M d, Y \a\t g:i A') }}</p>
                            @endif
                        </div>
                    @endif

                    @if($order->isCompleted())
                        <div class="mb-4 p-3 bg-purple-50 rounded-lg">
                            <div class="flex items-center space-x-2 mb-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                                <span class="text-sm font-medium text-purple-800">Order Completed</span>
                            </div>
                            @if($order->rating)
                                <div class="flex items-center space-x-1 mt-1">
                                    <span class="text-xs text-purple-700">Your Rating:</span>
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-3 h-3 {{ $i <= $order->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            @if($order->isActive())
                                <span class="text-blue-600">⏳ In Progress</span>
                            @elseif($order->isDelivered())
                                <span class="text-green-600">📦 Ready for Review</span>
                            @elseif($order->isCompleted())
                                <span class="text-purple-600">✅ Completed</span>
                            @endif
                        </div>

                        <a href="{{ route('orders.show', $order) }}" 
                           class="text-blue-600 hover:text-blue-800 font-medium">
                            View Details →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-600 mb-2">No orders yet</h3>
            <p class="text-gray-500 mb-4">Orders will appear here once you accept quotes from your quote requests.</p>
            <a href="{{ route('quote-requests.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                Create Quote Request
            </a>
        </div>
    @endif
</div>
@endsection
