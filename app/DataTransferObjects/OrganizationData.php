<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class OrganizationData extends Data
{
    public function __construct(
        public string $name,
    ) {}
}
