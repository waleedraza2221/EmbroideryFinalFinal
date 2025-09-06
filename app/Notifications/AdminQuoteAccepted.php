<?php

namespace App\Notifications;

class AdminQuoteAccepted extends BaseDatabaseNotification
{
    public function __construct(private int $quoteId, private string $title, private int $customerId)
    {}

    public function toDatabase(object $notifiable): array
    {
        return $this->baseData([
            'type' => 'admin_quote_accepted',
            'quote_id' => $this->quoteId,
            'title' => $this->title,
            'customer_id' => $this->customerId,
            'message' => "Quote accepted: '{$this->title}'",
            'action_url' => route('admin.quote-requests.show', $this->quoteId)
        ]);
    }
}
