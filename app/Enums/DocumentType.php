<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesEnumUtilities;

enum DocumentType: string
{
    use ProvidesEnumUtilities;

    case BORROWING = 'peminjaman';
    case LICENSING = 'perizinan';
    case INVITATION = 'undangan';

    public function label(): string
    {
        return match ($this) {
            self::BORROWING => 'Peminjaman',
            self::LICENSING => 'Perizinan',
            self::INVITATION => 'Undangan',
        };
    }

    public static function values()
    {
        return [
            self::BORROWING->value,
            self::LICENSING->value,
            self::INVITATION->value,
        ];
    }
}
