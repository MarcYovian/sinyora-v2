<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class SignatureData extends Data
{
    public function __construct(
        public string $name,
        public ?string $position,
    ) {}
}
