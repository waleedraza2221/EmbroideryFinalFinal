<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 30px; }
        .invoice-details { margin-bottom: 30px; }
        .billing-info { margin-bottom: 30px; }
        .line-items table { width: 100%; border-collapse: collapse; }
        .line-items th, .line-items td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .line-items th { background-color: #f2f2f2; }
        .totals { margin-top: 20px; text-align: right; }
        .total-row { margin-bottom: 5px; }
        .total-amount { font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <h2>{{ config('app.name', 'Embroidery Business') }}</h2>
    </div>

    <div class="invoice-details">
        <table width="100%">
            <tr>
                <td width="50%">
                    <strong>Invoice Number:</strong> {{ $invoice->invoice_number }}<br>
                    <strong>Date:</strong> {{ $invoice->created_at->format('M d, Y') }}<br>
                    <strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}<br>
                    <strong>Status:</strong> {{ ucfirst($invoice->status) }}
                </td>
                <td width="50%">
                    @if($invoice->order)
                        <strong>Order Number:</strong> {{ $invoice->order->order_number }}<br>
                        <strong>Order Date:</strong> {{ $invoice->order->created_at->format('M d, Y') }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="billing-info">
        <h3>Bill To:</h3>
        {{ $invoice->billing_name }}<br>
        @if($invoice->billing_company)
            {{ $invoice->billing_company }}<br>
        @endif
        {{ $invoice->billing_address }}<br>
        {{ $invoice->billing_city }}, {{ $invoice->billing_state }} {{ $invoice->billing_zip }}<br>
        {{ $invoice->billing_country }}

        <h3 style="margin-top: 20px;">Customer:</h3>
        {{ $invoice->customer->name }}<br>
        {{ $invoice->customer->email }}<br>
        @if($invoice->customer->phone)
            {{ $invoice->customer->phone }}
        @endif
    </div>

    <div class="line-items">
        <h3>Items:</h3>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Quantity</th>
                    <th style="text-align: right;">Price</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $lineItems = json_decode($invoice->line_items, true) ?? [];
                @endphp
                @forelse($lineItems as $item)
                    <tr>
                        <td>{{ $item['description'] ?? 'N/A' }}</td>
                        <td style="text-align: right;">{{ $item['quantity'] ?? 0 }}</td>
                        <td style="text-align: right;">${{ number_format($item['price'] ?? 0, 2) }}</td>
                        <td style="text-align: right;">${{ number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center;">No items found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="totals">
        <div class="total-row">
            <strong>Subtotal: ${{ number_format($invoice->subtotal, 2) }}</strong>
        </div>
        <div class="total-row">
            <strong>Tax: ${{ number_format($invoice->tax_amount, 2) }}</strong>
        </div>
        <div class="total-row total-amount">
            <strong>Total: ${{ number_format($invoice->total_amount, 2) }}</strong>
        </div>
    </div>

    @if($invoice->notes)
        <div style="margin-top: 30px;">
            <h3>Notes:</h3>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif

    <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
        Generated on {{ now()->format('M d, Y \a\t g:i A') }}
    </div>
</body>
</html>
