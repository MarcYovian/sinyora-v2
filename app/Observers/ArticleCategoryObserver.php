<?php

namespace App\Observers;

use App\Models\ArticleCategory;
use Illuminate\Support\Facades\Log;

class ArticleCategoryObserver
{
    /**
     * Handle the ArticleCategory "created" event.
     */
    public function created(ArticleCategory $category): void
    {
        Log::info('Article category created', [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the ArticleCategory "updated" event.
     */
    public function updated(ArticleCategory $category): void
    {
        Log::info('Article category updated', [
            'id' => $category->id,
            'name' => $category->name,
            'changes' => $category->getChanges(),
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the ArticleCategory "deleted" event.
     */
    public function deleted(ArticleCategory $category): void
    {
        Log::info('Article category deleted', [
            'id' => $category->id,
            'name' => $category->name,
            'deleted_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the ArticleCategory "restored" event.
     */
    public function restored(ArticleCategory $category): void
    {
        Log::info('Article category restored', [
            'id' => $category->id,
            'name' => $category->name,
        ]);
    }

    /**
     * Handle the ArticleCategory "force deleted" event.
     */
    public function forceDeleted(ArticleCategory $category): void
    {
        Log::info('Article category force deleted', [
            'id' => $category->id,
            'name' => $category->name,
        ]);
    }
}
