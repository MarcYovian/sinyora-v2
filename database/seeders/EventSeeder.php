<?php

namespace Database\Seeders;

use App\Enums\EventApprovalStatus;
use App\Enums\EventRecurrenceType;
use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::factory()
            ->count(10)
            ->state([
                'recurrence_type' => EventRecurrenceType::WEEKLY,
                'status' => EventApprovalStatus::APPROVED,
            ])
            ->create();
    }
}
