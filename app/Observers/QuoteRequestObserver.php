<?php

namespace App\Observers;

use App\Models\QuoteRequest;
use App\Models\Order;

class QuoteRequestObserver
{
    /**
     * Handle the QuoteRequest "updated" event.
     */
    public function updated(QuoteRequest $quoteRequest)
    {
        // If quote request status changed to 'accepted' and no order exists, create one
        if ($quoteRequest->isDirty('status') && 
            $quoteRequest->status === 'accepted' && 
            !$quoteRequest->order) {
            
            $this->createOrderFromQuoteRequest($quoteRequest);
        }
    }

    /**
     * Create an order from an accepted quote request
     */
    private function createOrderFromQuoteRequest(QuoteRequest $quoteRequest)
    {
        Order::create([
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

        \Log::info('Order automatically created for accepted quote request', [
            'quote_request_id' => $quoteRequest->id,
            'customer_id' => $quoteRequest->customer_id
        ]);
    }
}
