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
        // Order creation is now handled by the payment flow only
        // This observer no longer creates orders to prevent duplicates
        \Log::info('QuoteRequest updated', [
            'id' => $quoteRequest->id,
            'status' => $quoteRequest->status
        ]);
    }
}
