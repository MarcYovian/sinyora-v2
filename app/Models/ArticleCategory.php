<?php

namespace App\Models;

use App\Observers\ArticleCategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(ArticleCategoryObserver::class)]
class ArticleCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'published_articles_count'];

    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id', 'id');
    }
}
