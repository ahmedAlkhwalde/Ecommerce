<?php

namespace App\Traits;

use App\Notifications\DynamicDatabaseNotification;
use Illuminate\Support\Facades\Notification;

trait HasDynamicNotification
{
    public function sendNotification($notifiables, string $title, string $message, string $type, array $extraData = []): void
    {
        Notification::send(
            $notifiables,
            new DynamicDatabaseNotification($title, $message, $type, $extraData)
        );
    }
}
