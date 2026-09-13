<?php

namespace App\Listeners;

use App\Events\ResendOtp;
use App\Mail\maileVerified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class listnerotpresend
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
    public function handle(ResendOtp $event): void
    {
        $user=$event->user;
        Cache::forget('verification_code_' . $user->email);
        $newCode = rand(100000, 999999);
        Cache::put('verification_code_' . $user->email, $newCode, now()->addMinutes(10));
        Mail::to($user->email)->send(new maileVerified($user->name, $newCode));
    }
}
