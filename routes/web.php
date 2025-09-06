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
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Models\Testimonial;

// Public routes
// Root route: also accepts POST to gracefully handle legacy 2Checkout IPN hitting '/?wc-api=2checkout_ipn_inline'
Route::match(['GET','POST'], '/', [HomeController::class, 'index'])->name('home');

// Static marketing pages
Route::get('/about', [PageController::class,'about'])->name('about');
Route::get('/contact', [PageController::class,'contact'])->name('contact');
Route::prefix('services')->name('services.')->group(function(){
    Route::get('/embroidery-digitizing', [PageController::class,'serviceEmbroidery'])->name('embroidery');
    Route::get('/stitch-estimator', [PageController::class,'serviceStitchEstimator'])->name('stitch');
    Route::get('/vector-tracing', [PageController::class,'serviceVectorTracing'])->name('vector');
});

// Newsletter
Route::post('/newsletter/subscribe', [NewsletterController::class,'subscribe'])->name('newsletter.subscribe');
Route::post('/newsletter/unsubscribe', [NewsletterController::class,'unsubscribe'])->name('newsletter.unsubscribe');
Route::get('/newsletter/confirm/{token}', [NewsletterController::class,'confirm'])->name('newsletter.confirm');


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
    
    // Payment inline finalize (AJAX)
    Route::post('/payments/inline/finalized', [PaymentController::class, 'inlineFinalized'])->name('payments.inline.finalized');
    
    // Invoice routes (for customers)
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download/{format?}', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::get('/invoices/export/{format?}', [InvoiceController::class, 'exportAll'])->name('invoices.export');
    
    // Notification routes
    Route::post('/notifications/{notification}/read', function($notification){
        $n = auth()->user()->notifications()->where('id',$notification)->firstOrFail();
        if(!$n->read_at){ $n->read_at = now(); $n->save(); }
        return back();
    })->name('notifications.markRead');
    Route::post('/notifications/read-all', function(){
        auth()->user()->unreadNotifications->each(function($n){ $n->read_at = now(); $n->save(); });
        return back();
    })->name('notifications.markAllRead');
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

    // Testimonials CRUD
    Route::resource('testimonials', \App\Http\Controllers\Admin\TestimonialController::class)->except(['show']);
});

// Public webhook route (no authentication required)
Route::post('/payments/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');
// Authenticated (non-admin) order status proxy (kept outside admin group)
Route::get('/payments/order-status', [PaymentController::class, 'orderStatusProxy'])->middleware('auth')->name('payments.order-status');
// Complete payment & create order/invoice (called after external status COMPLETE)
Route::post('/payments/complete-from-client', [PaymentController::class, 'completeFromClient'])->middleware('auth')->name('payments.complete-from-client');
// Public thank you page (no authentication required)
Route::get('/thank-you', [PaymentController::class, 'thankYou'])->name('payment.thank-you');

