<?php

namespace App\DataTransferObjects;

use App\Enums\ArticleStatus;
use App\Livewire\Forms\ArticleForm;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;

class ArticleData extends Data
{
    public function __construct(
        public readonly string $title,
        public readonly string $slug,
        public readonly string $content,
        public readonly string $excerpt,
        public readonly int $category_id,
        public readonly array $tags,
        public readonly bool $is_published,
        public readonly ?UploadedFile $image,
        public readonly ?int $user_id,
    ) {}

    public static function fromLivewire(ArticleForm $form): self
    {
        return new self(
            title: $form->title,
            slug: $form->slug,
            content: $form->content,
            excerpt: $form->excerpt,
            category_id: (int) $form->category_id,
            tags: $form->tags,
            is_published: $form->is_published,
            image: $form->image,
            user_id: Auth::id()
        );
    }
}
