<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    public function __construct(protected Messaging $messaging) {}

    /**
     * إرسال إشعار لحظي عبر FCM Token
     */
    public function sendPushNotification(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);

            Log::info("✅ [FCM Success]: Sent to {$fcmToken}");
            return true;
        } catch (\Throwable $e) {
            Log::error("❌ [FCM Error]: " . $e->getMessage());
            return false;
        }
    }
}