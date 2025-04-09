<?php

namespace App\Enums;

enum EventRecurrenceType: String
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case BIWEEKLY = 'biweekly';
    case MONTHLY = 'monthly';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::DAILY => 'Daily',
            self::WEEKLY => 'Weekly',
            self::BIWEEKLY => 'Biweekly',
            self::MONTHLY => 'Monthly',
            self::CUSTOM => 'Custom',
        };
    }

    public static function values()
    {
        return [
            self::DAILY->value,
            self::WEEKLY->value,
            self::BIWEEKLY->value,
            self::MONTHLY->value,
            self::CUSTOM->value
        ];
    }
}
