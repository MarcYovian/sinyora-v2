<?php

namespace Database\Factories;

use App\Enums\EventApprovalStatus;
use App\Enums\EventRecurrenceType;
use App\Models\EventCategory;
use App\Models\Organization;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, '+2 months');
        $user = User::first() ?? User::factory()->create();

        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'start_recurring' => $startDate->format('Y-m-d'),
            'end_recurring' => $endDate->format('Y-m-d'),
            'status' => $this->faker->randomElement(EventApprovalStatus::cases()),
            'creator_id' => $user->id,
            'creator_type' => $user->getMorphClass(),
            'recurrence_type' => $this->faker->randomElement(EventRecurrenceType::cases()),
            'organization_id' => Organization::factory(),
            'event_category_id' => EventCategory::factory(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($event) {
            // Create event recurrences based on the event type
            $startDate = Carbon::parse($event->start_recurring);
            $endDate = Carbon::parse($event->end_recurring);

            if (
                $event->recurrence_type === EventRecurrenceType::CUSTOM ||
                $event->recurrence_type === EventRecurrenceType::DAILY
            ) {
                $this->createDailyRecurrences($event, $startDate, $endDate);
            } else {
                $this->createRecurringEvents($event, $startDate, $endDate);
            }

            // Attach random locations
            $event->locations()->attach(
                \App\Models\Location::factory()->count(rand(1, 3))->create()
            );
        });
    }

    protected function createRecurringEvents($event, $startDate, $endDate)
    {
        $interval = match ($event->recurrence_type) {
            EventRecurrenceType::WEEKLY => '1 week',
            EventRecurrenceType::BIWEEKLY => '2 weeks',
            EventRecurrenceType::MONTHLY => '1 month',
            default => '1 day',
        };

        $period = CarbonPeriod::create(
            $startDate,
            $interval,
            $endDate
        );

        foreach ($period as $date) {
            $this->createEventRecurrence($event, $date);
        }
    }

    protected function createDailyRecurrences($event, $startDate, $endDate)
    {
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $this->createEventRecurrence($event, $date);
        }
    }

    protected function createEventRecurrence($event, $date)
    {
        $startTime = $this->faker->time('H:i:s');
        $endTime = $this->faker->time('H:i:s', $startTime);

        $event->eventRecurrences()->create([
            'date' => $date->format('Y-m-d'),
            'time_start' => $startTime,
            'time_end' => $endTime,
        ]);
    }
}
