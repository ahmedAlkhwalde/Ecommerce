<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Traits\UploadImageTrait;
use App\Jobs\SendsFcmNotificationsQueue;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ProductService
{
    use UploadImageTrait;


    public function getAllProducts(int $perPage = 10): LengthAwarePaginator
    {
        $products = Product::with(['category', 'images'])->paginate($perPage);

        $products->getCollection()->transform(function ($product) {
            $product->images->transform(function ($image) {
                $image->url = asset($image->url);
                return $image;
            });
            return $product;
        });

        return $products;
    }


    public function getProductsByCategory(string $categoryId, int $perPage = 10): ?LengthAwarePaginator
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return null;
        }

        $products = $category->products()->with(['category', 'images'])->paginate($perPage);

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

        return $products;
    }


    public function createProduct(array $data, Request $request): Product
    {
        $product = Product::create($data);

        if ($request->hasFile('image')) {
            $this->uploadImage($request, $product, 'images', 'image', 'images/products');
        }

        $user = Auth::user();
        SendsFcmNotificationsQueue::dispatch("منتج جديد", "تم إضافة منتج بنجاح", $product, $user);

        return $product->load('images');
    }

 
    public function updateProduct(Product $product, array $data, Request $request): Product
    {
        if ($request->hasFile('image')) {
            $this->uploadImage($request, $product, 'images', 'image', 'images/products');
        }

        $product->update($data);

        return $product->fresh('images');
    }

    /**
     * حذف المنتج
     */
    public function deleteProduct(Product $product): bool
    {
        return $product->delete();
    }


    public function exportProducts(): string
    {
        if (!Storage::disk('local')->exists('exports')) {
            Storage::disk('local')->makeDirectory('exports');
        }

        $fileName = 'exports/products_' . time() . '.xlsx';
        (new ProductsExport)->store($fileName, 'local');

        return $fileName;
    }

    /**
     * استيراد المنتجات من ملف Excel
     */
    public function importProducts(Request $request): void
    {
        $path = $request->file('file')->store('imports', 'local');
        Excel::import(new ProductsImport, $path, 'local');
    }
}