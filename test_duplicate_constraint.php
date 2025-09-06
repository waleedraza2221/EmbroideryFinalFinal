<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing unique constraint on orders.quote_request_id...\n\n";

// First, let's find an existing quote request
$quoteRequest = App\Models\QuoteRequest::first();
if (!$quoteRequest) {
    echo "No quote requests found to test with.\n";
    exit;
}

echo "Using Quote Request ID: {$quoteRequest->id}\n";

// Check if order already exists
$existingOrder = App\Models\Order::where('quote_request_id', $quoteRequest->id)->first();
if ($existingOrder) {
    echo "Order already exists for this quote request: {$existingOrder->order_number}\n";
    
    // Try to create another order using the model method
    try {
        $duplicateOrder = $quoteRequest->createOrder();
        if ($duplicateOrder && $duplicateOrder->id === $existingOrder->id) {
            echo "SUCCESS: createOrder() method returned existing order (no duplicate created)!\n";
        } else {
            echo "ERROR: Duplicate order was created: {$duplicateOrder->order_number}\n";
        }
    } catch (Exception $e) {
        echo "SUCCESS: Database constraint prevented duplicate - " . $e->getMessage() . "\n";
    }
} else {
    echo "No existing order found for this quote request.\n";
}

// Show current orders
echo "\nCurrent orders:\n";
$orders = DB::table('orders')->select('id', 'quote_request_id', 'order_number')->get();
foreach ($orders as $order) {
    echo "ID: {$order->id}, Quote Request: {$order->quote_request_id}, Order Number: {$order->order_number}\n";
}
