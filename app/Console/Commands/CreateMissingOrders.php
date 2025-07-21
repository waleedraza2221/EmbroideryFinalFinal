<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuoteRequest;
use App\Models\Order;

class CreateMissingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:create-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create orders for accepted quote requests that don\'t have orders yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $acceptedQuotesWithoutOrders = QuoteRequest::where('status', 'accepted')
            ->whereDoesntHave('order')
            ->get();

        if ($acceptedQuotesWithoutOrders->isEmpty()) {
            $this->info('No accepted quotes without orders found.');
            return;
        }

        $this->info("Found {$acceptedQuotesWithoutOrders->count()} accepted quotes without orders.");

        foreach ($acceptedQuotesWithoutOrders as $quoteRequest) {
            $order = Order::create([
                'order_number' => 'ORD-' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT),
                'quote_request_id' => $quoteRequest->id,
                'customer_id' => $quoteRequest->customer_id,
                'title' => $quoteRequest->title ?: 'Custom Order',
                'instructions' => $quoteRequest->instructions,
                'original_files' => $quoteRequest->files,
                'amount' => $quoteRequest->quoted_amount,
                'delivery_days' => $quoteRequest->delivery_days ?: 7,
                'due_date' => now()->addDays($quoteRequest->delivery_days ?: 7),
                'status' => 'active'
            ]);

            $this->info("Created order {$order->order_number} for quote request {$quoteRequest->request_number}");
        }

        $this->info('Finished creating missing orders.');
    }
}
