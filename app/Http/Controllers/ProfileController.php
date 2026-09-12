<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use App\Traits\UploadImageTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    use UploadImageTrait;
    public function index()
    {
        $user = Auth::user();
        $user->profile;
        if ($user->profile && $user->profile->avatar) {
            $user->profile->image_url = asset($user->profile->avatar);
        }
        return response()->json([
            'message' => 'تم جلب بيانات الملف الشخصي بنجاح.',
            'data'    => $user,
        ], 200);
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
            if ($request->hasFile('avatar')) {
                $imagepath = $this->uploadImage($request, 'avatar', 'images');
                if ($imagepath) {
                    $valedatedata['avatar'] = $imagepath;
                }
            }
            $profile = Profile::updateOrCreate(['user_id' => $user_id], $valedatedata);
            if (!$profile) {
                return response()->json([
                    'message' => 'حدث خطأ أثناء حفظ بيانات الملف الشخصي.',
                ], 500);
            }
            return response()->json([
                'message' => 'تم حفظ بيانات الملف الشخصي بنجاح.',
                'data'    => $profile,
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
