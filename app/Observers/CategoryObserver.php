<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CategoryObserver
{
    /**
     * مسح الكاش عند إضافة قسم جديد أو تعديله أو حسفه
     */
    private function clearCache(): void
    {
        Cache::forget('categories_all');
        Log::info('تم مسح الكاش الخاص بالتصنيفات.');
    }

    public function created(Category $category): void
    {
        $this->clearCache();
    }

    public function updated(Category $category): void
    {
        $this->clearCache();
    }

    public function deleted(Category $category): void
    {
        $this->clearCache();
    }

    public function restored(Category $category): void
    {
        $this->clearCache();
    }
}