<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

abstract class BaseDatabaseNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    protected function baseData(array $extra = []): array
    {
        return array_merge([
            'id' => (string) Str::uuid(),
            'created_at' => now()->toIso8601String(),
            'category' => static::class,
        ], $extra);
    }
}
