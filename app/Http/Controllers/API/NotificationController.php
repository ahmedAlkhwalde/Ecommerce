<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->notifications();

        if ($request->has('type')) {
            $query->where('data->type', $request->query('type'));
        }

        return response()->json([
            'message' => 'تم جلب الإشعارات بنجاح.',
            'data'    => $query->paginate(10),
        ], 200);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'الإشعار غير موجود.'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'تم تحديد الإشعار كمقروء.'], 200);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'تم تحديد جميع الإشعارات كمقروءة.'], 200);
    }
}