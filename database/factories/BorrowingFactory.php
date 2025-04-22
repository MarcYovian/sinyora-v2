<?php

namespace Database\Factories;

use App\Enums\BorrowingStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;

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
        return [
            'created_by' => User::first()->id,
            'start_datetime' => $this->faker->dateTimeBetween('+1 day', '+3 days'),
            'end_datetime' => $this->faker->dateTimeBetween('+4 days', '+30 days'),
            'notes' => $this->faker->sentence(),
            'borrower' => $this->faker->name(),
            'borrower_phone' => $this->faker->phoneNumber(),
            'status' => BorrowingStatus::PENDING,
        ];
    }
}
