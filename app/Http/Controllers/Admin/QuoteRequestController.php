<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Notifications\CustomerQuoteProvided;

class QuoteRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of all quote requests
     */
    public function index(Request $request)
    {
        $query = QuoteRequest::with('customer')->latest();

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $quoteRequests = $query->paginate(15);

        return view('admin.quote-requests.index', compact('quoteRequests'));
    }

    /**
     * Display the specified quote request
     */
    public function show(QuoteRequest $quoteRequest)
    {
        $quoteRequest->load('customer');
        
        return view('admin.quote-requests.show', compact('quoteRequest'));
    }

    /**
     * Update quote request with admin response
     */
    public function update(Request $request, QuoteRequest $quoteRequest)
    {
        $request->validate([
            'quoted_amount' => 'required|numeric|min:1',
            'delivery_days' => 'required|integer|min:1|max:365',
            'quote_notes' => 'nullable|string|max:1000'
        ]);

        $quoteRequest->update([
            'quoted_amount' => $request->quoted_amount,
            'delivery_days' => $request->delivery_days,
            'quote_notes' => $request->quote_notes,
            'status' => 'quoted',
            'quoted_at' => now()
        ]);

        // Notify customer
        $customer = $quoteRequest->customer; 
        if($customer){
            $customer->notify(new CustomerQuoteProvided(
                $quoteRequest->id,
                $quoteRequest->title,
                (string) $quoteRequest->quoted_amount,
                (string) $quoteRequest->delivery_days
            ));
        }

        return redirect()->route('admin.quote-requests.show', $quoteRequest)
            ->with('success', 'Quote sent successfully! Customer will be notified.');
    }

    /**
     * Reject a quote request
     */
    public function reject(Request $request, QuoteRequest $quoteRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $quoteRequest->update([
            'status' => 'rejected',
            'quote_notes' => $request->rejection_reason
        ]);

        return redirect()->route('admin.quote-requests.index')
            ->with('success', 'Quote request rejected successfully.');
    }

    /**
     * Download a file from quote request
     */
    public function downloadFile(QuoteRequest $quoteRequest, $fileIndex)
    {
        $files = $quoteRequest->files ?? [];
        
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
            'pending' => QuoteRequest::where('status', 'pending')->count(),
            'quoted' => QuoteRequest::where('status', 'quoted')->count(),
            'accepted' => QuoteRequest::where('status', 'accepted')->count(),
            'rejected' => QuoteRequest::where('status', 'rejected')->count(),
            'total' => QuoteRequest::count()
        ];

        return response()->json($stats);
    }
}
