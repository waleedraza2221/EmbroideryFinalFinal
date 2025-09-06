<?php

namespace App\Notifications;

class CustomerQuoteProvided extends BaseDatabaseNotification
{
    public function __construct(private int $quoteId, private string $title, private string $amount, private string $deliveryDays)
    {}

    public function toDatabase(object $notifiable): array
    {
        return $this->baseData([
            'type' => 'quote_provided',
            'quote_id' => $this->quoteId,
            'title' => $this->title,
            'amount' => $this->amount,
            'delivery_days' => $this->deliveryDays,
            'message' => "Quote available for '{$this->title}'",
            'action_url' => route('quote-requests.show', $this->quoteId)
        ]);
    }
}
