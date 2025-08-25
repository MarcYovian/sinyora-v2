<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class OrganizerData extends Data
{
    public function __construct(
        public string $name,
        public ?string $contact
    ) {}
}
