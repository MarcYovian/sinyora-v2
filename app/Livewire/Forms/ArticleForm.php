<?php

namespace App\Livewire\Forms;

use App\Models\Article;
use App\Rules\HorizontalImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ArticleForm extends Form
{
    public ?Article $article = null;

    public ?int $id = null;
    public string $title = '';
    public string $slug = '';
    public string $content = '';
    public string $excerpt = '';
    public string $category_id = '';
    public array $tags = [];
    public bool $is_published = false;
    public $image = null; // Untuk menampung UploadedFile
    public string $featured_image = ''; // Untuk menampilkan gambar yang sudah ada

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('articles', 'slug')->ignore($this->article?->id)],
            'content' => ['required', 'string', 'min:10'],
            'excerpt' => ['required', 'string', 'min:10'],
            'image' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,gif,webp', new HorizontalImage],
            'category_id' => ['required', 'exists:article_categories,id'],
            'tags' => ['required', 'array', 'min:1'],
            'is_published' => ['boolean'],
        ];
    }

    public function setArticle(Article $article)
    {
        $this->article = $article;

        $this->id = $article->id;
        $this->title = $article->title;
        $this->slug = $article->slug;
        $this->content = $article->content;
        $this->excerpt = $article->excerpt;
        $this->featured_image = $article->featured_image;
        $this->category_id = $article->category_id;
        $this->tags = $article->tags->pluck('id', 'name')->toArray();
        $this->is_published = $article->is_published;
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
}
