<?php

namespace App\Notifications;

class CustomerOrderDelivered extends BaseDatabaseNotification
{
    public function __construct(private int $orderId, private string $title)
    {}

    public function toDatabase(object $notifiable): array
    {
        return $this->baseData([
            'type' => 'order_delivered',
            'order_id' => $this->orderId,
            'title' => $this->title,
            'message' => "Order delivered: '{$this->title}'",
            'action_url' => route('orders.show', $this->orderId)
        ]);
    }
}
