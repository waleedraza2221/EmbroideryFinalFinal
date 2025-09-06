<?php

namespace App\Notifications;

class AdminQuoteRequested extends BaseDatabaseNotification
{
    public function __construct(private int $quoteId, private string $title, private int $customerId)
    {}

    public function toDatabase(object $notifiable): array
    {
        return $this->baseData([
            'type' => 'admin_quote_requested',
            'quote_id' => $this->quoteId,
            'title' => $this->title,
            'customer_id' => $this->customerId,
            'message' => "New quote request: '{$this->title}'",
            'action_url' => route('admin.quote-requests.show', $this->quoteId)
        ]);
    }
}
