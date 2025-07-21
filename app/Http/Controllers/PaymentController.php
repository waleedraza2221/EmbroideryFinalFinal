<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\QuoteRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }



    /**
     * Handle 2Checkout webhook/callback - Enhanced for inline checkout
     */
    public function webhook(Request $request)
    {
        try {
            Log::info('2Checkout webhook received', $request->all());

            // Check if it's an INS (Instant Notification Service) message
            if ($request->has('message_type')) {
                return $this->handleINSWebhook($request);
            }

            // Handle standard webhook
            $calculatedHash = hash_hmac('sha512', $request->getContent(), config('services.twocheckout.secret_key'));
            $providedHash = $request->header('X-Avangate-Signature');

            if ($providedHash && !hash_equals($calculatedHash, $providedHash)) {
                Log::warning('Invalid webhook signature');
                return response('Invalid signature', 401);
            }

            $data = $request->all();
            
            // Find payment by various identifiers
            $payment = null;
            if (isset($data['merchant_order_id'])) {
                $payment = Payment::find($data['merchant_order_id']);
            } elseif (isset($data['order_number'])) {
                $payment = Payment::where('payment_id', $data['order_number'])->first();
            } elseif (isset($data['REFNO'])) {
                $payment = Payment::where('payment_id', $data['REFNO'])->first();
            }

            if (!$payment) {
                Log::warning('Payment not found for webhook', $data);
                return response('Payment not found', 404);
            }

            // Update payment status based on webhook
            $status = $data['ORDERSTATUS'] ?? $data['order_status'] ?? null;
            if ($status) {
                switch (strtoupper($status)) {
                    case 'COMPLETE':
                    case 'FINISHED':
                        if (!$payment->isCompleted()) {
                            $payment->update([
                                'status' => 'completed',
                                'paid_at' => now(),
                                'transaction_data' => json_encode($data)
                            ]);

                            // Update quote request and create order
                            if ($payment->quoteRequest->status !== 'accepted') {
                                $payment->quoteRequest->update([
                                    'status' => 'accepted',
                                    'responded_at' => now()
                                ]);
                                
                                $this->createOrder($payment->quoteRequest, $payment);
                            }
                        }
                        break;

                    case 'REFUND':
                    case 'REVERSED':
                        $payment->update([
                            'status' => 'refunded',
                            'transaction_data' => json_encode($data)
                        ]);
                        break;

                    case 'PENDING':
                        $payment->update([
                            'status' => 'pending',
                            'transaction_data' => json_encode($data)
                        ]);
                        break;

                    case 'CANCELED':
                    case 'CANCELLED':
                        $payment->update([
                            'status' => 'cancelled',
                            'transaction_data' => json_encode($data)
                        ]);
                        break;
                }
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Webhook processing error: ' . $e->getMessage());
            return response('Error', 500);
        }
    }

    /**
     * Handle 2Checkout INS (Instant Notification Service) webhook
     */
    private function handleINSWebhook(Request $request)
    {
        $data = $request->all();
        
        // Verify INS signature
        $stringToHash = $data['sale_id'] . $data['vendor_id'] . $data['invoice_id'] . config('services.twocheckout.secret_word', '');
        $calculatedHash = strtoupper(md5($stringToHash));
        $providedHash = strtoupper($data['md5_hash'] ?? '');

        if ($calculatedHash !== $providedHash) {
            Log::warning('Invalid INS signature', [
                'calculated' => $calculatedHash,
                'provided' => $providedHash
            ]);
            return response('Invalid signature', 401);
        }

        // Find payment by sale_id or invoice_id
        $payment = Payment::where('payment_id', $data['sale_id'])
            ->orWhere('payment_id', $data['invoice_id'])
            ->first();

        if (!$payment) {
            Log::warning('Payment not found for INS', $data);
            return response('Payment not found', 404);
        }

        // Process based on message type
        switch ($data['message_type']) {
            case 'ORDER_CREATED':
                $payment->update([
                    'status' => 'pending',
                    'transaction_data' => json_encode($data)
                ]);
                break;

            case 'FRAUD_STATUS_CHANGED':
                if ($data['fraud_status'] === 'pass') {
                    $payment->update(['status' => 'completed']);
                    
                    // Create order if payment is completed
                    if ($payment->quoteRequest->status !== 'accepted') {
                        $payment->quoteRequest->update(['status' => 'accepted']);
                        $this->createOrder($payment->quoteRequest, $payment);
                    }
                } else {
                    $payment->update(['status' => 'failed']);
                }
                break;

            case 'INVOICE_STATUS_CHANGED':
                if ($data['invoice_status'] === 'deposited') {
                    $payment->update(['status' => 'completed']);
                }
                break;
        }

        return response('OK', 200);
    }

    /**
     * Create order after successful payment
     */
    private function createOrder(QuoteRequest $quoteRequest, Payment $payment)
    {
        if ($quoteRequest->order) {
            return $quoteRequest->order; // Order already exists
        }

        $order = Order::create([
            'order_number' => 'ORD-' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT),
            'quote_request_id' => $quoteRequest->id,
            'customer_id' => $quoteRequest->customer_id,
            'title' => $quoteRequest->title ?: 'Custom Order',
            'instructions' => $quoteRequest->instructions,
            'original_files' => $quoteRequest->files,
            'amount' => $payment->amount,
            'delivery_days' => $quoteRequest->delivery_days ?: 7,
            'due_date' => now()->addDays($quoteRequest->delivery_days ?: 7),
            'status' => 'active'
        ]);

        Log::info('Order created after payment', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'quote_request_id' => $quoteRequest->id
        ]);

        return $order;
    }











    /**
     * Redirect to 2Checkout Convert Plus with dynamic parameters
     */
    public function convertPlusRedirect(QuoteRequest $quoteRequest)
    {
        // Ensure customer can only pay for their own quotes
        if ($quoteRequest->customer_id !== auth()->id()) {
            abort(403);
        }

        // Check if quote is ready for payment
        if ($quoteRequest->status !== 'quoted') {
            return redirect()->route('quote-requests.show', $quoteRequest)
                ->with('error', 'This quote is not ready for payment.');
        }

        // Generate signature for 2Checkout using JSON method
        $signature = $this->generateJSONSignature($quoteRequest);
        
        // Clean product title for URL (same cleaning as in signature)
        $cleanTitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $quoteRequest->title);
        $cleanTitle = trim($cleanTitle);
        if (empty($cleanTitle)) {
            $cleanTitle = 'Embroidery Product';
        }

        // Build 2Checkout URL - using secure.2checkout.com
        $baseUrl = 'https://secure.2checkout.com/checkout/buy';
        
        $params = [
            'merchant' => config('services.twocheckout.account_number'),
            'currency' => 'USD',
            'tpl' => 'default',
            'dynamic' => '1',
            'return-type' => 'redirect',
            'return-url' => route('payment.thankyou'),
            'prod' => $cleanTitle,
            'price' => $quoteRequest->quoted_amount,
            'type' => 'digital',
            'qty' => '1',
        ];
        
        // Add signature only if we have a valid secret key
        $secretKey = config('services.twocheckout.secret_key');
        if (!empty($secretKey)) {
            $params['signature'] = $signature;
        }
        
        // Add test parameter at the end
        $params['test'] = '1';

        $redirectUrl = $baseUrl . '?' . http_build_query($params);

        Log::info('Redirecting to 2Checkout Convert Plus (JSON signature)', [
            'quote_request_id' => $quoteRequest->id,
            'amount' => $quoteRequest->quoted_amount,
            'signature' => $signature,
            'base_url' => $baseUrl,
            'all_params' => $params,
            'url' => $redirectUrl
        ]);

        // If we're getting blocked, provide an alternative approach
        // Store the quote request ID in session for fallback
        session(['payment_quote_request_id' => $quoteRequest->id]);

        return redirect($redirectUrl);
    }

    /**
     * Simple redirect without signature for testing
     */
    public function convertPlusSimple(QuoteRequest $quoteRequest)
    {
        // Ensure customer can only pay for their own quotes
        if ($quoteRequest->customer_id !== auth()->id()) {
            abort(403);
        }

        // Check if quote is ready for payment
        if ($quoteRequest->status !== 'quoted') {
            return redirect()->route('quote-requests.show', $quoteRequest)
                ->with('error', 'This quote is not ready for payment.');
        }

        // Clean product title for URL
        $cleanTitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $quoteRequest->title);
        $cleanTitle = trim($cleanTitle);
        if (empty($cleanTitle)) {
            $cleanTitle = 'Embroidery Product';
        }

        // Build simple 2Checkout URL without signature
        $baseUrl = 'https://sandbox.2checkout.com/checkout/buy';
        $params = [
            'merchant' => config('services.twocheckout.account_number'),
            'currency' => 'USD',
            'prod' => $cleanTitle,
            'price' => $quoteRequest->quoted_amount,
            'qty' => '1',
            'return-url' => route('payment.thankyou'),
            'test' => '1'
        ];

        $redirectUrl = $baseUrl . '?' . http_build_query($params);

        Log::info('Redirecting to 2Checkout Simple (no signature)', [
            'quote_request_id' => $quoteRequest->id,
            'amount' => $quoteRequest->quoted_amount,
            'url' => $redirectUrl
        ]);

        session(['payment_quote_request_id' => $quoteRequest->id]);

        return redirect($redirectUrl);
    }

    /**
     * Convert Plus redirect using JSON signature method
     */
    public function convertPlusJSON(QuoteRequest $quoteRequest)
    {
        // Ensure customer can only pay for their own quotes
        if ($quoteRequest->customer_id !== auth()->id()) {
            abort(403);
        }

        // Check if quote is ready for payment
        if ($quoteRequest->status !== 'quoted') {
            return redirect()->route('quote-requests.show', $quoteRequest)
                ->with('error', 'This quote is not ready for payment.');
        }

        // Generate JSON-based signature
        $signature = $this->generateJSONSignature($quoteRequest);
        
        // Clean product title for URL
        $cleanTitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $quoteRequest->title);
        $cleanTitle = trim($cleanTitle);
        if (empty($cleanTitle)) {
            $cleanTitle = 'Embroidery Product';
        }

        // Build 2Checkout URL using secure endpoint
        $baseUrl = 'https://secure.2checkout.com/checkout/buy';
        
        $params = [
            'merchant' => config('services.twocheckout.account_number'),
            'currency' => 'USD',
            'tpl' => 'default',
            'dynamic' => '1',
            'return-type' => 'redirect',
            'return-url' => route('payment.thankyou'),
            'prod' => $cleanTitle,
            'price' => $quoteRequest->quoted_amount,
            'type' => 'digital',
            'qty' => '1',
        ];
        
        // Add signature
        if (!empty($signature)) {
            $params['signature'] = $signature;
        }
        
        // Add test parameter at the end
        $params['test'] = '1';

        $redirectUrl = $baseUrl . '?' . http_build_query($params);

        Log::info('Redirecting to 2Checkout with JSON signature', [
            'quote_request_id' => $quoteRequest->id,
            'amount' => $quoteRequest->quoted_amount,
            'signature' => $signature,
            'url' => $redirectUrl
        ]);

        session(['payment_quote_request_id' => $quoteRequest->id]);

        return redirect($redirectUrl);
    }

    /**
     * Generate signature for 2Checkout Convert Plus
     * Following official documentation: https://knowledgecenter.2checkout.com/Documentation/07Checkout_Options/Conversion_Plus/01Generating_a_Conversion_Plus_Buy_Link/01Using_Buy_Link_Parameters#ConvertPlus_signature
     */
    private function generateConvertPlusSignature(QuoteRequest $quoteRequest)
    {
        $secretKey = config('services.twocheckout.secret_key');
        
        // Ensure we have a secret key
        if (empty($secretKey)) {
            Log::warning('No secret key configured for 2Checkout');
            return '';
        }
        
        // Clean the product title to avoid special characters
        $cleanTitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $quoteRequest->title);
        $cleanTitle = trim($cleanTitle);
        if (empty($cleanTitle)) {
            $cleanTitle = 'Embroidery Product';
        }
        
        // Parameters that require signature for ConvertPlus according to documentation
        // Only include parameters that require signature based on your example URL
        $params = [
            'currency' => 'USD',
            'dynamic' => '1',
            'price' => (string)$quoteRequest->quoted_amount,
            'prod' => $cleanTitle,
            'qty' => '1',
            'type' => 'digital'
        ];
        
        // Sort parameters alphabetically (required by 2Checkout)
        ksort($params);
        
        // Serialize each parameter value according to 2Checkout specification:
        // Prepend the byte length of the value to the value itself
        $serializedValues = [];
        foreach ($params as $key => $value) {
            $valueStr = (string)$value;
            // Get byte length (UTF-8 byte count)
            $byteLength = strlen($valueStr);
            $serialized = $byteLength . $valueStr;
            $serializedValues[] = $serialized;
            
            Log::debug("Serializing parameter", [
                'key' => $key,
                'value' => $valueStr,
                'byte_length' => $byteLength,
                'serialized' => $serialized
            ]);
        }
        
        // Concatenate all serialized values
        $concatenated = implode('', $serializedValues);
        
        // Generate HMAC SHA256 signature using the secret key
        $signature = hash_hmac('sha256', $concatenated, $secretKey);

        Log::info('Generated Convert Plus signature', [
            'quote_request_id' => $quoteRequest->id,
            'original_title' => $quoteRequest->title,
            'clean_title' => $cleanTitle,
            'signature_params' => $params,
            'serialized_values' => $serializedValues,
            'concatenated_string' => $concatenated,
            'concatenated_length' => strlen($concatenated),
            'secret_key_length' => strlen($secretKey),
            'final_signature' => $signature
        ]);

        return $signature;
    }

    /**
     * Generate signature using JSON stringify approach (like Node.js example)
     */
    private function generateJSONSignature(QuoteRequest $quoteRequest)
    {
        $secretKey = config('services.twocheckout.secret_key');
        
        if (empty($secretKey)) {
            Log::warning('No secret key configured for JSON signature');
            return '';
        }
        
        // Create payload exactly like the Node.js example
        $payload = [
            'merchant' => config('services.twocheckout.account_number'),
            'currency' => 'USD',
            'amount' => number_format($quoteRequest->quoted_amount, 2, '.', '')
        ];
        
        // Generate signature using JSON stringify + HMAC SHA256 + base64
        $jsonString = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $hmacHash = hash_hmac('sha256', $jsonString, $secretKey, true);
        $signature = base64_encode($hmacHash);
        
        Log::info('Generated JSON signature (Node.js style)', [
            'quote_request_id' => $quoteRequest->id,
            'payload' => $payload,
            'json_string' => $jsonString,
            'secret_key_length' => strlen($secretKey),
            'signature' => $signature
        ]);
        
        return $signature;
    }

    /**
     * Generate direct 2Checkout URL for use in templates
     * This can be called directly without redirecting through controller
     */
    public static function generateDirectPaymentURL(QuoteRequest $quoteRequest)
    {
        $secretKey = config('services.twocheckout.secret_key');
        
        // Clean product title for URL
        $cleanTitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $quoteRequest->title);
        $cleanTitle = trim($cleanTitle);
        if (empty($cleanTitle)) {
            $cleanTitle = 'Embroidery Product';
        }

        // Generate JSON signature
        $signature = '';
        if (!empty($secretKey)) {
            $payload = [
                'merchant' => config('services.twocheckout.account_number'),
                'currency' => 'USD',
                'amount' => number_format($quoteRequest->quoted_amount, 2, '.', '')
            ];
            
            $jsonString = json_encode($payload, JSON_UNESCAPED_SLASHES);
            $hmacHash = hash_hmac('sha256', $jsonString, $secretKey, true);
            $signature = base64_encode($hmacHash);
        }

        // Build 2Checkout URL parameters
        $baseUrl = 'https://secure.2checkout.com/checkout/buy';
        
        $params = [
            'merchant' => config('services.twocheckout.account_number'),
            'currency' => 'USD',
            'tpl' => 'default',
            'dynamic' => '1',
            'return-type' => 'redirect',
            'return-url' => route('payment.thankyou'),
            'prod' => $cleanTitle,
            'price' => $quoteRequest->quoted_amount,
            'type' => 'digital',
            'qty' => '1',
        ];
        
        // Add signature if available
        if (!empty($signature)) {
            $params['signature'] = $signature;
        }
        
        // Add test parameter at the end
        $params['test'] = '1';

        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Generate 2Checkout ConvertPlus payload for JavaScript SDK
     */
    public function generateConvertPlusPayload(QuoteRequest $quoteRequest)
    {
        // Ensure customer can only pay for their own quotes
        if ($quoteRequest->customer_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if quote is ready for payment
        if ($quoteRequest->status !== 'quoted') {
            return response()->json(['error' => 'Quote not ready for payment'], 400);
        }

        $secretKey = config('services.twocheckout.secret_key');
        $sellerId = config('services.twocheckout.account_number');
        
        // Clean product title
        $cleanTitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $quoteRequest->title);
        $cleanTitle = trim($cleanTitle);
        if (empty($cleanTitle)) {
            $cleanTitle = 'Embroidery Product';
        }

        // Create the payload for ConvertPlus
        $payload = [
            'sellerId' => $sellerId,
            'publishableKey' => config('services.twocheckout.publishable_key'),
            'ccTokenId' => '',
            'billingAddr' => [
                'name' => auth()->user()->name,
                'addrLine1' => '',
                'city' => '',
                'state' => '',
                'zipCode' => '',
                'country' => 'US',
                'email' => auth()->user()->email
            ],
            'shippingAddr' => [
                'name' => auth()->user()->name,
                'addrLine1' => '',
                'city' => '',
                'state' => '',
                'zipCode' => '',
                'country' => 'US'
            ],
            'dynamicDescriptor' => 'Embroidery Service',
            'chargeAmount' => $quoteRequest->quoted_amount,
            'currency' => 'USD',
            'scaCompliant' => false,
            'paymentMethod' => [
                'type' => 'EES_TOKEN_PAYMENT'
            ],
            'customer' => [
                'email' => auth()->user()->email,
                'phone' => auth()->user()->phone ?? ''
            ]
        ];

        // Generate signature for ConvertPlus
        $signature = '';
        if (!empty($secretKey)) {
            // For ConvertPlus, we sign the JSON payload
            $jsonString = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $hmacHash = hash_hmac('sha256', $jsonString, $secretKey, true);
            $signature = base64_encode($hmacHash);
        }

        Log::info('Generated ConvertPlus payload', [
            'quote_request_id' => $quoteRequest->id,
            'payload' => $payload,
            'signature' => $signature
        ]);

        return response()->json([
            'payload' => $payload,
            'signature' => $signature,
            'sellerId' => $sellerId
        ]);
    }

    /**
     * Generate ConvertPlus URL with signature for direct redirect
     */
    public function generateConvertPlusURL(QuoteRequest $quoteRequest)
    {
        // Ensure customer can only pay for their own quotes
        if ($quoteRequest->customer_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if quote is ready for payment
        if ($quoteRequest->status !== 'quoted') {
            return response()->json(['error' => 'Quote not ready for payment'], 400);
        }

        $merchantCode = config('services.twocheckout.account_number');
        $secretWord = config('services.twocheckout.secret_key'); // Using secret_key as secret word
        
        // Clean product title
        $cleanTitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $quoteRequest->title);
        $cleanTitle = trim($cleanTitle);
        if (empty($cleanTitle)) {
            $cleanTitle = 'Embroidery Product';
        }

        // ConvertPlus parameters
        $params = [
            'merchant' => $merchantCode,
            'currency' => 'USD',
            'tpl' => 'default',
            'dynamic' => '1',
            'return-type' => 'redirect',
            'return-url' => route('payment.thankyou'),
            'prod' => $cleanTitle,
            'price' => number_format($quoteRequest->quoted_amount, 2, '.', ''),
            'type' => 'digital',
            'qty' => '1',
            'test' => '1'
        ];

        // Generate signature for ConvertPlus
        $signature = '';
        if (!empty($secretWord)) {
            // Create parameters that need to be signed (alphabetically sorted)
            $signatureParams = [
                'currency' => $params['currency'],
                'dynamic' => $params['dynamic'],
                'price' => $params['price'],
                'prod' => $params['prod'],
                'qty' => $params['qty'],
                'type' => $params['type']
            ];
            
            ksort($signatureParams);
            
            // Serialize parameters (prepend length to each value)
            $serializedValues = [];
            foreach ($signatureParams as $key => $value) {
                $valueStr = (string)$value;
                $length = strlen($valueStr);
                $serializedValues[] = $length . $valueStr;
            }
            
            // Concatenate and generate HMAC SHA256
            $concatenated = implode('', $serializedValues);
            $signature = hash_hmac('sha256', $concatenated, $secretWord);
            
            // Add signature to parameters
            $params['signature'] = $signature;
        }

        // Build final URL
        $baseUrl = 'https://secure.2checkout.com/checkout/buy';
        $url = $baseUrl . '?' . http_build_query($params);

        Log::info('Generated ConvertPlus URL with signature', [
            'quote_request_id' => $quoteRequest->id,
            'merchant' => $merchantCode,
            'amount' => $params['price'],
            'signature_params' => $signatureParams ?? [],
            'signature' => $signature,
            'url' => $url
        ]);

        return response()->json([
            'url' => $url,
            'signature' => $signature,
            'amount' => $params['price']
        ]);
    }

    /**
     * Thank you page after payment - verify payment status
     */
    public function thankYou(Request $request)
    {
        Log::info('Payment thank you page accessed', [
            'user_id' => auth()->id(),
            'query_params' => $request->all()
        ]);

        // Get payment parameters from 2Checkout
        $orderNumber = $request->get('order_number');
        $invoiceId = $request->get('invoice_id');
        
        if (!$orderNumber || !$invoiceId) {
            return view('payments.thank-you', [
                'status' => 'error',
                'message' => 'Payment verification failed - missing parameters.'
            ]);
        }

        try {
            // Verify payment with 2Checkout API
            $paymentStatus = $this->verify2CheckoutPayment($orderNumber, $invoiceId);
            
            if ($paymentStatus === 'COMPLETE') {
                // Find the quote request based on product name or order reference
                $quoteRequest = $this->findQuoteFromPayment($request);
                
                if ($quoteRequest) {
                    // Create order and update quote status
                    $this->processSuccessfulPayment($quoteRequest, $orderNumber, $invoiceId);
                    
                    return view('payments.thank-you', [
                        'status' => 'success',
                        'message' => 'Payment completed successfully! Your order has been created.',
                        'quote_request' => $quoteRequest
                    ]);
                }
            }
            
            return view('payments.thank-you', [
                'status' => 'pending',
                'message' => 'Payment is being processed. Please check your email for updates.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'order_number' => $orderNumber,
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
            
            return view('payments.thank-you', [
                'status' => 'error',
                'message' => 'Payment verification failed. Please contact support.'
            ]);
        }
    }

    /**
     * Verify payment status with 2Checkout API
     */
    private function verify2CheckoutPayment($orderNumber, $invoiceId)
    {
        // This would integrate with 2Checkout's API to verify payment
        // For now, return COMPLETE for testing
        return 'COMPLETE';
    }

    /**
     * Find quote request from payment parameters
     */
    private function findQuoteFromPayment(Request $request)
    {
        // Try to find quote request from product name or other parameters
        $productName = $request->get('prod');
        
        if ($productName) {
            return QuoteRequest::where('title', urldecode($productName))
                             ->where('customer_id', auth()->id())
                             ->where('status', 'quoted')
                             ->first();
        }
        
        return null;
    }

    /**
     * Process successful payment and create order
     */
    private function processSuccessfulPayment(QuoteRequest $quoteRequest, $orderNumber, $invoiceId)
    {
        // Create or update payment record
        $payment = Payment::updateOrCreate([
            'quote_request_id' => $quoteRequest->id,
        ], [
            'customer_id' => $quoteRequest->customer_id,
            'payment_id' => $orderNumber,
            'amount' => $quoteRequest->quoted_amount,
            'currency' => 'USD',
            'payment_method' => 'card',
            'status' => 'completed',
            'payment_data' => json_encode([
                'order_number' => $orderNumber,
                'invoice_id' => $invoiceId,
                'provider' => '2checkout'
            ])
        ]);

        // Update quote request status
        $quoteRequest->update(['status' => 'accepted']);

        // Create order
        $order = Order::create([
            'customer_id' => $quoteRequest->customer_id,
            'quote_request_id' => $quoteRequest->id,
            'payment_id' => $payment->id,
            'order_number' => 'ORD-' . time() . '-' . $quoteRequest->id,
            'status' => 'pending',
            'instructions' => $quoteRequest->instructions,
            'total_amount' => $quoteRequest->quoted_amount,
            'delivery_days' => $quoteRequest->delivery_days,
            'original_files' => $quoteRequest->files,
        ]);

        Log::info('Order created from Convert Plus payment', [
            'quote_request_id' => $quoteRequest->id,
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'order_number' => $orderNumber
        ]);

        return $order;
    }
}
