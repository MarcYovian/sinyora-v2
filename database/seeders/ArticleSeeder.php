<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the event tag
        $eventTag = \App\Models\Tag::where('name', 'event')->first();

        // Create 4 Event Articles
        \App\Models\Article::factory(4)->create([
            'title' => 'Kegiatan ' . fake()->sentence(3),
            'is_published' => true,
        ])->each(function ($article) use ($eventTag) {
            $article->tags()->attach($eventTag->id);
        });

        // Create 10 Regular Articles (without event tag)
        \App\Models\Article::factory(10)->create();
    }
}
