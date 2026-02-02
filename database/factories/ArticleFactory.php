<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence();
        $slug = \Illuminate\Support\Str::slug($title);

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $this->faker->paragraph(),
            'content' => $this->faker->paragraphs(3, true),
            'reading_time' => $this->faker->numberBetween(1, 15),
            'featured_image' => 'https://placehold.co/1200x630/2563eb/ffffff?text=' . urlencode($title),
            'user_id' => \App\Models\User::query()->inRandomOrder()->first()?->id ?? \App\Models\User::factory(),
            'category_id' => \App\Models\ArticleCategory::query()->inRandomOrder()->first()?->id ?? \App\Models\ArticleCategory::factory(),
            'is_published' => $this->faker->boolean(80),
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'views' => $this->faker->numberBetween(0, 1000),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($article) {
            // Attach random tags (1 to 3 tags)
            $tags = \App\Models\Tag::query()->inRandomOrder()->limit(rand(1, 3))->pluck('id');
            if ($tags->isNotEmpty()) {
                $article->tags()->attach($tags);
            }
        });
    }
}
