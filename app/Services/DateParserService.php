<?php

namespace App\Services;

use App\Exceptions\ParsingFailedException;
use Carbon\Carbon;

class DateParserService
{
    protected static $indonesianMonths = [
        'januari' => 1,
        'februari' => 2,
        'maret' => 3,
        'april' => 4,
        'mei' => 5,
        'juni' => 6,
        'juli' => 7,
        'agustus' => 8,
        'september' => 9,
        'oktober' => 10,
        'november' => 11,
        'desember' => 12,
    ];

    /**
     * Titik masuk utama untuk mem-parsing teks.
     *
     * @param string $text Teks yang akan di-parse (misal: "Sabtu, 5 - 6 Juli 2025").
     * @param string $fieldType Tipe field ('date' atau 'time') untuk menentukan aturan.
     * @return array Data terstruktur jika berhasil.
     * @throws ParsingFailedException jika semua aturan gagal.
     */
    public static function parse(string $text, string $fieldType = 'date'): array
    {
        $text = strtolower(trim($text));

        if ($fieldType === 'date') {
            $parsers = ['_tryParseDateList', '_tryParseDateRange', '_tryParseSingleDate'];
        } elseif ($fieldType === 'time') {
            $parsers = ['_tryParseTimeRange', '_tryParseTimeRangeSelesai', '_tryParseSingleTime'];
        } else {
            throw new ParsingFailedException("Tipe field tidak valid.", $fieldType);
        }

        foreach ($parsers as $parser) {
            $result = self::$parser($text);
            if ($result !== null) {
                return $result;
            }
        }

        // Jika semua parser gagal, lemparkan exception.
        throw new ParsingFailedException("Format untuk '$text' tidak dapat kami proses.", $fieldType);
    }

    /**
     * Konversi nama bulan Indonesia ke nomor bulan.
     */
    private static function _convertMonth(string $monthName): ?int
    {
        return self::$indonesianMonths[strtolower($monthName)] ?? null;
    }

    // --- Parser untuk Tanggal ---

    private static function _tryParseSingleDate(string $text): ?array
    {
        // Mencocokkan format seperti "7 juli 2025" atau "senin, 7 juli 2025"
        if (preg_match('/^(?:[a-z\s,-]+,)?\s*(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})$/', $text, $matches)) {
            $day = $matches[1];
            $month = self::_convertMonth($matches[2]);
            $year = $matches[3];

            if ($month) {
                return [
                    'type' => 'single',
                    'date' => Carbon::create($year, $month, $day)->toDateString(),
                ];
            }
        }
        return null;
    }

    private static function _tryParseDateRange(string $text): ?array
    {
        // Mencocokkan "5 - 6 juli 2025" atau "31 mei dan 02 juni 2024"
        if (preg_match('/^(?:[a-z\s,-]+,)?\s*(\d{1,2})\s*(-|—|sampai)\s*(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})$/', $text, $matches)) {
            // Rentang dalam bulan yang sama
            $startDay = $matches[1];
            $endDay = $matches[3];
            $month = self::_convertMonth($matches[4]);
            $year = $matches[5];

            if ($month) {
                return [
                    'type' => 'range',
                    'start_date' => Carbon::create($year, $month, $startDay)->toDateString(),
                    'end_date' => Carbon::create($year, $month, $endDay)->toDateString(),
                ];
            }
        }
        return null;
    }

    private static function _tryParseDateList(string $text): ?array
    {
        // Mencocokkan "02, 09, 16, 23, dan 30 juni 2024"
        $parts = preg_split('/( dan |,)\s*/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        $dates = [];
        $lastMonth = null;
        $lastYear = null;

        // Membersihkan array dari pemisah (separator)
        $cleanParts = [];
        foreach ($parts as $part) {
            if (trim($part) !== 'dan' && trim($part) !== ',') {
                $cleanParts[] = trim($part);
            }
        }

        // Iterasi dari belakang untuk mendapatkan bulan dan tahun terlebih dahulu
        foreach (array_reverse($cleanParts) as $part) {
            // Coba parse sebagai tanggal penuh
            if (preg_match('/^(?:[a-z\s,-]+,)?\s*(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})$/', $part, $matches)) {
                $day = $matches[1];
                $month = self::_convertMonth($matches[2]);
                $year = $matches[3];
                if ($month) {
                    $dates[] = Carbon::create($year, $month, $day)->toDateString();
                    $lastMonth = $month;
                    $lastYear = $year;
                }
            }
            // Coba parse sebagai tanggal dan bulan (tahun dari entri sebelumnya)
            elseif ($lastYear && preg_match('/^(?:[a-z\s,-]+,)?\s*(\d{1,2})\s+([a-zA-Z]+)$/', $part, $matches)) {
                $day = $matches[1];
                $month = self::_convertMonth($matches[2]);
                if ($month) {
                    $dates[] = Carbon::create($lastYear, $month, $day)->toDateString();
                    $lastMonth = $month;
                }
            }
            // Coba parse sebagai tanggal saja (bulan & tahun dari entri sebelumnya)
            elseif ($lastMonth && $lastYear && preg_match('/^(?:[a-z\s,-]+,)?\s*(\d{1,2})$/', $part, $matches)) {
                $day = $matches[1];
                $dates[] = Carbon::create($lastYear, $lastMonth, $day)->toDateString();
            }
        }

        if (count($dates) > 1) {
            return ['type' => 'list', 'dates' => array_reverse($dates)]; // Balikkan urutan agar benar
        }

        return null;
    }

    // --- Parser untuk Waktu ---

    private static function _tryParseSingleTime(string $text): ?array
    {
        if (preg_match('/^(\d{2}[:.]\d{2})(?:\s*wib)?(?:\s*\(.*\))?$/i', $text, $matches)) {
            return [
                'type' => 'single',
                'time' => str_replace('.', ':', $matches[1]),
            ];
        }
        return null;
    }

    private static function _tryParseTimeRange(string $text): ?array
    {
        if (preg_match('/^(\d{2}[:.]\d{2})\s*(-|—)\s*(\d{2}[:.]\d{2})(?:\s*wib)?(?:\s*\(.*\))?$/i', $text, $matches)) {
            return [
                'type' => 'range',
                'start_time' => str_replace('.', ':', $matches[1]),
                'end_time' => str_replace('.', ':', $matches[3]),
            ];
        }
        return null;
    }

    private static function _tryParseTimeRangeSelesai(string $text): ?array
    {
        if (preg_match('/^(\d{2}[:.]\d{2})\s*(-|—)\s*selesai(?:\s*wib)?(?:\s*\(.*\))?$/i', $text, $matches)) {
            return [
                'type' => 'range_open_end',
                'start_time' => str_replace('.', ':', $matches[1]),
                'end_time' => 'selesai',
            ];
        }
        return null;
    }
}
