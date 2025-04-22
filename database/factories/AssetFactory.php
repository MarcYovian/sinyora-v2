<?php

namespace Database\Factories;

use App\Models\AssetCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_category_id' => AssetCategory::get()->random()->id,
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'code' => $this->faker->unique()->numerify('AS-####'),
            'description' => $this->faker->sentence(),
            'quantity' => $this->faker->randomNumber(2),
            'storage_location' => $this->faker->sentence(),
            'is_active' => true,
            'image' => $this->faker->imageUrl(),
            'created_by' => User::first()->id,
        ];
    }
}
