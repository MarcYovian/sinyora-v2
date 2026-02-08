<?php

namespace Database\Factories;

use App\Enums\BorrowingStatus;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Borrowing>
 */
class BorrowingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::first() ?? User::factory()->create();
        $activity = Activity::create([
            'name' => $this->faker->sentence(3),
            'location' => $this->faker->address(),
        ]);

        return [
            'start_datetime' => $this->faker->dateTimeBetween('+1 day', '+3 days'),
            'end_datetime' => $this->faker->dateTimeBetween('+4 days', '+30 days'),
            'notes' => $this->faker->sentence(),
            'borrower' => $this->faker->name(),
            'borrower_phone' => $this->faker->phoneNumber(),
            'status' => BorrowingStatus::PENDING,
            'creator_id' => $user->id,
            'creator_type' => $user->getMorphClass(),
            'borrowable_id' => $activity->id,
            'borrowable_type' => $activity->getMorphClass(),
        ];
    }

    /**
     * Indicate that the borrowing is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BorrowingStatus::APPROVED,
        ]);
    }

    /**
     * Indicate that the borrowing is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BorrowingStatus::REJECTED,
        ]);
    }
}
