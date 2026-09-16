<?php

namespace App\Traits;

use App\Models\User;
use App\Services\FirebaseService;
use Exception;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Log;

trait SendsFcmNotifications
{
    /**
     * إرسال إشعار FCM لمستخدم محدد أو لآول مستخدم لديه توكن
     *
     * @param string $title
     * @param string $body
     * @param array $data
     * @param User|null $user
     * @return bool
     */
    public function sendNotificationToUser(
        string $title,
        string $body,
        array $data = [],
        ?User $user = null
    ): bool {
        try {
            // جلب المستخدم المحدد أو المسجل حالياً أو أول مستخدم يمتلك FCM Token
            $targetUser = $user ?? User::whereNotNull('fcm_token')->first();

            if (!$targetUser || !$targetUser->fcm_token) {
                return false;
            }

            /** @var FirebaseService $firebaseService */
            $firebaseService = app(FirebaseService::class);

            $firebaseService->sendPushNotification(
                $targetUser->fcm_token,
                $title,
                $body,
                $data
            );

            return true;
        } catch (Exception $e) {
            Log::error('فشل إرسال إشعار FCM عبر الـ Trait', [
                'error' => $e->getMessage(),
                'title' => $title,
            ]);

            return false;
        }
    }
}