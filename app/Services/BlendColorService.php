<?php

namespace App\Services;

class BlendColorService
{
    const DEFAULT_COLOR = '#6B7280';

    /**
     * Blends an array of hex color codes into a single average color.
     *
     * @param array $hexColors An array of hex color strings to be blended.
     * @param string $defaultColor The default color to return if no colors are provided.
     * @return string The resulting blended hex color code.
     */

    public static function blend(array $hexColors = [], string $defaultColor = self::DEFAULT_COLOR): string
    {
        $totalColors = count($hexColors);
        if ($totalColors === 0) {
            return $defaultColor; // Warna abu-abu default jika tidak ada lokasi
        }
        if ($totalColors === 1) {
            return $hexColors[0]; // Kembalikan warna itu sendiri jika hanya ada satu
        }

        $red = $green = $blue = 0;

        foreach ($hexColors as $hex) {
            // Hapus karakter '#'
            $hex = ltrim($hex, '#');
            // Konversi hex ke desimal
            $red   += hexdec(substr($hex, 0, 2));
            $green += hexdec(substr($hex, 2, 2));
            $blue  += hexdec(substr($hex, 4, 2));
        }

        // Hitung rata-rata untuk setiap komponen warna
        $avgRed   = round($red / $totalColors);
        $avgGreen = round($green / $totalColors);
        $avgBlue  = round($blue / $totalColors);

        // Konversi kembali ke hex dan format dengan padding nol jika perlu
        return '#' . sprintf('%02x', $avgRed) . sprintf('%02x', $avgGreen) . sprintf('%02x', $avgBlue);
    }
}
