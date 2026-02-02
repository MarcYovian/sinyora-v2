<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArticleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Berita',
            'Pengumuman',
            'Kegiatan',
            'Artikel Rohani',
            'Edukasi',
        ];

        foreach ($categories as $category) {
            ArticleCategory::factory()->create([
                'name' => $category,
                'slug' => \Illuminate\Support\Str::slug($category),
            ]);
        }

        // Add some random ones
        ArticleCategory::factory(5)->create();
    }
}
