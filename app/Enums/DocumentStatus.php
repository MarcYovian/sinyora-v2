<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesEnumUtilities;

enum DocumentStatus: string
{
    use ProvidesEnumUtilities;

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
            self::PENDING => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            self::PROCESSED => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            self::DONE => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
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
