<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = Auth::user();

        if ($user->verified == 0) {
            return response()->json([
                'message' => 'يرجى التحقق من بريدك الإلكتروني لتفعيل الحساب قبل المتابعة.',
            ], 403);
        }
        if ($user->verified == 0) {
            return response()->json([
                'message' => 'يرجى التحقق من بريدك الإلكتروني لتفعيل الحساب قبل المتابعة.',
            ], 403);
        }
        if ($user->is_banned) {
            return response()->json([
                'message' => 'تم حظر حسابك. يرجى التواصل مع الإدارة.',
            ], 403);
        }
        return $next($request);
    }
}
