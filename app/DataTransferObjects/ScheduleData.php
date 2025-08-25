<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class ScheduleData extends Data
{
    public function __construct(
        public string $description,
        public ?string $duration,
        public ?string $endTime,
        public ?string $startTime,
    ) {}
}
