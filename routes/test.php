<?php

use Illuminate\Support\Facades\Route;

// Test route to verify payment routes are working
Route::get('/test-routes', function () {
    $routes = [
        'payment.initiate' => route('payment.initiate', 1),
        'payment.process' => route('payment.process', 1),
        'payment.status' => route('payment.status', 1),
        'payment.webhook' => route('payment.webhook'),
    ];
    
    return response()->json([
        'status' => 'success',
        'message' => 'Payment routes are working',
        'routes' => $routes
    ]);
});
