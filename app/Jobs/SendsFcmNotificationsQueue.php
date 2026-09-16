<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use App\Traits\SendsFcmNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendsFcmNotificationsQueue implements ShouldQueue
{
    use Queueable,SendsFcmNotifications;

    /**
     * Create a new job instance.
     */
    public $product;
    public $user;
    public $title;
    public $desc;
    public $data;
    public function __construct($title,$desc, $data,User $user)
    {
        $this->product=$data;
        $this->user=$user;
        $this->title=$title;
        $this->desc=$desc;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->sendNotificationToUser(
            $this->title,
            $this->desc,
            [],
            $this->user
        );
    }
}
