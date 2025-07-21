@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Invoice Management</h1>
        <div class="flex space-x-4">
            <a href="{{ route('admin.invoices.export', 'csv') }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export All CSV
            </a>
            <a href="{{ route('admin.invoices.export', 'pdf') }}" 
               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export All PDF
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Invoices</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_invoices']) }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Amount</dt>
                        <dd class="text-lg font-medium text-gray-900">${{ number_format($stats['total_amount'], 2) }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-600 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Paid Amount</dt>
                        <dd class="text-lg font-medium text-gray-900">${{ number_format($stats['paid_amount'], 2) }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Pending Amount</dt>
                        <dd class="text-lg font-medium text-gray-900">${{ number_format($stats['pending_amount'], 2) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div>
                <label for="customer_search" class="block text-sm font-medium text-gray-700 mb-2">Customer Search</label>
                <input type="text" 
                       name="customer_search" 
                       id="customer_search"
                       value="{{ request('customer_search') }}"
                       placeholder="Search by name or email..."
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" 
                       name="start_date" 
                       id="start_date"
                       value="{{ request('start_date') }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" 
                       name="end_date" 
                       id="end_date"
                       value="{{ request('end_date') }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" 
                        id="status"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <div>
                <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-2">Invoice Number</label>
                <input type="text" 
                       name="invoice_number" 
                       id="invoice_number"
                       value="{{ request('invoice_number') }}"
                       placeholder="Search by invoice number..."
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="min_amount" class="block text-sm font-medium text-gray-700 mb-2">Min Amount</label>
                <input type="number" 
                       name="min_amount" 
                       id="min_amount"
                       value="{{ request('min_amount') }}"
                       placeholder="0.00"
                       step="0.01"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="max_amount" class="block text-sm font-medium text-gray-700 mb-2">Max Amount</label>
                <input type="number" 
                       name="max_amount" 
                       id="max_amount"
                       value="{{ request('max_amount') }}"
                       placeholder="0.00"
                       step="0.01"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="xl:col-span-4 flex space-x-4">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Apply Filters
                </button>
                <a href="{{ route('admin.invoices.index') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg">
                    Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form id="bulk-action-form" method="POST">
            @csrf
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Bulk Actions:</label>
                <button type="button" 
                        onclick="submitBulkAction('csv')"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                    Export Selected (CSV)
                </button>
                <button type="button" 
                        onclick="submitBulkAction('pdf')"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">
                    Export Selected (PDF)
                </button>
                <span id="selected-count" class="text-sm text-gray-600">0 selected</span>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($invoices->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" id="select-all" class="rounded">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($invoices as $invoice)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <input type="checkbox" 
                                           name="invoice_ids[]" 
                                           value="{{ $invoice->id }}" 
                                           class="invoice-checkbox rounded">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $invoice->invoice_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-gray-900">{{ $invoice->customer->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $invoice->customer->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                    {{ $invoice->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                    {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'paid' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'refunded' => 'bg-gray-100 text-gray-800'
                                        ];
                                    @endphp
                                    <form method="POST" action="{{ route('admin.invoices.update-status', $invoice) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" 
                                                onchange="this.form.submit()"
                                                class="text-xs px-2 py-1 rounded-full border-0 {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            <option value="pending" {{ $invoice->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="paid" {{ $invoice->status === 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="cancelled" {{ $invoice->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            <option value="refunded" {{ $invoice->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    ${{ number_format($invoice->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.invoices.show', $invoice) }}" 
                                       class="text-blue-600 hover:text-blue-900">View</a>
                                    <a href="{{ route('admin.invoices.download', [$invoice, 'pdf']) }}" 
                                       class="text-red-600 hover:text-red-900">PDF</a>
                                    <a href="{{ route('admin.invoices.download', [$invoice, 'csv']) }}" 
                                       class="text-green-600 hover:text-green-900">CSV</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $invoices->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['customer_search', 'start_date', 'end_date', 'status', 'invoice_number', 'min_amount', 'max_amount']))
                        Try adjusting your filters to see more results.
                    @else
                        Invoices will appear here once orders are completed.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

<script>
// Bulk action functionality
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.invoice-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedCount();
});

document.querySelectorAll('.invoice-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});

function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.invoice-checkbox:checked').length;
    document.getElementById('selected-count').textContent = selectedCount + ' selected';
}

function submitBulkAction(format) {
    const selectedInvoices = Array.from(document.querySelectorAll('.invoice-checkbox:checked')).map(cb => cb.value);
    
    if (selectedInvoices.length === 0) {
        alert('Please select invoices to export.');
        return;
    }
    
    const form = document.getElementById('bulk-action-form');
    form.action = '{{ route("admin.invoices.bulk-export", ["format" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', format);
    
    // Add selected invoice IDs to form
    selectedInvoices.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'invoice_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    form.submit();
}
</script>
@endsection
