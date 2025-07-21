@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->id }}</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage order details and delivery</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.orders.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition">
                        Back to Orders
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition">
                        Dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Information -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Order Information</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Order ID</dt>
                                <dd class="mt-1 text-sm text-gray-900">#{{ $order->id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($order->status === 'active') bg-blue-100 text-blue-800
                                        @elseif($order->status === 'delivered') bg-green-100 text-green-800
                                        @elseif($order->status === 'completed') bg-purple-100 text-purple-800
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($order->amount, 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Delivery Days</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->delivery_days }} days</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('M j, Y g:i A') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @php
                                        $dueDate = $order->created_at->addDays($order->delivery_days);
                                        $isOverdue = $dueDate->isPast() && $order->status === 'active';
                                    @endphp
                                    <span class="{{ $isOverdue ? 'text-red-600 font-medium' : '' }}">
                                        {{ $dueDate->format('M j, Y') }}
                                    </span>
                                    @if($isOverdue)
                                        <span class="text-xs text-red-500 block">Overdue</span>
                                    @endif
                                </dd>
                            </div>
                            @if($order->delivered_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Delivered</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $order->delivered_at->format('M j, Y g:i A') }}</dd>
                                </div>
                            @endif
                            @if($order->completed_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Completed</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $order->completed_at->format('M j, Y g:i A') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Project Details -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Project Details</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Project Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->quoteRequest->project_type }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Instructions</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->quoteRequest->instructions }}</dd>
                            </div>
                            @if($order->quoteRequest->additional_requirements)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Additional Requirements</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $order->quoteRequest->additional_requirements }}</dd>
                                </div>
                            @endif
                            @if($order->admin_notes)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Admin Notes</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $order->admin_notes }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Original Files -->
                @if($order->original_files && count($order->original_files) > 0)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Original Files</h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($order->original_files as $index => $file)
                                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $file['name'] }}</p>
                                                <p class="text-sm text-gray-500">{{ round($file['size'] / 1024, 2) }} KB</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.orders.download-original-file', [$order, $index]) }}" 
                                           class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                                            Download
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Delivery Files -->
                @if($order->delivery_files && count($order->delivery_files) > 0)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Delivery Files</h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($order->delivery_files as $index => $file)
                                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $file['name'] }}</p>
                                                <p class="text-sm text-gray-500">{{ round($file['size'] / 1024, 2) }} KB</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.orders.download-delivery-file', [$order, $index]) }}" 
                                           class="text-green-600 hover:text-green-500 text-sm font-medium">
                                            Download
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                            @if($order->delivery_notes)
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <dt class="text-sm font-medium text-gray-500">Delivery Notes</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $order->delivery_notes }}</dd>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Customer Feedback -->
                @if($order->isCompleted() && ($order->rating || $order->customer_feedback))
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Customer Feedback</h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            @if($order->rating)
                                <div class="mb-4">
                                    <dt class="text-sm font-medium text-gray-500">Rating</dt>
                                    <dd class="mt-1 flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-5 h-5 {{ $i <= $order->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                        <span class="ml-2 text-sm text-gray-600">({{ $order->rating }}/5)</span>
                                    </dd>
                                </div>
                            @endif
                            @if($order->customer_feedback)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Feedback</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $order->customer_feedback }}</dd>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Delivery Form -->
                @if($order->status === 'active')
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Deliver Order</h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <form action="{{ route('admin.orders.deliver', $order) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                
                                <div class="space-y-6">
                                    <div>
                                        <label for="delivery_files" class="block text-sm font-medium text-gray-700">Delivery Files *</label>
                                        <input type="file" name="delivery_files[]" id="delivery_files" multiple required
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <p class="mt-1 text-sm text-gray-500">Upload completed work files. Max 20MB per file. Any file type is supported.</p>
                                        @error('delivery_files.*')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="delivery_notes" class="block text-sm font-medium text-gray-700">Delivery Notes (Optional)</label>
                                        <textarea name="delivery_notes" id="delivery_notes" rows="4" 
                                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                                  placeholder="Additional notes about the delivered work..."></textarea>
                                        @error('delivery_notes')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div class="flex justify-end">
                                        <button type="submit" 
                                                class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition">
                                            Mark as Delivered
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Customer Information -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Customer</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12">
                                <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-lg font-medium text-gray-700">
                                        {{ strtoupper(substr($order->customer->name, 0, 2)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">{{ $order->customer->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $order->customer->email }}</p>
                                @if($order->customer->phone)
                                    <p class="text-sm text-gray-500">{{ $order->customer->phone }}</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <dt class="text-sm font-medium text-gray-500">Member since</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $order->customer->created_at->format('M j, Y') }}</dd>
                        </div>
                    </div>
                </div>

                <!-- Order Timeline -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Timeline</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li>
                                    <div class="relative pb-8">
                                        <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                                        <div class="relative flex space-x-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-500 ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div>
                                                    <p class="text-sm text-gray-500">Order created</p>
                                                    <p class="text-xs text-gray-400">{{ $order->created_at->format('M j, Y g:i A') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                
                                @if($order->delivered_at)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$order->completed_at)
                                                <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-500 ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div>
                                                        <p class="text-sm text-gray-500">Order delivered</p>
                                                        <p class="text-xs text-gray-400">{{ $order->delivered_at->format('M j, Y g:i A') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                                
                                @if($order->completed_at)
                                    <li>
                                        <div class="relative">
                                            <div class="relative flex space-x-3">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-500 ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div>
                                                        <p class="text-sm text-gray-500">Order completed</p>
                                                        <p class="text-xs text-gray-400">{{ $order->completed_at->format('M j, Y g:i A') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
