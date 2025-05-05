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

        $user_ids = \App\Models\User::pluck('id')->toArray();
        $category_ids = \App\Models\ArticleCategory::pluck('id')->toArray();
        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $this->faker->paragraph(),
            'content' => $this->faker->paragraph(5),
            'reading_time' => $this->faker->numberBetween(1, 10),
            'featured_image' => 'https://placehold.co/600x400/orange/white?text=' . $title,
            'user_id' => $this->faker->randomElement($user_ids),
            'category_id' => $this->faker->randomElement($category_ids),
            'is_published' => 1,
            'published_at' => now(),
            'views' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($article) {
            // Attach random tags
            $tags = \App\Models\Tag::pluck('id')->toArray();
            $article->tags()->attach($tags);
        });
    }
}
