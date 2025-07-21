<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Add admin middleware when you have it
        // $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $query = Invoice::with(['order.customer', 'customer'])
            ->completed();

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by customer search
        if ($request->filled('customer_search')) {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_search . '%')
                  ->orWhere('email', 'like', '%' . $request->customer_search . '%');
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by invoice number
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        // Filter by amount range
        if ($request->filled('min_amount')) {
            $query->where('total_amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('total_amount', '<=', $request->max_amount);
        }

        $invoices = $query->latest()->paginate(15);

        // Get customers for filter dropdown
        $customers = User::select('id', 'name', 'email')
            ->whereHas('orders')
            ->orderBy('name')
            ->get();

        // Summary statistics
        $stats = [
            'total_invoices' => $query->count(),
            'total_amount' => $query->sum('total_amount'),
            'paid_amount' => $query->where('status', 'paid')->sum('total_amount'),
            'pending_amount' => $query->where('status', 'pending')->sum('total_amount'),
        ];

        return view('admin.invoices.index', compact('invoices', 'customers', 'stats'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['order.customer', 'customer']);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function download(Invoice $invoice, $format = 'pdf')
    {
        if ($format === 'csv') {
            return $this->downloadCsv($invoice);
        }

        return $this->downloadPdf($invoice);
    }

    private function downloadCsv(Invoice $invoice)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="invoice-' . $invoice->invoice_number . '.csv"',
        ];

        $callback = function() use ($invoice) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Invoice Details']);
            fputcsv($file, []);
            fputcsv($file, ['Invoice Number', $invoice->invoice_number]);
            fputcsv($file, ['Date', $invoice->created_at->format('M d, Y')]);
            fputcsv($file, ['Due Date', $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A']);
            fputcsv($file, ['Status', ucfirst($invoice->status)]);
            fputcsv($file, []);
            
            // Customer information
            fputcsv($file, ['Customer Information']);
            fputcsv($file, ['Name', $invoice->customer->name]);
            fputcsv($file, ['Email', $invoice->customer->email]);
            fputcsv($file, ['Phone', $invoice->customer->phone ?? 'N/A']);
            fputcsv($file, []);
            
            // Billing information
            fputcsv($file, ['Billing Information']);
            fputcsv($file, ['Name', $invoice->billing_name]);
            fputcsv($file, ['Company', $invoice->billing_company ?: 'N/A']);
            fputcsv($file, ['Address', $invoice->billing_address]);
            fputcsv($file, ['City', $invoice->billing_city]);
            fputcsv($file, ['State', $invoice->billing_state]);
            fputcsv($file, ['Zip', $invoice->billing_zip]);
            fputcsv($file, ['Country', $invoice->billing_country]);
            fputcsv($file, []);
            
            // Line items
            fputcsv($file, ['Order Items']);
            fputcsv($file, ['Description', 'Quantity', 'Price', 'Total']);
            
            $lineItems = json_decode($invoice->line_items, true) ?? [];
            foreach ($lineItems as $item) {
                fputcsv($file, [
                    $item['description'] ?? '',
                    $item['quantity'] ?? 0,
                    '$' . number_format($item['price'] ?? 0, 2),
                    '$' . number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 2)
                ]);
            }
            
            fputcsv($file, []);
            fputcsv($file, ['Subtotal', '', '', '$' . number_format($invoice->subtotal, 2)]);
            fputcsv($file, ['Tax', '', '', '$' . number_format($invoice->tax_amount, 2)]);
            fputcsv($file, ['Total', '', '', '$' . number_format($invoice->total_amount, 2)]);
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function downloadPdf(Invoice $invoice)
    {
        // For now, return a simple PDF view - you can integrate with a PDF library later
        $pdf = view('admin.invoices.pdf', compact('invoice'))->render();
        
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="invoice-' . $invoice->invoice_number . '.pdf"');
    }

    public function exportAll(Request $request, $format = 'csv')
    {
        $query = Invoice::with(['order.customer', 'customer'])
            ->completed();

        // Apply same filters as index
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('customer_search')) {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_search . '%')
                  ->orWhere('email', 'like', '%' . $request->customer_search . '%');
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('min_amount')) {
            $query->where('total_amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('total_amount', '<=', $request->max_amount);
        }

        $invoices = $query->latest()->get();

        if ($format === 'csv') {
            return $this->exportAllCsv($invoices);
        }

        return $this->exportAllPdf($invoices);
    }

    private function exportAllCsv($invoices)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="all-invoices-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($invoices) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Invoice Number', 'Date', 'Due Date', 'Status', 
                'Customer Name', 'Customer Email', 'Subtotal', 'Tax', 'Total'
            ]);
            
            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->created_at->format('M d, Y'),
                    $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A',
                    ucfirst($invoice->status),
                    $invoice->customer->name,
                    $invoice->customer->email,
                    '$' . number_format($invoice->subtotal, 2),
                    '$' . number_format($invoice->tax_amount, 2),
                    '$' . number_format($invoice->total_amount, 2)
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportAllPdf($invoices)
    {
        // Simple PDF export for all invoices
        $pdf = view('admin.invoices.export-pdf', compact('invoices'))->render();
        
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="all-invoices-' . now()->format('Y-m-d') . '.pdf"');
    }

    public function updateStatus(Invoice $invoice, Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,cancelled,refunded'
        ]);

        $invoice->update([
            'status' => $request->status,
            'paid_at' => $request->status === 'paid' ? now() : null
        ]);

        return back()->with('success', 'Invoice status updated successfully.');
    }

    public function bulkExport(Request $request, $format = 'csv')
    {
        $invoiceIds = $request->input('invoice_ids', []);
        
        if (empty($invoiceIds)) {
            return back()->with('error', 'Please select invoices to export.');
        }

        $invoices = Invoice::with(['order.customer', 'customer'])
            ->whereIn('id', $invoiceIds)
            ->get();

        if ($format === 'csv') {
            return $this->exportAllCsv($invoices);
        }

        return $this->exportAllPdf($invoices);
    }
}
