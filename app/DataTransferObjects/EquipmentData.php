<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class EquipmentData extends Data
{
    public function __construct(
        public ?string $item,
        public int|string|null $quantity,
    ) {}
}
