<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the customer's orders
     */
    public function index(Request $request)
    {
        $query = Order::where('customer_id', auth()->id())
            ->with('quoteRequest');

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply ordering
        $query->latest();

        $orders = $query->paginate(10)->appends($request->query());

        // Get order statuses for filter dropdown
        $statuses = Order::where('customer_id', auth()->id())
            ->distinct()
            ->pluck('status')
            ->filter()
            ->sort();

        return view('orders.index', compact('orders', 'statuses'));
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        // Ensure customer can only view their own orders
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        $order->load('quoteRequest');

        return view('orders.show', compact('order'));
    }

    /**
     * Mark order as completed and submit feedback
     */
    public function complete(Request $request, Order $order)
    {
        // Ensure customer can only complete their own orders
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        if (!$order->isDelivered()) {
            return back()->with('error', 'Order must be delivered before it can be completed.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'customer_feedback' => 'nullable|string|max:1000'
        ]);

        $order->update([
            'status' => 'completed',
            'rating' => $request->rating,
            'customer_feedback' => $request->customer_feedback,
            'completed_at' => now()
        ]);

        // Generate invoice for completed order
        $invoice = $order->generateInvoice();
        
        $message = 'Order completed successfully! Thank you for your feedback.';
        if ($invoice) {
            $message .= ' An invoice has been generated and is available in your invoices section.';
        }

        return redirect()->route('orders.show', $order)
            ->with('success', $message);
    }

    /**
     * Download a delivery file
     */
    public function downloadDeliveryFile(Order $order, $fileIndex)
    {
        // Ensure customer can only download their own files
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        if (!$order->isDelivered() && !$order->isCompleted()) {
            abort(403, 'Delivery files are not available yet.');
        }

        $files = $order->delivery_files ?? [];
        
        if (!isset($files[$fileIndex])) {
            abort(404);
        }

        $file = $files[$fileIndex];
        
        if (!Storage::disk('public')->exists($file['path'])) {
            abort(404);
        }

        return Storage::disk('public')->download($file['path'], $file['name']);
    }

    /**
     * Download original files from quote request
     */
    public function downloadOriginalFile(Order $order, $fileIndex)
    {
        // Ensure customer can only download their own files
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        $files = $order->original_files ?? [];
        
        if (!isset($files[$fileIndex])) {
            abort(404);
        }

        $file = $files[$fileIndex];
        
        if (!Storage::disk('public')->exists($file['path'])) {
            abort(404);
        }

        return Storage::disk('public')->download($file['path'], $file['name']);
    }
}
