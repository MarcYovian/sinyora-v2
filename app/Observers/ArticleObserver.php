<?php

namespace App\Observers;

use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ArticleObserver
{
    /**
     * Cache keys prefix
     */
    private const CACHE_PREFIX = 'articles';

    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        $this->clearListCache();
        $this->updateCategoryCount($article->category_id);
        Log::debug('Article cache cleared: new article created', ['id' => $article->id]);
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        // Clear individual article cache
        Cache::forget($this->getArticleCacheKey($article->slug));

        // Clear list caches
        $this->clearListCache();

        // Update category counts if category changed or publish status changed
        if ($article->wasChanged(['category_id', 'is_published', 'published_at'])) {
            // Update old category count if category changed
            if ($article->wasChanged('category_id')) {
                $this->updateCategoryCount($article->getOriginal('category_id'));
            }
            $this->updateCategoryCount($article->category_id);
        }

        Log::debug('Article cache cleared: article updated', ['id' => $article->id, 'slug' => $article->slug]);
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        Cache::forget($this->getArticleCacheKey($article->slug));
        $this->clearListCache();
        $this->updateCategoryCount($article->category_id);

        Log::debug('Article cache cleared: article deleted', ['id' => $article->id]);
    }

    /**
     * Handle the Article "restored" event.
     */
    public function restored(Article $article): void
    {
        $this->clearListCache();
        $this->updateCategoryCount($article->category_id);
        Log::debug('Article cache cleared: article restored', ['id' => $article->id]);
    }

    /**
     * Handle the Article "force deleted" event.
     */
    public function forceDeleted(Article $article): void
    {
        Cache::forget($this->getArticleCacheKey($article->slug));
        $this->clearListCache();
        $this->updateCategoryCount($article->category_id);

        Log::debug('Article cache cleared: article force deleted', ['id' => $article->id]);
    }

    /**
     * Get cache key for individual article
     */
    private function getArticleCacheKey(string $slug): string
    {
        return self::CACHE_PREFIX . '.show.' . $slug;
    }

    /**
     * Clear all list-related caches
     */
    private function clearListCache(): void
    {
        // Clear recent articles cache
        Cache::forget(self::CACHE_PREFIX . '.recent');

        // Clear popular categories cache
        Cache::forget(self::CACHE_PREFIX . '.popular_categories');

        // Clear all categories cache
        Cache::forget(self::CACHE_PREFIX . '.categories.all');

        // Note: Related articles cache uses pattern 'articles.related.{category_id}.exclude.{article_id}'
        // File cache doesn't support pattern deletion, so these will expire naturally (1 hour TTL)
    }

    /**
     * Update the denormalized published_articles_count on ArticleCategory.
     * This avoids expensive correlated subquery for popular categories.
     */
    private function updateCategoryCount(?int $categoryId): void
    {
        if (!$categoryId) {
            return;
        }

        $category = \App\Models\ArticleCategory::find($categoryId);
        if ($category) {
            $count = Article::where('category_id', $categoryId)
                ->published()
                ->count();
            $category->published_articles_count = $count;
            $category->saveQuietly(); // Avoid triggering observers
        }
    }
}
