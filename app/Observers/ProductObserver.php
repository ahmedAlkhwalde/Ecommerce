<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Database;

class ProductObserver
{
    protected Database $database;

    // حقن خدمة Firebase Database
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * عند إضافة منتج جديد
     */
    public function created(Product $product): void
    {
        $this->syncToFirebase($product);
    }

    /**
     * عند تعديل بيانات المنتج
     */
    public function updated(Product $product): void
    {
        $this->syncToFirebase($product);
    }

    /**
     * عند حذف المنتج
     */
    public function deleted(Product $product): void
    {
        // حذف عقدة المنتج المحددة نهائياً من Firebase
        $this->database->getReference("products/{$product->id}")->remove();
    }

    /**
     * دالة مساعدة لمزامنة بيانات المنتج بالكامل
     */
    private function syncToFirebase(Product $product): void
    {
        Log::info("مزامنة بيانات المنتج مع Firebase: {$product->id}");
        // حفظ بيانات المنتج كاملة تحت المسار products/PRODUCT_ID
        $this->database->getReference("products/{$product->id}")->set([
            'id'          => $product->id,
            'title'        => $product->title,
            'price'       => (float) $product->price,
            'stock'       => (int) $product->stock,
            'category_id' => $product->category_id,
            'description' => $product->description ?? '',
            'created_at'  => $product->created_at?->toIso8601String(),
            'updated_at'  => $product->updated_at?->toIso8601String(),
        ]);
    }
}