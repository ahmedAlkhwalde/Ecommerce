<?php

namespace App\Http\Controllers;

use App\Events\ResendOtp;
use App\Events\UserRegister;
use App\Http\Requests\loginRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Jobs\ResendEmailQueue;
use App\Jobs\SendWelcomeEmailJob;
use App\Mail\maileVerified;
use App\Mail\mailPasswordReset;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{

    public function handleGoogleCallback(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $googleUser = Socialite::driver('google')->userFromToken($request->token);

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name'          => $googleUser->getName(),
                    'provider_id'   => $googleUser->getId(),
                    'provider_name' => 'google',
                    'role_id'       => 2,
                    'verified'      => 1,
                    'password'      => null,
                ]
            );



            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message'      => 'تم تسجيل الدخول بنجاح عبر غوغل.',
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'فشل التحقق من حساب غوغل.',
                'error'   => $e->getMessage(),
            ], 401);
        }
    }

    public function getusers()
    {
        try {
            $users = User::with('profile')->get();
            if (!$users) {
                return response()->json([
                    'message' => 'حدث خطأ أثناء جلب المستخدمين.',
                ], 500);
            }
            return response()->json([
                'message' => 'تم جلب جميع المستخدمين بنجاح.',
                'data'    => $users,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب المستخدمين.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function blockUser($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'المستخدم غير موجود.',
                ], 404);
            }
            $user->is_banned = true;
            $user->save();
            return response()->json([
                'message' => 'تم حظر المستخدم بنجاح.',
                'data'    => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء حظر المستخدم.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function unblockUser($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'المستخدم غير موجود.',
                ], 404);
            }
            $user->is_banned = false;
            $user->save();
            return response()->json([
                'message' => 'تم إلغاء حظر المستخدم بنجاح.',
                'data'    => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إلغاء حظر المستخدم.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function register(UserRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['role_id'] = 2;
        $user = User::create($validatedData);
        if (!$user) {
            return response()->json(['error' => 'User not created'], 422);
        }
        SendWelcomeEmailJob::dispatch($user);
        return response()->json([
            'message' => 'تم إنشاء الحساب بنجاح، تم إرسال رمز التحقق إلى بريدك الإلكتروني.',
            'user'    => $user,
        ], 201);
    }




    public function verifyOtp(VerifyOtpRequest $request)
    {
        $email = $request->email;
        $code  = $request->code;

        $cachedCode = Cache::get('verification_code_' . $email);

        if (!$cachedCode || $cachedCode != $code) {
            return response()->json([
                'message' => 'رمز التحقق غير صحيح أو انتهت صلاحيته (تكون الصلاحية 10 دقائق).'
            ], 400);
        }

        $user = User::where('email', $email)->first();

        $user->update([
            'verified'          => 1,
            'email_verified_at' => now(),
        ]);

        Cache::forget('verification_code_' . $email);

        return response()->json([
            'message' => 'تم تفعيل حسابك بنجاح! يمكنك الآن تسجيل الدخول.'
        ], 200);
    }




    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        $user = User::where('email', $request->email)->first();

        if ($user->verified == 1) {
            return response()->json([
                'message' => 'هذا الحساب مفعل مسبقاً، يمكنك تسجيل الدخول مباشرة.'
            ], 400);
        }
        ResendEmailQueue::dispatch($user);
        return response()->json([
            'message' => 'تم إعادة إرسال رمز تحقق جديد إلى بريدك الإلكتروني.'
        ], 200);
    }




    public function login(loginRequest $request)
    {
        $validatedData = $request->validated();
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user || !password_verify($validatedData['password'], $user->password)) {
            return response()->json(['error' => 'بيانات الدخول غير صحيحة'], 401);
        }

        if ($user->verified == 0) {
            return response()->json(['error' => 'حسابك غير مفعل، يرجى التحقق من بريدك الإلكتروني.'], 403);
        }

        if ($user->is_banned) {
            return response()->json(['error' => 'تم حظر حسابك. يرجى التواصل مع الإدارة.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'User' => $user,
            'access_token' => $token,
        ]);
    }

    public function forgetpassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $code = rand(100000, 999999);
        Cache::put('password_reset_code_' . $user->email, $code, now()->addMinutes(10));
        Mail::to($user->email)->send(new mailPasswordReset($user->name, $code));

        return response()->json([
            'message' => 'تم إرسال رمز إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.'
        ], 200);
    }


    public function resetpassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|digits:6',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $cachedCode = Cache::get('password_reset_code_' . $request->email);

        if (!$cachedCode || $cachedCode != $request->code) {
            return response()->json([
                'message' => 'رمز إعادة تعيين كلمة المرور غير صحيح أو انتهت صلاحيته (تكون الصلاحية 10 دقائق).'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = bcrypt($request->new_password);
        $user->save();

        Cache::forget('password_reset_code_' . $request->email);

        return response()->json([
            'message' => 'تم إعادة تعيين كلمة المرور بنجاح! يمكنك الآن تسجيل الدخول بكلمة المرور الجديدة.'
        ], 200);
    }



    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $request->user()->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.fcm_token_updated'), // استخدام الترجمة المجهزة سابقاً
        ]);
    }




    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'User logged out successfully'], 200);
    }
}
