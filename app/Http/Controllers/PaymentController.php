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
     * Generate 2Checkout payment URL for Blade views
     * This static method can be called directly from Blade templates
     */
    public static function generatePaymentURL(QuoteRequest $quoteRequest)
    {
        $merchantCode = config('services.twocheckout.account_number'); // 255036765830
        $secretWord = config('services.twocheckout.secret_word'); // From TWOCHECKOUT_SECRET_WORD
        
        // Clean product title - using the same as your working example
        $cleanTitle = 'Embroidery Digitize';

        // Parameters that require signature (must be sorted alphabetically)
        $currency = 'USD';
        $price = (string)$quoteRequest->quoted_amount;
        $qty = '1';
        $type = 'digital';
        
        // Create signature following 2Checkout specification:
        // 1. Extract signature-required parameters
        // 2. Sort alphabetically: currency, price, prod, qty, type
        // 3. Serialize values with length prefix
        // 4. Concatenate and encrypt with HMAC SHA256
        
        $signatureParams = [
            'currency' => $currency,
            'price' => $price,
            'prod' => $cleanTitle,
            'qty' => $qty,
            'type' => $type
        ];
        
        // Sort alphabetically by key
        ksort($signatureParams);
        
        // Serialize each value (prepend length)
        $serializedValues = [];
        foreach ($signatureParams as $key => $value) {
            $serializedValues[] = strlen($value) . $value;
        }
        
        // Concatenate all serialized values
        $dataToSign = implode('', $serializedValues);
        
        // Generate HMAC SHA256 signature
        $signature = hash_hmac('sha256', $dataToSign, $secretWord);

        // ConvertPlus parameters - fixed for proper cart loading
        $params = [
            // Required parameters
            'merchant' => $merchantCode,
            'currency' => $currency,
            'dynamic' => '1',
            'prod' => $cleanTitle,
            'price' => $price,
            'type' => $type,
            'qty' => $qty,
            'signature' => $signature,
            
            // Cart behavior - Fixed
            'tpl' => 'default',
            'return-url' => 'https://embroiderydigitize.com/thank-you/',
            'return-type' => 'redirect',
            
            // Customer information pre-fill
            'name' => $quoteRequest->customer->name,
            'email' => $quoteRequest->customer->email,
            'phone' => $quoteRequest->customer->phone ?? '',
            
            // Order tracking
            'order-ext-ref' => 'QUOTE-' . $quoteRequest->id,
            'customer-ext-ref' => 'CUSTOMER-' . $quoteRequest->customer_id,
            'src' => 'quote-acceptance',
            
            // Additional product information
            'description' => 'Embroidery digitization service for: ' . ($quoteRequest->title ?? 'Custom Design'),
            'tangible' => '0', // Digital product
            
            // Cart settings - Fixed to load cart properly
            'language' => 'en'
            // Removed 'empty-cart' => '1' and 'test' => '1' as they cause cart issues
        ];

        // Build final URL
        $baseUrl = 'https://secure.2checkout.com/checkout/buy';
        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Payment waiting/verification page
     */
    public function paymentWaiting(Request $request, $quoteId)
    {
        $quoteRequest = QuoteRequest::where('id', $quoteId)
                                  ->where('customer_id', auth()->id())
                                  ->firstOrFail();

        Log::info('Payment waiting page accessed', [
            'quote_request_id' => $quoteId,
            'user_id' => auth()->id(),
            'query_params' => $request->all()
        ]);

        // Check if we have payment confirmation parameters from 2Checkout
        $orderNumber = $request->get('order_number');
        $invoiceId = $request->get('invoice_id');
        $key = $request->get('key');

        if ($orderNumber && $invoiceId) {
            // Process the successful payment
            $this->processPaymentCallback($quoteRequest, $request->all());
            
            return view('payments.waiting', [
                'quoteRequest' => $quoteRequest,
                'status' => 'processing',
                'message' => 'Payment received! We are processing your order.',
                'orderNumber' => $orderNumber,
                'invoiceId' => $invoiceId
            ]);
        }

        // If no payment parameters, show waiting page
        return view('payments.waiting', [
            'quoteRequest' => $quoteRequest,
            'status' => 'waiting',
            'message' => 'Waiting for payment confirmation...',
        ]);
    }

    /**
     * Thank You page after 2Checkout payment
     */
    public function thankYou(Request $request)
    {
        try {
            Log::info('Thank you page accessed', [
                'query_params' => $request->all(),
                'user_id' => auth()->id() ?? 'guest'
            ]);

            // Get parameters from 2Checkout return URL
            $orderNumber = $request->get('order_number');
            $invoiceId = $request->get('invoice_id');
            $merchantOrderId = $request->get('merchant_order_id');
            $total = $request->get('total');
            $key = $request->get('key');

            // If we have payment parameters, find the quote request
            $quoteRequest = null;
            if ($merchantOrderId) {
                $quoteRequest = QuoteRequest::find($merchantOrderId);
            }

            // Return the thank you page with the parameters
            // The webhook will handle the actual order creation
            return view('payments.thank-you', [
                'orderNumber' => $orderNumber,
                'invoiceId' => $invoiceId,
                'merchantOrderId' => $merchantOrderId,
                'total' => $total,
                'key' => $key,
                'quoteRequest' => $quoteRequest,
                'hasPaymentData' => !empty($orderNumber) && !empty($invoiceId)
            ]);

        } catch (\Exception $e) {
            Log::error('Thank you page error: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            
            return view('payments.thank-you', [
                'hasPaymentData' => false,
                'error' => 'Unable to process payment information'
            ]);
        }
    }

    /**
     * Process payment callback from 2Checkout
     */
    private function processPaymentCallback(QuoteRequest $quoteRequest, array $paymentData)
    {
        try {
            // Create or update payment record
            $payment = Payment::updateOrCreate([
                'quote_request_id' => $quoteRequest->id,
            ], [
                'customer_id' => $quoteRequest->customer_id,
                'payment_id' => $paymentData['order_number'] ?? $paymentData['invoice_id'] ?? 'PENDING',
                'amount' => $quoteRequest->quoted_amount,
                'currency' => 'USD',
                'payment_method' => 'card',
                'status' => 'completed',
                'payment_data' => json_encode($paymentData),
                'paid_at' => now()
            ]);

            // Update quote request status
            $quoteRequest->update([
                'status' => 'accepted',
                'responded_at' => now()
            ]);

            // Create order
            $order = Order::create([
                'customer_id' => $quoteRequest->customer_id,
                'quote_request_id' => $quoteRequest->id,
                'payment_id' => $payment->id,
                'order_number' => 'ORD-' . time() . '-' . $quoteRequest->id,
                'title' => $quoteRequest->title ?: 'Embroidery Order',
                'status' => 'pending',
                'instructions' => $quoteRequest->instructions,
                'total_amount' => $quoteRequest->quoted_amount,
                'delivery_days' => $quoteRequest->delivery_days,
                'original_files' => $quoteRequest->files,
                'due_date' => now()->addDays($quoteRequest->delivery_days ?: 7),
                'amount' => $quoteRequest->quoted_amount
            ]);

            Log::info('Order created from payment callback', [
                'quote_request_id' => $quoteRequest->id,
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'order_number' => $order->order_number
            ]);

            // Associate order with quote request
            $quoteRequest->update(['order_id' => $order->id]);

            return $order;
            
        } catch (\Exception $e) {
            Log::error('Payment callback processing failed', [
                'quote_request_id' => $quoteRequest->id,
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);
            throw $e;
        }
    }
}
