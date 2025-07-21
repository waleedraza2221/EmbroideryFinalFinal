<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of all orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'quoteRequest']);

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

        // Filter by customer if provided
        if ($request->has('customer') && $request->customer) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'ILIKE', '%' . $request->customer . '%')
                  ->orWhere('email', 'ILIKE', '%' . $request->customer . '%');
            });
        }

        // Apply ordering
        $query->latest();

        $orders = $query->paginate(15)->appends($request->query());

        // Get order statuses for filter dropdown
        $statuses = Order::distinct()
            ->pluck('status')
            ->filter()
            ->sort();

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'quoteRequest']);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Deliver order with files
     */
    public function deliver(Request $request, Order $order)
    {
        $request->validate([
            'delivery_notes' => 'nullable|string|max:1000',
            'delivery_files.*' => 'required|file|max:20480'
        ]);

        if (!$order->isActive()) {
            return back()->with('error', 'Only active orders can be delivered.');
        }

        $uploadedFiles = [];
        
        if ($request->hasFile('delivery_files')) {
            foreach ($request->file('delivery_files') as $file) {
                $path = $file->store('delivery-files', 'public');
                $uploadedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType()
                ];
            }
        }

        $order->update([
            'status' => 'delivered',
            'delivery_files' => $uploadedFiles,
            'delivery_notes' => $request->delivery_notes,
            'delivered_at' => now()
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order delivered successfully! Customer has been notified.');
    }

    /**
     * Download original files from order
     */
    public function downloadOriginalFile(Order $order, $fileIndex)
    {
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

    /**
     * Download delivery files
     */
    public function downloadDeliveryFile(Order $order, $fileIndex)
    {
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
     * Get statistics for dashboard
     */
    public function stats()
    {
        $stats = [
            'active' => Order::where('status', 'active')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'overdue' => Order::where('status', 'active')
                ->where('due_date', '<', now())
                ->count(),
            'total' => Order::count()
        ];

        return response()->json($stats);
    }
}
