<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $products = $this->productService->getAllProducts(10);

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

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->createProduct($request->validated(), $request);

            return response()->json([
                'message' => __('messages.product_created_successfully') ?? 'تم إنشاء المنتج بنجاح.',
                'data'    => $product,
            ], 201);
        } catch (Exception $e) {
            Log::error('فشلت عملية إنشاء المنتج', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء المنتج.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $products = $this->productService->getProductsByCategory($id, 10);

            if ($products === null) {
                return response()->json([
                    'message' => 'التصنيف غير موجود.',
                ], 404);
            }

            return response()->json([
                'message' => $products->isEmpty() ? 'لا توجد منتجات لهذا التصنيف.' : 'تم جلب المنتجات بنجاح.',
                'data'    => $products,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب المنتجات.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            $updatedProduct = $this->productService->updateProduct($product, $request->validated(), $request);

            return response()->json([
                'message' => 'تم تحديث المنتج بنجاح.',
                'data'    => $updatedProduct,
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

    public function destroy(string $id): JsonResponse
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'message' => 'المنتج غير موجود.',
                ], 404);
            }

            $this->productService->deleteProduct($product);

            return response()->json([
                'message' => 'تم حذف المنتج بنجاح.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء الحذف.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function export(): JsonResponse
    {
        $fileName = $this->productService->exportProducts();

        return response()->json([
            'message'   => 'تم جلب الطلب وبدء إنشاء ملف الإكسل في الخلفية عبر الـ Queue.',
            'file_name' => $fileName,
        ], 202);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $this->productService->importProducts($request);

        return response()->json([
            'message' => 'بدأت عملية الاستيراد في الـ Queue بنجاح.',
        ], 202);
    }
}