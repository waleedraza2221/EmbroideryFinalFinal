<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\Invoice;

try {
    echo "Testing invoice creation for Order 12...\n";
    
    $order = Order::find(12);
    if (!$order) {
        echo "Order 12 not found!\n";
        exit(1);
    }
    
    echo "Order found: {$order->title}\n";
    echo "Customer ID: {$order->customer_id}\n";
    
    $customer = $order->customer;
    if (!$customer) {
        echo "Customer not found for order!\n";
        exit(1);
    }
    
    echo "Customer found: {$customer->name}\n";
    echo "Customer email: {$customer->email}\n";
    
    // Try to create invoice using the same logic as PaymentController
    $lineItems = [[ 'description'=>$order->title, 'quantity'=>1, 'price'=>$order->amount ]];
    $subtotal = $order->amount;
    $taxRate = 0.10; 
    $taxAmount = $subtotal * $taxRate; 
    $totalAmount = $subtotal + $taxAmount;
    
    echo "Attempting to create invoice...\n";
    echo "Subtotal: $subtotal\n";
    echo "Tax: $taxAmount\n";
    echo "Total: $totalAmount\n";
    
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
        'notes'=>'Test invoice for order: '.$order->order_number
    ]);
    
    echo "Invoice created successfully! ID: {$invoice->id}\n";
    echo "Invoice number: {$invoice->invoice_number}\n";
    
} catch (Exception $e) {
    echo "Error creating invoice: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
