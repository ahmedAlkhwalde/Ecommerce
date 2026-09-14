<?php

namespace App\Jobs;

use App\Events\UserRegister;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user)
    {
        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Welcome email sent to ' . $this->user->email);
        UserRegister::dispatch($this->user);
    }
}
