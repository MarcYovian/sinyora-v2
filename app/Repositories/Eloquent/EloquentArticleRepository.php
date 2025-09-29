<?php

namespace App\Repositories\Eloquent;

use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentArticleRepository implements ArticleRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Article::with('user', 'category')->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Article
    {
        return Article::find($id);
    }

    public function create(array $data): Article
    {
        return Article::create($data);
    }

    public function update(Article $article, array $data): bool
    {
        return $article->update($data);
    }

    public function delete(Article $article): bool
    {
        return $article->delete();
    }

    public function forceDelete(Article $article): bool
    {
        return $article->forceDelete();
    }

    public function syncTags(Article $article, array $tagIds): void
    {
        $article->tags()->sync($tagIds);
    }
}
