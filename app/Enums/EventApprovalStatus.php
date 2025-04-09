<?php

namespace App\Enums;

enum EventApprovalStatus: String
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
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
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
