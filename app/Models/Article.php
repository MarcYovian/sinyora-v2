<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Observers\ArticleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

#[ObservedBy([ArticleObserver::class])]
class Article extends Model implements Sitemapable
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
        'updated_by',
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

    /**
     * Author/creator of the article
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Last user who updated the article
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
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

    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('articles.show', $this))
            ->setLastModificationDate($this->updated_at)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            ->setPriority(0.8);
    }
}
