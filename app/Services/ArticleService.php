<?php

namespace App\Services;

use App\DataTransferObjects\ArticleData;
use App\Models\Article;
use App\Models\Tag;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ArticleService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected ArticleRepositoryInterface $articleRepository
    ) {}

    /**
     * Menyimpan (membuat atau memperbarui) artikel.
     *
     * @throws Throwable
     */
    public function saveArticle(ArticleData $data, ?Article $article = null): Article
    {
        return DB::transaction(function () use ($data, $article) {
            $payload = $this->preparePayload($data, $article);
            dd($data->tags);

            // Handle image upload
            if ($data->image) {
                // Hapus gambar lama jika ada saat update
                if ($article?->featured_image) {
                    $this->deleteImage($article->featured_image);
                }
                $payload['featured_image'] = $data->image->store('articles/thumbnails', 'public');
            }

            // Create atau Update artikel
            if ($article) {
                $this->articleRepository->update($article, $payload);
                $article->refresh(); // Ambil data terbaru setelah update
            } else {
                $article = $this->articleRepository->create($payload);
            }

            // Handle Tags (termasuk membuat tag baru)
            $tagIds = $this->processTags($data->tags);
            $this->articleRepository->syncTags($article, $tagIds);

            return $article;
        });
    }

    /**
     * Menghapus artikel (soft delete).
     */
    public function deleteArticle(Article $article): bool
    {
        try {
            return $this->articleRepository->delete($article);
        } catch (Throwable $e) {
            Log::error('Failed to delete article: ' . $article->id, ['exception' => $e]);
            return false;
        }
    }

    /**
     * Menghapus artikel secara permanen beserta file terkait.
     *
     * @throws Throwable
     */
    public function forceDeleteArticle(Article $article): bool
    {
        return DB::transaction(function () use ($article) {
            // Hapus relasi tags
            $this->articleRepository->syncTags($article, []);

            // Hapus gambar dari konten
            $this->deleteContentImages($article->content);

            // Hapus featured image
            if ($article->featured_image) {
                $this->deleteImage($article->featured_image);
            }

            // Hapus artikel dari database
            return $this->articleRepository->forceDelete($article);
        });
    }

    /**
     * Mengubah status publikasi artikel menjadi false.
     */
    public function unpublishArticle(Article $article): bool
    {
        try {
            return $this->articleRepository->update($article, [
                'is_published' => false,
                'published_at' => null
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to unpublish article: ' . $article->id, ['exception' => $e]);
            return false;
        }
    }

    /**
     * Menyiapkan data array untuk disimpan ke database.
     */
    private function preparePayload(ArticleData $data, ?Article $article): array
    {
        $payload = [
            'title' => $data->title,
            'slug' => $data->slug,
            'content' => $data->content,
            'excerpt' => $data->excerpt,
            'user_id' => $data->user_id,
            'category_id' => $data->category_id,
            'reading_time' => $this->calculateReadingTime($data->content),
            'is_published' => $data->is_published,
        ];

        if ($data->is_published) {
            // Hanya set published_at jika belum pernah di-publish sebelumnya
            $payload['published_at'] = $article?->published_at ?? now();
        } else {
            $payload['published_at'] = null;
        }

        return $payload;
    }

    /**
     * Memproses array tags dari form, membuat tag baru jika diperlukan.
     */
    private function processTags(array $tagsData): array
    {
        $tagIds = [];
        foreach ($tagsData as $tagData) {
            // Cek jika tag baru (misal, dari select-create)
            if (is_array($tagData) && str_starts_with($tagData['id'], 'new:')) {
                $newTag = Tag::firstOrCreate(
                    ['name' => $tagData['name']],
                    [
                        'slug' => Str::slug($tagData['name']),
                    ]
                );
                $tagIds[] = $newTag->id;
            } else {
                // Tag yang sudah ada
                $tagIds[] = is_array($tagData) ? $tagData['id'] : $tagData;
            }
        }
        return $tagIds;
    }

    /**
     * Menghapus file gambar dari storage.
     */
    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Mencari dan menghapus semua gambar yang di-embed di dalam konten Trix editor.
     */
    private function deleteContentImages(?string $content): void
    {
        if (!$content) return;

        preg_match_all('/<img[^>]+src="([^">]+)"/', $content, $matches);
        $imageUrls = $matches[1] ?? [];

        foreach ($imageUrls as $imageUrl) {
            $relativePath = str_replace(url('/storage/'), '', $imageUrl);
            $this->deleteImage($relativePath);
        }
    }

    /**
     * Menghitung estimasi waktu baca dari konten HTML.
     */
    private function calculateReadingTime(string $html): int
    {
        $text = strip_tags($html);
        $wordCount = count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY));

        $imageCount = substr_count($html, '<figure');
        $baseWPM = 200; // Words Per Minute
        $complexityFactor = 1 + ($imageCount * 0.1); // +10% waktu per gambar
        $adjustedWPM = $baseWPM / $complexityFactor;

        return max(1, (int) ceil($wordCount / $adjustedWPM));
    }
}
