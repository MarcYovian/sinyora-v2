<?php

namespace App\Enums\Concerns;

trait ProvidesEnumUtilities
{
    /**
     * Menghasilkan array dari semua nilai (value) enum.
     * ['pending', 'processed', 'done']
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Menghasilkan array asosiatif untuk opsi dropdown.
     * ['pending' => 'Pending', 'processed' => 'Processed', ...]
     */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            function ($carry, $case) {
                // Asumsi setiap case punya method label()
                $carry[$case->value] = $case->label();
                return $carry;
            },
            []
        );
    }
}
