<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Invoice::with(['order.customer'])
            ->where('customer_id', Auth::id());

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('invoice_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('invoice_date', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by invoice number
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        $invoices = $query->latest('invoice_date')->paginate(10);

        return view('invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        // Ensure user can only view their own invoices
        if ($invoice->customer_id !== Auth::id()) {
            abort(403);
        }

        return view('invoices.show', compact('invoice'));
    }

    public function download(Invoice $invoice, $format = 'pdf')
    {
        // Ensure user can only download their own invoices
        if ($invoice->customer_id !== Auth::id()) {
            abort(403);
        }

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
            fputcsv($file, ['Date', $invoice->invoice_date->format('M d, Y')]);
            fputcsv($file, ['Due Date', $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A']);
            fputcsv($file, ['Status', ucfirst($invoice->status)]);
            fputcsv($file, []);
            
            // Customer information
            fputcsv($file, ['Customer Information']);
            fputcsv($file, ['Name', $invoice->customer_name]);
            fputcsv($file, ['Email', $invoice->customer_email]);
            fputcsv($file, ['Address', $invoice->billing_address]);
            if ($invoice->billing_company) {
                fputcsv($file, ['Company', $invoice->billing_company]);
            }
            fputcsv($file, []);
            
            // Line items
            fputcsv($file, ['Order Items']);
            fputcsv($file, ['Description', 'Quantity', 'Price', 'Total']);
            
            $lineItems = $invoice->line_items ?? [];
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
        $pdf = view('invoices.pdf', compact('invoice'))->render();
        
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="invoice-' . $invoice->invoice_number . '.pdf"');
    }

    public function exportAll(Request $request, $format = 'csv')
    {
        $query = Invoice::with(['order.customer'])
            ->where('customer_id', Auth::id());

        // Apply same filters as index
        if ($request->filled('start_date')) {
            $query->whereDate('invoice_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('invoice_date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        $invoices = $query->latest('invoice_date')->get();

        if ($format === 'csv') {
            return $this->exportAllCsv($invoices);
        }

        return $this->exportAllPdf($invoices);
    }

    private function exportAllCsv($invoices)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="invoices-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($invoices) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Invoice Number', 'Date', 'Due Date', 'Status', 
                'Customer', 'Subtotal', 'Tax', 'Total'
            ]);
            
            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->invoice_date->format('M d, Y'),
                    $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A',
                    ucfirst($invoice->status),
                    $invoice->customer_name,
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
        $pdf = view('invoices.export-pdf', compact('invoices'))->render();
        
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="invoices-' . now()->format('Y-m-d') . '.pdf"');
    }
}
