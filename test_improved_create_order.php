<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing improved createOrder() method...\n\n";

// Test with an existing quote request that already has an order
$quoteRequest = App\Models\QuoteRequest::find(1);
if (!$quoteRequest) {
    echo "Quote request not found.\n";
    exit;
}

echo "Testing Quote Request ID: {$quoteRequest->id}\n";

// Try multiple times to create order (simulating race condition)
for ($i = 1; $i <= 3; $i++) {
    echo "Attempt {$i}: ";
    try {
        $order = $quoteRequest->createOrder();
        echo "SUCCESS - Order returned: {$order->order_number} (ID: {$order->id})\n";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\nFinal orders in database:\n";
$orders = DB::table('orders')->select('id', 'quote_request_id', 'order_number')->get();
foreach ($orders as $order) {
    echo "ID: {$order->id}, Quote Request: {$order->quote_request_id}, Order Number: {$order->order_number}\n";
}
