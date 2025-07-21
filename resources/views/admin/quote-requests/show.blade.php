@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Quote Request Details</h1>
                    <p class="mt-1 text-sm text-gray-600">Review and respond to customer request</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.quote-requests.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition">
                        Back to List
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
                <!-- Request Information -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Request Information</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Project Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $quoteRequest->project_type }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($quoteRequest->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($quoteRequest->status === 'quoted') bg-green-100 text-green-800
                                        @elseif($quoteRequest->status === 'accepted') bg-blue-100 text-blue-800
                                        @elseif($quoteRequest->status === 'rejected') bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($quoteRequest->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Instructions</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $quoteRequest->instructions }}</dd>
                            </div>
                            @if($quoteRequest->additional_requirements)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Additional Requirements</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $quoteRequest->additional_requirements }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Submitted</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $quoteRequest->created_at->format('M j, Y g:i A') }}</dd>
                            </div>
                            @if($quoteRequest->quoted_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Quoted</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $quoteRequest->quoted_at->format('M j, Y g:i A') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Files -->
                @if($quoteRequest->files && count($quoteRequest->files) > 0)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Uploaded Files</h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($quoteRequest->files as $index => $file)
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
                                        <a href="{{ route('admin.quote-requests.download-file', [$quoteRequest, $index]) }}" 
                                           class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                                            Download
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Quote Response -->
                @if($quoteRequest->status === 'pending')
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Provide Quote</h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <form action="{{ route('admin.quote-requests.update', $quoteRequest) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="grid grid-cols-1 gap-6">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="quoted_amount" class="block text-sm font-medium text-gray-700">Quote Amount ($)</label>
                                            <input type="number" step="0.01" min="1" name="quoted_amount" id="quoted_amount" 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                                   placeholder="0.00" required>
                                            @error('quoted_amount')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label for="delivery_days" class="block text-sm font-medium text-gray-700">Delivery Days</label>
                                            <input type="number" min="1" max="365" name="delivery_days" id="delivery_days" 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                                   placeholder="7" required>
                                            @error('delivery_days')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="quote_notes" class="block text-sm font-medium text-gray-700">Quote Notes (Optional)</label>
                                        <textarea name="quote_notes" id="quote_notes" rows="4" 
                                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                                  placeholder="Additional details about the quote..."></textarea>
                                        @error('quote_notes')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div class="flex justify-end space-x-3">
                                        <button type="button" onclick="showRejectModal()" 
                                                class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition">
                                            Reject Request
                                        </button>
                                        <button type="submit" 
                                                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
                                            Send Quote
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @elseif($quoteRequest->status !== 'pending')
                    <!-- Quote Details -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Quote Details</h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Quoted Amount</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($quoteRequest->quoted_amount, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Delivery Days</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $quoteRequest->delivery_days }} days</dd>
                                </div>
                                @if($quoteRequest->quote_notes)
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Quote Notes</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $quoteRequest->quote_notes }}</dd>
                                    </div>
                                @endif
                            </dl>
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
                                        {{ strtoupper(substr($quoteRequest->customer->name, 0, 2)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">{{ $quoteRequest->customer->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $quoteRequest->customer->email }}</p>
                                @if($quoteRequest->customer->phone)
                                    <p class="text-sm text-gray-500">{{ $quoteRequest->customer->phone }}</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <dt class="text-sm font-medium text-gray-500">Member since</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quoteRequest->customer->created_at->format('M j, Y') }}</dd>
                        </div>
                    </div>
                </div>

                <!-- Order Status -->
                @if($quoteRequest->status === 'accepted' && $quoteRequest->order)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Order Status</h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <div class="text-center">
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                                    @if($quoteRequest->order->status === 'active') bg-blue-100 text-blue-800
                                    @elseif($quoteRequest->order->status === 'delivered') bg-green-100 text-green-800
                                    @elseif($quoteRequest->order->status === 'completed') bg-purple-100 text-purple-800
                                    @endif">
                                    {{ ucfirst($quoteRequest->order->status) }}
                                </span>
                                <div class="mt-3">
                                    <a href="{{ route('admin.orders.show', $quoteRequest->order) }}" 
                                       class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                                        View Order Details →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reject Quote Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('admin.quote-requests.reject', $quoteRequest) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Reject Quote Request
                            </h3>
                            <div class="mt-4">
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Reason for rejection</label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="4" required
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" 
                                          placeholder="Please explain why this request is being rejected..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Reject Request
                    </button>
                    <button type="button" onclick="hideRejectModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function hideRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>
@endsection
