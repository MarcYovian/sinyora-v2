<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case PENDING = 'pending';
    case PROCESSED = 'processed';
    case DONE = 'done';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSED => 'Processed',
            self::DONE => 'Done',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::PROCESSED => 'green',
            self::DONE => 'blue',
        };
    }

    public static function values()
    {
        return [
            self::PENDING->value,
            self::PROCESSED->value,
            self::DONE->value,
        ];
    }
}
