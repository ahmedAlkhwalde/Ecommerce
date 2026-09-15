<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Traits\UploadImageTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exports\ProductsExport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;

class ProductController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // 1. تحميل التصنيف ومعرض الصور مسبقاً
            $products = Product::with(['category', 'images'])->paginate(10);

            $products->getCollection()->transform(function ($product) {
                $product->images->transform(function ($image) {
                    $image->url = asset($image->url);
                    return $image;
                });
                return $product;
            });

            return response()->json([
                'message' => 'تم جلب المنتجات بنجاح.',
                'data'    => $products,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب المنتجات.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        try {
            $valedateData = $request->validated();
            $product = Product::create($valedateData);
            if (!$product) {
                return response()->json([
                    'message' => 'حدث خطأ أثناء إنشاء المنتج.',
                ], 500);
            }
            $imagePath = $this->uploadImage($request, $product, 'images', 'image', 'images/products');
            return response()->json([
                'message' => 'تم إنشاء المنتج بنجاح.',
                'data'    => $product->load('images'),
                'image_path' => $imagePath,
            ], 201);
        } catch (Exception $e) {
            Log::error('فشلت عملية إنشاء المنتج', [
                'error'    => $e->getMessage(),
                'file'     => $e->getFile(),
                'line'     => $e->getLine(),
            ]);
            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء المنتج.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'message' => 'التصنيف غير موجود.',
                ], 404);
            }

            // 1. تحميل التصنيف وعلاقة الصور مسبقاً (سواء كانت images أو image)
            $products = $category->products()->with(['category', 'images'])->paginate(10);

            // 2. تعديل روابط الصور داخل مجموعة البيانات بدون كسر الهيكل
            $products->getCollection()->transform(function ($product) {
                if ($product->relationLoaded('images')) {
                    $product->images->transform(function ($img) {
                        $img->url = asset($img->url);
                        return $img;
                    });
                } elseif ($product->relationLoaded('image') && $product->image) {
                    $product->image->url = asset($product->image->url);
                }
                return $product;
            });

            // 3. إرجاع النتيجة مباشرة (Paginator يرجع دائمًا هيكل منظم حتى لو كان فارغاً)
            return response()->json([
                'message' => $products->isEmpty() ? 'لا توجد منتجات لهذا التصنيف.' : 'تم جلب المنتجات بنجاح.',
                'data'    => $products,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب المنتجات.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */


    public function update(UpdateProductRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            $product = Product::findOrFail($id);

            if ($request->hasFile('image')) {
                $imagepath = $this->uploadImage($request, $product, 'images', 'image', 'images/products');
            }

            $product->update($validatedData);

            return response()->json([
                'message' => 'تم تحديث المنتج بنجاح.',
                'data'    => $product->fresh(),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'المنتج غير موجود.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء تحديث المنتج.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'message' => 'المنتج غير موجود.',
                ], 404);
            }
            $product->delete();
            return response()->json([
                'message' => 'تم حذف المنتج بنجاح.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء الحذف.',
                'error' => $e->getMessage(),
            ]);
        }
    }



    public function export()
    {
        // التأكد من وجود المجلد
        if (!Storage::disk('local')->exists('exports')) {
            Storage::disk('local')->makeDirectory('exports');
        }

        $fileName = 'exports/products_' . time() . '.xlsx';

        // استخدام store وتحديد disk local صراحةً
        (new ProductsExport)->store($fileName, 'local');

        return response()->json([
            'message' => 'تم جلب الطلب وبدء إنشاء ملف الإكسل في الخلفية عبر الـ Queue.',
            'file_name' => $fileName,
        ], 202);
    }



    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        // حفظ الملف في local storage ليصل إليه الـ Worker بأمان
        $path = $request->file('file')->store('imports', 'local');

        Excel::import(new ProductsImport, $path, 'local');

        return response()->json([
            'message' => 'بدأت عملية الاستيراد في الـ Queue بنجاح.',
        ], 202);
    }
}
