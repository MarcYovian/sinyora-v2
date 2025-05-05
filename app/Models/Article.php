<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'reading_time',
        'featured_image',
        'user_id',
        'category_id',
        'is_published',
        'published_at',
        'views',
    ];

    protected $casts = [
        'reading_time' => 'integer',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'views' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'article_tag', 'article_id', 'tag_id');
    }

    #[Scope]
    protected function published(Builder $query)
    {
        $query->where('published_at', '<=', now())->where('is_published', true);
    }

    #[Scope]
    protected function draft(Builder $query)
    {
        $query->where('is_published', false)->where('published_at', null);
    }
}
