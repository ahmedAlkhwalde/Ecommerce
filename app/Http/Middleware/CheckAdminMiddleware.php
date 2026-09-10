<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. التأكد من تسجيل الدخول أولاً
        if (!Auth::check()) {
            return response()->json([
                'message' => 'غير مصرح لك بالوصول، يرجى تسجيل الدخول أولاً.',
            ], 401);
        }

        $user = Auth::user();

        if ($user->verified == 0) {
            return response()->json([
                'message' => 'يرجى التحقق من بريدك الإلكتروني لتفعيل الحساب قبل المتابعة.',
            ], 403);
        }

        if ($user->role_id != 1) {
            return response()->json([
                'message' => 'عذراً، هذه المنطقة مخصصة للمشرفين (Admins) فقط.',
            ], 403);
        }

        return $next($request);
    }
}
