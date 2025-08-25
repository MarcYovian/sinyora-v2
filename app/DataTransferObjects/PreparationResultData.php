<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class PreparationResultData extends Data
{
    public function __construct(
        public array $preparedData,
        public bool $hasErrors,
    ) {}
}
