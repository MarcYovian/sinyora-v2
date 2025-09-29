<?php

namespace App\Repositories\Contracts;

use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;

interface ArticleRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Article;

    public function create(array $data): Article;

    public function update(Article $article, array $data): bool;

    public function delete(Article $article): bool;

    public function forceDelete(Article $article): bool;

    public function syncTags(Article $article, array $tagIds): void;
}
