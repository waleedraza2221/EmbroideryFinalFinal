@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Invoice {{ $invoice->invoice_number }}</h1>
        <div class="flex space-x-4">
            <a href="{{ route('invoices.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                Back to Invoices
            </a>
            <a href="{{ route('invoices.download', [$invoice, 'csv']) }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3"></path>
                </svg>
                Download CSV
            </a>
            <a href="{{ route('invoices.download', [$invoice, 'pdf']) }}" 
               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3"></path>
                </svg>
                Download PDF
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-8">
        <!-- Invoice Header -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Invoice Details</h2>
                <div class="space-y-2">
                    <div><span class="font-medium">Invoice Number:</span> {{ $invoice->invoice_number }}</div>
                    <div><span class="font-medium">Date:</span> {{ $invoice->invoice_date->format('M d, Y') }}</div>
                    <div><span class="font-medium">Due Date:</span> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</div>
                    <div>
                        <span class="font-medium">Status:</span> 
                        @php
                            $statusColors = [
                                'draft' => 'bg-gray-100 text-gray-800',
                                'sent' => 'bg-blue-100 text-blue-800',
                                'paid' => 'bg-green-100 text-green-800',
                                'overdue' => 'bg-red-100 text-red-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                        @endphp
                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                    @if($invoice->paid_at)
                        <div><span class="font-medium">Paid Date:</span> {{ $invoice->paid_at->format('M d, Y') }}</div>
                    @endif
                </div>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Customer Information</h2>
                <div class="space-y-1">
                    <div class="font-medium">{{ $invoice->customer_name }}</div>
                    <div>{{ $invoice->customer_email }}</div>
                    @if($invoice->billing_company)
                        <div>{{ $invoice->billing_company }}</div>
                    @endif
                    @if($invoice->billing_address)
                        <div class="whitespace-pre-line">{{ $invoice->billing_address }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Information -->
        @if($invoice->order)
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Related Order</h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="font-medium">Order ID:</span> 
                            <a href="{{ route('orders.show', $invoice->order) }}" class="text-blue-600 hover:text-blue-800">
                                #{{ $invoice->order->id }}
                            </a>
                        </div>
                        <div><span class="font-medium">Status:</span> {{ ucfirst($invoice->order->status) }}</div>
                        <div><span class="font-medium">Order Date:</span> {{ $invoice->order->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Line Items -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Items</h2>
            <div class="overflow-x-auto">
                <table class="w-full border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 border-b">Description</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700 border-b">Quantity</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700 border-b">Price</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700 border-b">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $lineItems = $invoice->line_items ?? [];
                        @endphp
                        @forelse($lineItems as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 border-b">{{ $item['description'] ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right border-b">{{ $item['quantity'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right border-b">${{ number_format($item['price'] ?? 0, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right border-b">${{ number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">No items found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals -->
        <div class="flex justify-end">
            <div class="w-full max-w-sm">
                <div class="space-y-2">
                    <div class="flex justify-between py-2">
                        <span class="font-medium">Subtotal:</span>
                        <span>${{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="font-medium">Tax:</span>
                        <span>${{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-t border-gray-200 text-lg font-bold">
                        <span>Total:</span>
                        <span>${{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
            <div class="mt-8 pt-8 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Notes</h3>
                <p class="text-gray-700">{{ $invoice->notes }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
