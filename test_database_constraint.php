<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing database unique constraint directly...\n\n";

try {
    // Try to force create a duplicate order using raw database insert
    // This bypasses the model-level protection to test the database constraint
    DB::table('orders')->insert([
        'quote_request_id' => 1,
        'order_number' => 'FORCE-DUPLICATE-TEST',
        'customer_id' => 1,
        'title' => 'Test Duplicate',
        'instructions' => 'Testing database constraint',
        'amount' => 99.99,
        'delivery_days' => 1,
        'due_date' => now()->addDay(),
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "ERROR: Database constraint failed - duplicate was created!\n";
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'orders_quote_request_id_unique')) {
        echo "SUCCESS: Database unique constraint prevented duplicate order!\n";
    } else {
        echo "Unexpected error: " . $e->getMessage() . "\n";
    }
}

echo "\nFinal orders count:\n";
$orders = DB::table('orders')->select('id', 'quote_request_id', 'order_number')->get();
foreach ($orders as $order) {
    echo "ID: {$order->id}, Quote Request: {$order->quote_request_id}, Order Number: {$order->order_number}\n";
}
