<?php

namespace App\Livewire\Forms;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ArticleForm extends Form
{
    public ?Article $article = null;

    public ?int $id = null;
    public string $title = '';
    public string $slug = '';
    public string $content = '';
    public $reading_time = null;
    public string $excerpt = '';
    public string $featured_image = '';
    public string $user_id = '';
    public string $category_id = '';
    public array $tags = [];
    public bool $is_published = false;
    public string $published_at = '';
    public int $views = 0;
    public $image = null;

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('articles', 'slug')->ignore($this->article?->id)],
            'content' => ['required', 'string', 'min:10'],
            'excerpt' => ['required', 'string', 'min:10'],
            'image' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,gif'],
            'category_id' => ['required', 'exists:article_categories,id'],
            'tags' => ['required', 'array', 'min:1'],
            'tags.*' => ['required', 'exists:tags,id'],
            'is_published' => ['boolean'],
        ];
    }

    public function setArticle(Article $article)
    {
        $this->article = $article;

        if ($article) {
            $this->id = $article->id;
            $this->title = $article->title;
            $this->slug = $article->slug;
            $this->content = $article->content;
            $this->excerpt = $article->excerpt;
            $this->featured_image = $article->featured_image;
            $this->user_id = $article->user_id;
            $this->category_id = $article->category_id;
            $this->tags = $article->tags->pluck('id')->toArray();
            $this->is_published = $article->is_published;
            if ($article->is_published) {
                $this->published_at = $article->published_at;
            }
            $this->views = $article->views;
        }
    }

    public function store()
    {
        $this->validate();

        $this->reading_time = $this->calculateReadingTime();

        $data = [
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'reading_time' => $this->reading_time['minutes'],
            'excerpt' => $this->excerpt,
            'user_id' => Auth::id(),
            'category_id' => $this->category_id,
            'views' => $this->views,
        ];

        if ($this->is_published) {
            $data['is_published'] = true;
            $data['published_at'] = now();
        }

        if ($this->image) {
            $data['featured_image'] = $this->storeImage();
        }

        $article = Article::create($data);

        $article->tags()->sync($this->tags);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'user_id' => Auth::id(),
            'category_id' => $this->category_id,
            'views' => $this->views,
        ];

        if ($this->is_published) {
            $data['is_published'] = true;
            if (!$this->published_at) {
                $data['published_at'] = now();
            }
        }

        if ($this->image) {
            $data['featured_image'] = $this->storeImage();
        }

        $this->article->update($data);

        $this->article->tags()->sync($this->tags);

        $this->reset();
    }

    public function delete()
    {
        if ($this->article) {
            $this->article->delete();
            $this->reset();
        }
    }

    public function unpublish()
    {
        if ($this->article) {
            $this->article->update(['is_published' => false, 'published_at' => null]);
            $this->reset();
        }
    }

    public function forceDelete()
    {
        if ($this->article) {
            $this->article->tags()->detach();
            $this->deleteImageContent();

            $this->removeImage();

            $this->article->forceDelete();
            $this->reset();
        }
    }

    protected function storeImage(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // Delete old image if exists
        if ($this->featured_image) {
            if (Storage::disk('public')->exists($this->featured_image)) {
                Storage::disk('public')->delete($this->featured_image);
            }
        }

        // Store new image
        return $this->image->store('articles/thumbnails', 'public');
    }

    public function removeImage()
    {
        if ($this->featured_image) {
            if (Storage::disk('public')->exists($this->featured_image)) {
                Storage::disk('public')->delete($this->featured_image);
            }
            $this->featured_image = '';
        }
        $this->image = null;
    }

    public function deleteImageContent()
    {
        preg_match_all('/<img[^>]+src="([^">]+)"/', $this->content, $matches);
        $imageUrls = $matches[1] ?? [];

        foreach ($imageUrls as $imageUrl) {
            $relativePath = str_replace(
                ['http://127.0.0.1:8000/storage/', url('/storage/')],
                '',
                $imageUrl
            );

            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }
    }

    protected function calculateReadingTime()
    {
        $html = $this->content;

        // Hitung kata
        $text = strip_tags($html);
        $wordCount = count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY));

        // Count attachments and complex elements
        $imageCount = substr_count($html, '<figure data-trix-attachment');
        $attachmentCount = substr_count($html, '<figure data-trix-attachment');
        $headingsCount = substr_count($html, '<h1') + substr_count($html, '<h2') + substr_count($html, '<h3');

        // Base reading speed (words per minute)
        $baseWPM = 200;

        // Adjust for content complexity
        $complexityFactor = 1 +
            ($imageCount * 0.1) +
            ($attachmentCount * 0.15) +
            ($headingsCount * 0.05);

        $adjustedWPM = $baseWPM / $complexityFactor;
        $minutes = max(1, ceil($wordCount / $adjustedWPM));

        return [
            'minutes' => $minutes,
            'seconds' => $wordCount % $adjustedWPM,
            'words' => $wordCount,
            'images' => $imageCount,
            'attachments' => $attachmentCount,
            'headings' => $headingsCount,
            'wpm' => round($adjustedWPM),
            'display' => $minutes > 1 ? "{$minutes} menit" : "1 menit"
        ];
    }
}
