<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use App\Traits\UploadImageTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    use UploadImageTrait;
    public function index()
{
    try {
        $user = Auth::user();
        
        if ($user->profile) {
            $user->profile->load('image');

            if ($user->profile->image) {
                $user->profile->image->url = asset($user->profile->image->url);
            }
        }

        return response()->json([
            'message' => 'تم جلب بيانات الملف الشخصي بنجاح.',
            'data'    => $user,
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'message' => 'حدث خطأ أثناء جلب بيانات الملف الشخصي.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(UpdateProfileRequest $request)
    {
        try {
            $user_id = Auth::user()->id;
            $valedatedata = $request->validated();
            $valedatedata['user_id'] = $user_id;
            $profile = Profile::updateOrCreate(['user_id' => $user_id], $valedatedata);
            if (!$profile) {
                return response()->json([
                    'message' => 'حدث خطأ أثناء حفظ بيانات الملف الشخصي.',
                ], 500);
            }
            $imagepath=$this->uploadImage($request, $profile, 'image', 'avatar', 'image/profile');
            if(!$imagepath) {
                Log::error('فشل تحميل الصورة', [
                    'user_id' => $user_id,
                    'profile_id' => $profile->id,
                    
                ]);
            }
            return response()->json([
                'message' => 'تم حفظ بيانات الملف الشخصي بنجاح.',
                'data'    => $profile,
                'image_path' => $imagepath,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء تحديث الملف الشخصي.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
