<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class EventData extends Data
{
    public function __construct(
        public ?string $eventName,
        public ?string $date,
        public ?string $time,
        public ?string $location,

        #[DataCollectionOf(EquipmentData::class)]
        public ?DataCollection $equipment,

        public ?string $attendees,

        #[DataCollectionOf(OrganizerData::class)]
        public ?DataCollection $organizers,

        #[DataCollectionOf(ScheduleData::class)]
        public ?DataCollection $schedule,
    ) {}
}
