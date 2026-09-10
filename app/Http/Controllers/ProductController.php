<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $products = Product::with('category')->get();
            $products->transform(function ($product) {
                $product->image = $product->image ? asset($product->image) : null;
                return $product;
            });
            return response()->json([
                'message' => 'تم جلب جميع المنتجات بنجاح.',
                'data'    => $products,
            ], 200);
        } catch (Exception $e) {
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
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName);
                $valedateData['image'] = 'images/' . $imageName;
            }
            $product = Product::create($valedateData);
            if (!$product) {
                return response()->json([
                    'message' => 'حدث خطأ أثناء إنشاء المنتج.',
                ], 500);
            }
            return response()->json([
                'message' => 'تم إنشاء المنتج بنجاح.',
                'data'    => $product,
            ], 201);
        } catch (Exception $e) {
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

            $products = $category->products->load('category')->map(function ($product) {
                $product->image = $product->image ? asset($product->image) : null;
                return $product;
            });

            if ($products->isEmpty()) {
                return response()->json([
                    'message' => 'لا توجد منتجات لهذا التصنيف.',
                    'data'    => [],
                ], 200);
            }

            return response()->json([
                'message' => 'تم جلب المنتجات بنجاح.',
                'data'    => $products,
            ], 200);
        } catch (Exception $e) {
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
                if ($product->image && File::exists(public_path($product->image))) {
                    File::delete(public_path($product->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName);
                $validatedData['image'] = 'images/' . $imageName;
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
            $product = Product::findorfail($id);
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
}
