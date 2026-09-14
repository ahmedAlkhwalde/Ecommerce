<?php

namespace App\Jobs;

use App\Traits\HasDynamicNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class NotificationAndLogJob implements ShouldQueue
{
    use HasDynamicNotification,Queueable;

    /**
     * Create a new job instance.
     */
    public $notifiables;
    public $title;
    public $message;
    public $type;
    public $extraData;
    public function __construct($notifiables, string $title, string $message, string $type, array $extraData = [])
    {
        $this->notifiables = $notifiables;
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->extraData = $extraData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->sendNotification(
                $this->notifiables,
                $this->title,
                $this->message,
                $this->type,
                $this->extraData
            );
        } catch (Exception $e) {
            Log::error('NotificationAndLogJob Error: ' . $e->getMessage());
        }
        Log::info('NotificationAndLogJob completed.');
    }
}
