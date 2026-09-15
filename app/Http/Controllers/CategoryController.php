<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $Category = Cache::remember('categories_all', now()->addDay(), function () {
                return Category::all();
            });
            if ($Category->isEmpty()) {
                return response()->json([
                    'message' => 'لا توجد تصنيفات متاحة حالياً.',
                ], 404);
            }
            return response()->json([
                'message' => 'تم جلب جميع التصنيفات بنجاح.',
                'data'    => $Category,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب البيانات.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $Category = Category::create([
                'name' => $request->name,
            ]);
            if (!$Category) {
                return response()->json([
                    'message' => 'حدث خطأ أثناء إنشاء التصنيف.',
                ], 500);
            }
            return response()->json([
                'message' => 'تم إنشاء التصنيف بنجاح.',
                'data'    => $Category,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء التصنيف.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $Category = Category::find($id);
            if (!$Category) {
                return response()->json([
                    'message' => 'التصنيف غير موجود.',
                ], 404);
            }
            $Category->update([
                'name' => $request->name,
            ]);
            return response()->json([
                'message' => 'تم تحديث التصنيف بنجاح.',
                'data'    => $Category,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء تحديث التصنيف.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $Category = Category::find($id);
            if (!$Category) {
                return response()->json([
                    'message' => 'التصنيف غير موجود.',
                ], 404);
            }
            if ($Category->products()->exists()) {
                return response()->json([
                    'message' => 'لا يمكن حذف التصنيف لأنه مرتبط بمنتجات حالية. قم بنقل أو حذف المنتجات أولاً.',
                ], 400);
            }

            $Category->delete();
            return response()->json([
                'message' => 'تم حذف التصنيف بنجاح.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف التصنيف.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
