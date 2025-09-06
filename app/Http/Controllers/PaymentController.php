<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\QuoteRequest;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Allow public access to thank-you and webhook endpoints
        $this->middleware('auth')->except(['thankYou','webhook']);
    }

    /**
     * Inline finalize callback from front-end (still creates a pending payment + order stub)
     * NOTE: Verification logic has been intentionally removed per request.
     */
    public function inlineFinalized(Request $request)
    {
        $data = $request->validate([
            'quote_id' => ['required','integer','exists:quote_requests,id'],
            'order_external_ref' => ['required','string','max:255'],
            'user_id' => ['nullable','integer'],
            'payment_event' => ['required','in:payment:finalized,payment:initiated'],
            'payload' => ['nullable','array']
        ]);

        $quote = QuoteRequest::findOrFail($data['quote_id']);
        if ($quote->customer_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        // Avoid duplicates
        $existingCompleted = $quote->payments()->where('status','completed')->first();
        if ($existingCompleted) {
            return response()->json(['status'=>'ok','message'=>'Payment already completed','payment_id'=>$existingCompleted->id]);
        }
        $pending = $quote->payments()->where('status','pending')->where('payment_id',$data['order_external_ref'])->first();
        if ($pending) {
            return response()->json(['status'=>'ok','message'=>'Payment already pending','payment_id'=>$pending->id,'existing'=>true]);
        }

        DB::transaction(function() use (&$quote,$data){
            if ($quote->isQuoted()) { $quote->status = 'accepted'; $quote->save(); }
            if (!$quote->order) { try { $quote->createOrder(); } catch(\Throwable $e){ Log::warning('Order create fail inline: '.$e->getMessage()); } }
            Payment::create([
                'quote_request_id'=>$quote->id,
                'customer_id'=>$quote->customer_id,
                'payment_id'=>$data['order_external_ref'],
                'order_id'=>optional($quote->order)->id,
                'amount'=>$quote->quoted_amount,
                'currency'=>'USD',
                'status'=>'pending',
                'payment_method'=>'other',
                'payment_data'=>['event'=>$data['payment_event'], 'method'=>'2checkout-inline'],
                'transaction_data'=>$data['payload'] ?? null,
            ]);
        });
        return response()->json(['status'=>'ok']);
    }

    /**
     * Webhook endpoint intentionally left inert (no verification logic) per removal request.
     */
    public function webhook(Request $request)
    {
        Log::info('Webhook hit (verification disabled)');
        return response()->json(['status'=>'ignored']);
    }

    /**
     * Public thank-you page. All server-side verification removed.
     * Simply passes along basic query params for display; front-end JS (if any) must handle its own logic.
     */
    public function thankYou(Request $request)
    {
        //dd($request->all());
        $refno = $request->query('refno') ?? $request->query('REFNO');
        $amount = $request->query('total');
        $currency = $request->query('total-currency') ?? $request->query('currency');
    // Support additional ways of receiving quote identifier (refnoext from 2Checkout / manual)
    $quoteIdRaw = $request->query('quote_id') ?? $request->query('quote') ?? $request->query('refnoext');
    $quoteId = null;
    if($quoteIdRaw){
        if(preg_match('/(\d+)/', $quoteIdRaw, $m)){ $quoteId = $m[1]; }
    }

        // PRIORITY: Create payment record BEFORE showing thank-you page
        $user = $request->user();
        $paymentCreated = false;
        
        if ($refno && $quoteId) {
            // First check if payment already exists
            $existing = Payment::where('payment_id', $refno)->first();
            
            if (!$existing) {
                // Get the quote to ensure it exists and get customer info
                $quote = QuoteRequest::where('id', $quoteId)->first();
                
                if ($quote) {
                    // Determine customer ID (from auth user or quote)
                    $customerId = $user ? $user->id : $quote->customer_id;
                    
                    // Force creation with DB transaction
                    try {
                        DB::transaction(function() use ($quote, $customerId, $refno, $amount, $currency, &$paymentCreated) {
                            $payment = Payment::create([
                                'quote_request_id' => $quote->id,
                                'customer_id'      => $customerId,
                                'payment_id'       => $refno,
                                'order_id'         => optional($quote->order)->id,
                                'amount'           => $quote->quoted_amount ?? ($amount ? (float)$amount : 0),
                                'currency'         => $currency ?: 'USD',
                                'status'           => 'pending',
                                'payment_method'   => 'other',
                                'payment_data'     => ['created_on_thankyou' => true, 'timestamp' => now()->toISOString(), 'method' => '2checkout-inline'],
                                'transaction_data' => null,
                            ]);
                            $paymentCreated = true;
                            Log::info('Payment created successfully', ['payment_id' => $payment->id, 'refno' => $refno]);
                        });
                    } catch (\Throwable $e) {
                        Log::error('CRITICAL: Payment creation failed on thankYou', [
                            'error' => $e->getMessage(),
                            'refno' => $refno,
                            'quote_id' => $quoteId,
                            'customer_id' => $customerId
                        ]);
                        // Don't fail silently - this is critical
                        throw $e;
                    }
                } else {
                    Log::error('Quote not found for payment creation', ['quote_id' => $quoteId, 'refno' => $refno]);
                }
            }
        }
        
        // Handle placeholder payment mapping after main creation attempt
        if ($refno) {
            // Reconcile placeholder payment_id (e.g. QUOTE-<id>) to actual refno
            $refnoExt = $request->query('refnoext');
            if($refnoExt && $refnoExt !== $refno){
                $placeholder = Payment::where('payment_id',$refnoExt)->first();
                if($placeholder){
                    // Only update if no real payment record yet
                    $alreadyReal = Payment::where('payment_id',$refno)->exists();
                    if(!$alreadyReal){
                        try {
                            $placeholder->payment_id = $refno;
                            $placeholder->payment_data = array_merge($placeholder->payment_data ?? [], ['mapped_from'=>$refnoExt]);
                            $placeholder->save();
                            Log::info('Mapped placeholder payment', ['from' => $refnoExt, 'to' => $refno]);
                        } catch(\Throwable $e){ 
                            Log::warning('Failed to map placeholder payment id: '.$e->getMessage()); 
                        }
                    }
                }
            }
        }

        if(view()->exists('payments.thank-you')){
            return view('payments.thank-you', [
                'hasPaymentData' => $paymentCreated,
                'total' => $amount,
                'orderNumber' => null,
                'quoteRequest' => isset($quote) ? $quote : null,
                'invoiceId' => null,
                'merchantOrderId' => $refno,
                'quoteId' => $quoteId,
                'paymentCreated' => $paymentCreated,
                'key' => null,
                'signatureValid' => null,
                'verified' => false,
                'verificationAttempted' => false,
            ]);
        }
        return response('<h1>Thank you</h1><p>Your payment is being processed.</p>',200);
    }

    /**
     * Secure proxy for fetching order status from 2Checkout without exposing secret/HMAC to client.
     * Public (thank-you page is public) but rate-limited via global throttling; returns limited fields only.
     */
    public function orderStatusProxy(Request $request)
    {
        $data = $request->validate([
            'refno' => ['required','string','max:64'],
        ]);
        $refno = $data['refno'];

        $merchantCode = config('services.twocheckout.account_number', '255036765830');
        $secretKey = config('services.twocheckout.secret_key', 'Jdg6htmb2[7MITxpZqea');
        $apiVersion = '6.0';
        $url = "https://api.2checkout.com/rest/{$apiVersion}/orders/{$refno}";

        $date = gmdate('Y-m-d H:i:s');
        $string = strlen($merchantCode) . $merchantCode . strlen($date) . $date;
        $hash = hash_hmac('md5', $string, $secretKey);

        $headers = [
            "Content-Type: application/json",
            "Accept: application/json",
            "X-Avangate-Authentication: code=\"{$merchantCode}\" date=\"{$date}\" hash=\"{$hash}\"",
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return response()->json([
            'request_params' => $request->all(),
            'http_code'      => $httpCode,
            'curl_error'     => $curlError,
            'raw_response'   => $response,
            'api_response'   => json_decode($response, true),
        ]);
    }

    public function completeFromClient(Request $request)
    {
        $user = $request->user();
        if(!$user){
            return response()->json(['ok'=>false,'error'=>'Unauthenticated'], 401);
        }
        $data = $request->validate([
            'refno' => ['required','string','max:255'],
            'quote_id' => ['nullable','integer','exists:quote_requests,id'],
            'refnoext' => ['nullable','string','max:255']
        ]);
        $refno = $data['refno'];
        $quoteIdCandidate = $data['quote_id'] ?? $data['refnoext'] ?? $request->input('quote') ?? null;
        $payment = Payment::where('payment_id', $refno)->where('customer_id',$user->id)->first();
        if(!$payment && !empty($quoteIdCandidate)){
            // Try placeholder pattern QUOTE-{id}
            $placeholderId = 'QUOTE-' . preg_replace('/[^0-9]/','',$quoteIdCandidate);
            $payment = Payment::where('payment_id',$placeholderId)->where('customer_id',$user->id)->first();
            if($payment){
                try {
                    $payment->payment_id = $refno;
                    $payment->payment_data = array_merge($payment->payment_data ?? [], ['mapped_in_finalize'=>true]);
                    $payment->save();
                } catch(\Throwable $e){ Log::warning('Finalize mapping placeholder failed: '.$e->getMessage()); }
            }
        }
        if(!$payment){
            // Attempt to create a pending payment now if quote_id provided
            if(!empty($quoteIdCandidate)){
                $quote = QuoteRequest::where('id',$quoteIdCandidate)->where('customer_id',$user->id)->first();
                if($quote){
                    try {
                        $payment = Payment::create([
                            'quote_request_id'=>$quote->id,
                            'customer_id'=>$user->id,
                            'payment_id'=>$refno,
                            'order_id'=>optional($quote->order)->id,
                            'amount'=>$quote->quoted_amount,
                            'currency'=>'USD',
                            'status'=>'pending',
                            'payment_method'=>'other',
                            'payment_data'=>['created_in_finalize'=>true, 'method'=>'2checkout-inline'],
                            'transaction_data'=>null,
                        ]);
                    } catch(\Throwable $e){
                        Log::warning('Failed to create payment in finalize: '.$e->getMessage());
                    }
                }
            }
            // Fallback: maybe earlier we stored a placeholder like QUOTE-{id} but now have real refno
            if(!$payment && !empty($quoteIdCandidate)){
                $fallback = Payment::where('quote_request_id',$quoteIdCandidate)->where('customer_id',$user->id)->orderByDesc('id')->first();
                if($fallback){
                    // Promote this payment to use refno for future lookups
                    if($fallback->payment_id !== $refno){
                        $fallback->payment_id = $refno;
                        try { $fallback->save(); } catch(\Throwable $e){ Log::warning('Failed to update fallback payment_id: '.$e->getMessage()); }
                    }
                    $payment = $fallback;
                }
            }
            if(!$payment){
                return response()->json([
                    'ok'=>false,
                    'error'=>'Payment not found',
                    'debug'=>[
                        'searched_refno'=>$refno,
                        'quote_id_candidate'=>$quoteIdCandidate,
                        'payments_for_quote'=> Payment::where('quote_request_id',$quoteIdCandidate)->where('customer_id',$user->id)->pluck('payment_id')
                    ]
                ],404);
            }
        }
        // If still no payment (unlikely) attempt final auto-resolution using upstream ExternalReference
        if(!$payment){
            try {
                $merchantCode = config('services.twocheckout.account_number');
                $secretKey = config('services.twocheckout.secret_key');
                if($merchantCode && $secretKey){
                    $date = gmdate('Y-m-d H:i:s');
                    $string = strlen($merchantCode) . $merchantCode . strlen($date) . $date;
                    $hash = hash_hmac('md5', $string, $secretKey);
                    $url = "https://api.2checkout.com/rest/6.0/orders/{$refno}";
                    $headers = [
                        "Content-Type: application/json",
                        "Accept: application/json",
                        "X-Avangate-Authentication: code=\"{$merchantCode}\" date=\"{$date}\" hash=\"{$hash}\"",
                    ];
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $resp = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if($httpCode === 200 && $resp){
                        $parsed = json_decode($resp,true);
                        $extRef = $parsed['ExternalReference'] ?? $parsed['externalReference'] ?? null;
                        if($extRef && preg_match('/QUOTE-(\d+)/',$extRef,$m)){
                            $autoQuoteId = $m[1];
                            $quote = QuoteRequest::where('id',$autoQuoteId)->where('customer_id',$user->id)->first();
                            if($quote){
                                // Create or adopt payment
                                $payment = Payment::where('payment_id',$refno)->where('customer_id',$user->id)->first();
                                if(!$payment){
                                    $payment = Payment::create([
                                        'quote_request_id'=>$quote->id,
                                        'customer_id'=>$user->id,
                                        'payment_id'=>$refno,
                                        'order_id'=>optional($quote->order)->id,
                                        'amount'=>$quote->quoted_amount,
                                        'currency'=>'USD',
                                        'status'=>'pending',
                                        'payment_method'=>'other',
                                        'payment_data'=>['created_via_external_reference'=>true, 'method'=>'2checkout-inline'],
                                        'transaction_data'=>$parsed,
                                    ]);
                                }
                            }
                        }
                    }
                }
            } catch(\Throwable $e){ Log::warning('ExternalReference resolution failed: '.$e->getMessage()); }
        }
        if(!$payment){
            return response()->json(['ok'=>false,'error'=>'Payment not found after external reference attempt'],404);
        }
        if($payment->status==='completed'){
            // Check if order_id is missing and try to fix it
            if(!$payment->order_id && $payment->quoteRequest && $payment->quoteRequest->order) {
                try {
                    $payment->order_id = $payment->quoteRequest->order->id;
                    $payment->save();
                    Log::info('Fixed missing order_id for completed payment', [
                        'payment_id' => $payment->id, 
                        'order_id' => $payment->order_id
                    ]);
                } catch(\Throwable $e) {
                    Log::warning('Failed to fix missing order_id: ' . $e->getMessage());
                }
            }
            return response()->json([
                'ok'=>true,
                'already'=>true,
                'order_id'=>optional($payment->order)->id,
                'invoice_id'=>optional(optional($payment->order)->invoice)->id
            ]);
        }
        $quote = $payment->quoteRequest;
        if(!$quote){ return response()->json(['ok'=>false,'error'=>'Quote missing'],422); }
        try {
            DB::transaction(function() use (&$payment,&$quote){
                if($quote->isQuoted()) { 
                    $quote->status='accepted'; 
                    $quote->save(); 
                }
                
                // Create order if it doesn't exist (with proper duplicate prevention)
                $quote->refresh(); // Refresh to get latest state
                if(!$quote->order){ 
                    try { 
                        $order = $quote->createOrder(); 
                        // Refresh the relationship again after creation
                        $quote->refresh();
                        Log::info('Order created successfully', ['order_id' => $order->id, 'quote_id' => $quote->id]);
                    } catch(\Throwable $e){ 
                        Log::error('Order creation failed in transaction', [
                            'error' => $e->getMessage(),
                            'quote_id' => $quote->id,
                            'quote_status' => $quote->status,
                            'is_quoted' => $quote->isQuoted(),
                            'is_accepted' => $quote->isAccepted()
                        ]);
                        throw $e; // Re-throw to abort transaction
                    } 
                } else {
                    Log::info('Order already exists, skipping creation', ['quote_id' => $quote->id, 'order_id' => $quote->order->id]);
                }
                
                // Update payment with order_id if order exists
                if($quote->order) {
                    $payment->order_id = $quote->order->id;
                }
                
                $payment->status='completed';
                $payment->paid_at=now();
                $payment->save();
            });
        } catch(\Throwable $e){
            Log::error('Finalize payment transaction failed', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
                'quote_id' => optional($quote)->id
            ]);
            return response()->json(['ok'=>false,'error'=>'Finalize failure: ' . $e->getMessage()],500);
        }
        // Refresh quote to get the latest order relationship
        $quote->refresh();
        $order = $quote->order;
        
        if(!$order){ 
            Log::error('Order still missing after transaction', [
                'quote_id' => $quote->id,
                'quote_status' => $quote->status,
                'payment_id' => $payment->id
            ]);
            return response()->json(['ok'=>false,'error'=>'Order creation failed - order not found after transaction'],500); 
        }
        $invoice = $order->invoice;
        if(!$invoice){
            try {
                $customer = $order->customer;
                $lineItems = [[ 'description'=>$order->title, 'quantity'=>1, 'price'=>$order->amount ]];
                $subtotal = $order->amount;
                $taxRate = 0.10; $taxAmount = $subtotal*$taxRate; $totalAmount = $subtotal + $taxAmount;
                $invoice = Invoice::create([
                    'customer_id'=>$order->customer_id,
                    'order_id'=>$order->id,
                    'customer_name'=>$customer->name,
                    'customer_email'=>$customer->email,
                    'billing_address'=>$customer->billing_address ?? 'Address not provided',
                    'billing_company'=>$customer->billing_company,
                    'subtotal'=>$subtotal,
                    'tax_rate'=>$taxRate,
                    'tax_amount'=>$taxAmount,
                    'total_amount'=>$totalAmount,
                    'currency'=>'USD',
                    'status'=>'paid',
                    'invoice_date'=>now(),
                    'due_date'=>now(),
                    'line_items'=>$lineItems,
                    'notes'=>'Auto invoice for paid order: '.$order->order_number
                ]);
            } catch(\Throwable $e){ Log::error('Invoice create fail: '.$e->getMessage()); }
        }
        return response()->json(['ok'=>true,'order_id'=>$order->id,'invoice_id'=>optional($invoice)->id,'payment_id'=>$payment->id]);
    }
        
    }

