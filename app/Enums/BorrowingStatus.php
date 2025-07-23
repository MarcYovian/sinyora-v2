<?php

namespace App\Enums;

enum BorrowingStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            self::APPROVED => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            self::REJECTED => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        };
    }

    public function borderColor(): string
    {
        return match ($this) {
            self::PENDING => 'border-l-yellow-400 dark:border-l-yellow-600',
            self::APPROVED => 'border-l-green-500 dark:border-l-green-600',
            self::REJECTED => 'border-l-red-500 dark:border-l-red-600',
        };
    }

    public static function values(): array
    {
        return [
            self::PENDING->value,
            self::APPROVED->value,
            self::REJECTED->value,
        ];
    }
}
