<?php

namespace App\Listeners;

use App\Events\UserRegister;
use App\Mail\maileVerified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class sendEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegister $event): void
    {
        $user = $event->user;
        $code = rand(100000, 999999);
        Cache::put('verification_code_' . $user->email, $code, now()->addMinutes(10));
        Mail::to($user->email)->send(new maileVerified($user->name, $code));
        Log::info('Verification email sent to ' . $user->email);
    }
}
