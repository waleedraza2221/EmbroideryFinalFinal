<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Admin\QuoteRequestController as AdminQuoteRequestController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// User routes (protected)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    
    Route::get('/profile/edit', [UserController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [UserController::class, 'update'])->name('profile.update');
    Route::put('/profile/update-basic', [UserController::class, 'updateBasic'])->name('profile.update.basic');
    Route::put('/profile/update-billing', [UserController::class, 'updateBilling'])->name('profile.update.billing');
    
    // Quote Request routes
    Route::resource('quote-requests', QuoteRequestController::class)->except(['edit', 'update', 'destroy']);
    Route::post('/quote-requests/{quoteRequest}/accept', [QuoteRequestController::class, 'acceptQuote'])->name('quote-requests.accept');
    Route::get('/quote-requests/{quoteRequest}/files/{fileIndex}', [QuoteRequestController::class, 'downloadFile'])->name('quote-requests.download-file');
    
    // Order routes
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::get('/orders/{order}/delivery-files/{fileIndex}', [OrderController::class, 'downloadDeliveryFile'])->name('orders.download-delivery-file');
    Route::get('/orders/{order}/original-files/{fileIndex}', [OrderController::class, 'downloadOriginalFile'])->name('orders.download-original-file');
    
    // Payment routes (for customers)
    Route::get('/payments/{quoteRequest}/initiate', [PaymentController::class, 'initiatePayment'])->name('payment.initiate');
    Route::get('/payments/{quoteRequest}/convertplus', [PaymentController::class, 'convertPlusRedirect'])->name('payment.convertplus');
    Route::get('/payments/{quoteRequest}/convertplus-simple', [PaymentController::class, 'convertPlusSimple'])->name('payment.convertplus.simple');
    Route::get('/payments/{quoteRequest}/convertplus-json', [PaymentController::class, 'convertPlusJSON'])->name('payment.convertplus.json');
    Route::post('/payments/{quoteRequest}/convertplus-payload', [PaymentController::class, 'generateConvertPlusPayload'])->name('payment.convertplus.payload');
    Route::post('/payments/{quoteRequest}/convertplus-url', [PaymentController::class, 'generateConvertPlusURL'])->name('payment.convertplus.url');
    Route::get('/payments/thank-you', [PaymentController::class, 'thankYou'])->name('payment.thankyou');
    Route::post('/payments/{payment}/process', [PaymentController::class, 'processPayment'])->name('payment.process');
    Route::post('/payments/{quoteRequest}/process-token', [PaymentController::class, 'processToken'])->name('payment.process.token');
    Route::post('/payments/{quoteRequest}/process-popup', [PaymentController::class, 'processPopup'])->name('payment.process.popup');
    Route::post('/payments/{quoteRequest}/process-inline', [PaymentController::class, 'processInline'])->name('payment.process.inline');
    Route::get('/payments/{payment}/status', [PaymentController::class, 'status'])->name('payment.status');
    Route::get('/payments/{quoteRequest}/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
    Route::post('/payments/{quoteRequest}/mock', [PaymentController::class, 'mockPayment'])->name('payment.mock');
    
    // Invoice routes (for customers)
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download/{format?}', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::get('/invoices/export/{format?}', [InvoiceController::class, 'exportAll'])->name('invoices.export');
});

// Admin routes (protected)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Admin user management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
    // Admin Quote Request management
    Route::resource('quote-requests', AdminQuoteRequestController::class)->only(['index', 'show', 'update']);
    Route::post('/quote-requests/{quoteRequest}/reject', [AdminQuoteRequestController::class, 'reject'])->name('quote-requests.reject');
    Route::get('/quote-requests/{quoteRequest}/files/{fileIndex}', [AdminQuoteRequestController::class, 'downloadFile'])->name('quote-requests.download-file');
    Route::get('/quote-requests/stats', [AdminQuoteRequestController::class, 'stats'])->name('quote-requests.stats');
    
    // Admin Order management
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show']);
    Route::post('/orders/{order}/deliver', [AdminOrderController::class, 'deliver'])->name('orders.deliver');
    Route::get('/orders/{order}/original-files/{fileIndex}', [AdminOrderController::class, 'downloadOriginalFile'])->name('orders.download-original-file');
    Route::get('/orders/{order}/delivery-files/{fileIndex}', [AdminOrderController::class, 'downloadDeliveryFile'])->name('orders.download-delivery-file');
    Route::get('/orders/stats', [AdminOrderController::class, 'stats'])->name('orders.stats');
    
    // Admin Invoice management
    Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download/{format?}', [AdminInvoiceController::class, 'download'])->name('invoices.download');
    Route::get('/invoices/export/{format?}', [AdminInvoiceController::class, 'exportAll'])->name('invoices.export');
    Route::post('/invoices/bulk-export/{format?}', [AdminInvoiceController::class, 'bulkExport'])->name('invoices.bulk-export');
    Route::put('/invoices/{invoice}/status', [AdminInvoiceController::class, 'updateStatus'])->name('invoices.update-status');
});

// Public webhook route (no authentication required)
Route::post('/payments/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

// 2Checkout URL Test Page
Route::get('/test-2checkout-urls', function () {
    return view('test-2checkout-urls');
})->name('test.2checkout.urls');

// 2Checkout Inline Checkout Test
Route::get('/test-inline-checkout', function () {
    return view('test-2checkout-inline');
})->name('test.inline.checkout');

// 2Checkout InLine Checkout Test (updated)
Route::get('/test-2checkout-inline', function () {
    return view('test-2checkout-inline');
})->name('test.2checkout.inline');

// 2Checkout Locked Cart Test
Route::get('/test-2checkout-locked', function () {
    return view('test-2checkout-locked');
})->name('test.2checkout.locked');

// Webhook test route (for debugging)
Route::get('/test-webhook', function() {
    return response()->json([
        'webhook_url' => route('payment.webhook'),
        'webhook_method' => 'POST',
        'environment' => app()->environment(),
        'timestamp' => now()->toISOString()
    ]);
})->name('test.webhook');

// Signature test route (for debugging different signature methods)
Route::get('/test-signature/{quoteRequestId}', function($quoteRequestId) {
    $quoteRequest = \App\Models\QuoteRequest::findOrFail($quoteRequestId);
    $secretKey = config('services.twocheckout.secret_key');
    
    // Method 1: ConvertPlus official method (current implementation)
    $cleanTitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $quoteRequest->title);
    $cleanTitle = trim($cleanTitle) ?: 'Embroidery Product';
    
    $params = [
        'currency' => 'USD',
        'dynamic' => '1',
        'price' => (string)$quoteRequest->quoted_amount,
        'prod' => $cleanTitle,
        'qty' => '1',
        'type' => 'digital'
    ];
    ksort($params);
    
    $serializedValues = [];
    foreach ($params as $key => $value) {
        $valueStr = (string)$value;
        $byteLength = strlen($valueStr);
        $serializedValues[] = $byteLength . $valueStr;
    }
    $concatenated = implode('', $serializedValues);
    $signature1 = hash_hmac('sha256', $concatenated, $secretKey);
    
    // Method 2: JSON stringify approach (like your Node.js example)
    $payload = [
        'merchant' => config('services.twocheckout.account_number'),
        'currency' => 'USD',
        'amount' => $quoteRequest->quoted_amount
    ];
    $signature2 = base64_encode(hash_hmac('sha256', json_encode($payload), $secretKey, true));
    
    // Method 3: Simple concatenation
    $simpleString = $params['currency'] . $params['dynamic'] . $params['price'] . $params['prod'] . $params['qty'] . $params['type'];
    $signature3 = hash_hmac('sha256', $simpleString, $secretKey);
    
    return response()->json([
        'quote_request_id' => $quoteRequest->id,
        'amount' => $quoteRequest->quoted_amount,
        'title' => $quoteRequest->title,
        'clean_title' => $cleanTitle,
        'secret_key_length' => strlen($secretKey),
        'methods' => [
            'convertplus_official' => [
                'params' => $params,
                'serialized' => $serializedValues,
                'concatenated' => $concatenated,
                'signature' => $signature1
            ],
            'json_stringify' => [
                'payload' => $payload,
                'json' => json_encode($payload),
                'signature' => $signature2
            ],
            'simple_concat' => [
                'string' => $simpleString,
                'signature' => $signature3
            ]
        ]
    ]);
})->name('test.signature');


