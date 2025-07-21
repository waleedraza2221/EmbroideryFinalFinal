<!DOCTYPE html>
<html>
<head>
    <title>All Invoices Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .export-table table { width: 100%; border-collapse: collapse; }
        .export-table th, .export-table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .export-table th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoices Export</h1>
        <p>Generated on {{ now()->format('M d, Y \a\t g:i A') }}</p>
        <p>Total Invoices: {{ $invoices->count() }}</p>
    </div>

    <div class="export-table">
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Customer Name</th>
                    <th>Customer Email</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-right">Tax</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                        <td>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ ucfirst($invoice->status) }}</td>
                        <td>{{ $invoice->customer->name }}</td>
                        <td>{{ $invoice->customer->email }}</td>
                        <td class="text-right">${{ number_format($invoice->subtotal, 2) }}</td>
                        <td class="text-right">${{ number_format($invoice->tax_amount, 2) }}</td>
                        <td class="text-right">${{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="6">TOTALS</td>
                    <td class="text-right">${{ number_format($invoices->sum('subtotal'), 2) }}</td>
                    <td class="text-right">${{ number_format($invoices->sum('tax_amount'), 2) }}</td>
                    <td class="text-right">${{ number_format($invoices->sum('total_amount'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
