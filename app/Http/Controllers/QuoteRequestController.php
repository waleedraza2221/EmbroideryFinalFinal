<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Notifications\AdminQuoteRequested;
use App\Notifications\AdminQuoteAccepted;

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
        // Get server upload limits
        $uploadMaxSize = ini_get('upload_max_filesize');
        $uploadMaxBytes = $this->parseSize($uploadMaxSize);
        $maxSizeKB = floor($uploadMaxBytes / 1024);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'instructions' => 'required|string|max:5000',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,ai,eps,svg,psd,zip,rar|max:' . $maxSizeKB
        ], [
            'files.*.max' => 'Each file must be no larger than ' . $uploadMaxSize . '. Current server limit: ' . $uploadMaxSize,
            'files.*.mimes' => 'File must be: jpg, jpeg, png, gif, pdf, ai, eps, svg, psd, zip, or rar'
        ]);

        $quoteRequest = new QuoteRequest();
        $quoteRequest->customer_id = auth()->id();
        $quoteRequest->title = $request->title;
        $quoteRequest->instructions = $request->instructions;

        // Handle file uploads with better error handling
        if ($request->hasFile('files')) {
            $uploadedFiles = [];
            
            foreach ($request->file('files') as $index => $file) {
                if ($file->isValid()) {
                    try {
                        // Ensure storage directory exists
                        if (!Storage::disk('public')->exists('quote-files')) {
                            Storage::disk('public')->makeDirectory('quote-files');
                        }
                        
                        // Generate unique filename to avoid conflicts
                        $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('quote-files', $filename, 'public');
                        
                        if ($path) {
                            $uploadedFiles[] = [
                                'name' => $file->getClientOriginalName(),
                                'path' => $path,
                                'size' => $file->getSize(),
                                'type' => $file->getMimeType()
                            ];
                        } else {
                            throw new \Exception("Failed to store file: " . $file->getClientOriginalName());
                        }
                    } catch (\Exception $e) {
                        return back()->withErrors(['files.' . $index => 'Failed to upload file: ' . $file->getClientOriginalName() . '. Error: ' . $e->getMessage()])->withInput();
                    }
                } else {
                    return back()->withErrors(['files.' . $index => 'Invalid file: ' . $file->getClientOriginalName() . '. Please check file size (max: ' . $uploadMaxSize . ')'])->withInput();
                }
            }
            
            $quoteRequest->files = $uploadedFiles;
        }

        $quoteRequest->save();

        // Notify all admins
        User::where('is_admin', true)->each(function($admin) use ($quoteRequest){
            $admin->notify(new AdminQuoteRequested($quoteRequest->id, $quoteRequest->title, $quoteRequest->customer_id));
        });

        return redirect()->route('quote-requests.show', $quoteRequest)
            ->with('success', 'Quote request submitted successfully! You will receive a response within 24 hours.');
    }

    /**
     * Parse PHP size string (like "2M", "8M") to bytes
     */
    private function parseSize($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        
        return round($size);
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

            // Notify admins of acceptance
            User::where('is_admin', true)->each(function($admin) use ($quoteRequest){
                $admin->notify(new AdminQuoteAccepted($quoteRequest->id, $quoteRequest->title, $quoteRequest->customer_id));
            });
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
