<?php

namespace App\Observers;

use App\Models\Category;
use App\Events\CategoryUpdated;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    public function created(Category $category): void
    {
        Cache::forget('categories_all');
        event(new CategoryUpdated($category, 'created'));
    }

    public function updated(Category $category): void
    {
        Cache::forget('categories_all');
        event(new CategoryUpdated($category, 'updated'));
    }

    public function deleted(Category $category): void
    {
        Cache::forget('categories_all');
        event(new CategoryUpdated($category, 'deleted'));
    }
}