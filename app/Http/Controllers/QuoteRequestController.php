<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuoteRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the customer's quote requests
     */
    public function index(Request $request)
    {
        $query = QuoteRequest::where('customer_id', auth()->id());

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $quoteRequests = $query->latest()->paginate(10)->withQueryString();

        // Get available statuses for the filter dropdown
        $statuses = [
            'all' => 'All Statuses',
            'pending' => 'Pending',
            'quoted' => 'Quoted',
            'accepted' => 'Accepted',
        ];

        return view('quote-requests.index', compact('quoteRequests', 'statuses'));
    }

    /**
     * Show the form for creating a new quote request
     */
    public function create()
    {
        return view('quote-requests.create');
    }

    /**
     * Store a newly created quote request
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'instructions' => 'required|string|max:5000',
            'files.*' => 'nullable|file|max:10240'
        ]);

        $quoteRequest = new QuoteRequest();
        $quoteRequest->customer_id = auth()->id();
        $quoteRequest->title = $request->title;
        $quoteRequest->instructions = $request->instructions;

        // Handle file uploads
        if ($request->hasFile('files')) {
            $uploadedFiles = [];
            
            foreach ($request->file('files') as $file) {
                $path = $file->store('quote-files', 'public');
                $uploadedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType()
                ];
            }
            
            $quoteRequest->files = $uploadedFiles;
        }

        $quoteRequest->save();

        return redirect()->route('quote-requests.show', $quoteRequest)
            ->with('success', 'Quote request submitted successfully! You will receive a response within 24 hours.');
    }

    /**
     * Display the specified quote request
     */
    public function show(QuoteRequest $quoteRequest)
    {
        // Ensure customer can only view their own quote requests
        if ($quoteRequest->customer_id !== auth()->id()) {
            abort(403);
        }

        // Generate 2Checkout signature for locked cart if quote is available
        $signatureHash = null;
        if ($quoteRequest->isQuoted()) {
            $signatureHash = $this->generateCartSignature($quoteRequest);
        }

        return view('quote-requests.show', compact('quoteRequest', 'signatureHash'));
    }

    /**
     * Generate 2Checkout signature for locked cart
     */
    private function generateCartSignature(QuoteRequest $quoteRequest)
    {
        $payload = [
            'merchant' => config('services.twocheckout.account_number'),
            'currency' => 'USD',
            'lock' => 1,
            'products' => [
                [
                    'code' => "QUOTE_{$quoteRequest->id}",
                    'quantity' => (int) $quoteRequest->quoted_amount
                ]
            ]
        ];
        
        // Convert to JSON and create hash using secret key
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $secretKey = config('services.twocheckout.secret_key');
        $signature = hash('sha256', $jsonPayload . $secretKey);
        
        \Log::info('Generated 2Checkout signature', [
            'quote_id' => $quoteRequest->id,
            'payload' => $payload,
            'signature' => $signature
        ]);
        
        return $signature;
    }

    /**
     * Accept quote and redirect to payment
     */
    public function acceptQuote(QuoteRequest $quoteRequest)
    {
        // Ensure customer can only accept their own quote requests
        if ($quoteRequest->customer_id !== auth()->id()) {
            abort(403);
        }

        if (!$quoteRequest->isQuoted()) {
            return back()->with('error', 'This quote request has not been quoted yet.');
        }

        // Check if already paid for
        if ($quoteRequest->isPaid()) {
            return redirect()->route('orders.index')
                ->with('info', 'This quote has already been paid for.');
        }

        // Redirect to payment page
        try {
            $paymentUrl = route('payment.initiate', $quoteRequest);
            \Log::info('Payment URL generated: ' . $paymentUrl);
            return redirect($paymentUrl);
        } catch (\Exception $e) {
            \Log::error('Error generating payment route: ' . $e->getMessage());
            return back()->with('error', 'Payment system is currently unavailable. Please try again later.');
        }
    }

    /**
     * Download a file from quote request
     */
    public function downloadFile(QuoteRequest $quoteRequest, $fileIndex)
    {
        // Ensure customer can only download their own files
        if ($quoteRequest->customer_id !== auth()->id()) {
            abort(403);
        }

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
}
