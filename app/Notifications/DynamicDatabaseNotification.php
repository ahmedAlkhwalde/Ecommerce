<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DynamicDatabaseNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $title,
        protected string $message,
        protected string $type,
        protected array $extraData = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'title'   => $this->title,
            'message' => $this->message,
            'type'    => $this->type,
        ], $this->extraData);
    }
}