<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Helper service untuk menggabungkan string tanggal dan waktu Indonesia
 * menjadi format datetime terstruktur.
 */
class DateTimeHelperService
{
    private static array $indonesianMonths = [
        'januari' => 'january',
        'februari' => 'february',
        'maret' => 'march',
        'april' => 'april',
        'mei' => 'may',
        'juni' => 'june',
        'juli' => 'july',
        'agustus' => 'august',
        'september' => 'september',
        'oktober' => 'october',
        'november' => 'november',
        'desember' => 'december'
    ];

    /**
     * Menggabungkan string tanggal dan waktu Indonesia menjadi datetime terstruktur.
     *
     * @param string $dateStr String tanggal (misal: "Sabtu, 5 - 6 Juli 2025")
     * @param string $timeStr String waktu (misal: "09.00 - 12.00 WIB")
     * @return array|null ['start' => 'Y-m-d\TH:i', 'end' => 'Y-m-d\TH:i'] atau null jika gagal
     */
    public static function combineDateTime(string $dateStr, string $timeStr): ?array
    {
        if (empty($dateStr) || empty($timeStr)) return null;

        try {
            $dateStrEnglish = str_ireplace(
                array_keys(self::$indonesianMonths),
                array_values(self::$indonesianMonths),
                $dateStr
            );

            Carbon::setLocale('id_ID');

            // Langkah 1: Parse tanggal mulai dan selesai
            $dateParts = explode(' - ', $dateStrEnglish);
            $startDateStr = trim($dateParts[0]);
            $endDateStr = trim(end($dateParts));
            $startdatePart = preg_replace('/^\w+,\s*/', '', $startDateStr);
            $enddatePart = preg_replace('/^\w+,\s*/', '', $endDateStr);
            $startDate = Carbon::parse(trim($startdatePart));
            $endDate = Carbon::parse(trim($enddatePart));

            // Cari semua pola waktu
            preg_match_all('/(\d{1,2})[.|:]\s?(\d{1,2})/', $timeStr, $matches);

            // Langkah 2: Parse waktu mulai dan selesai
            if (count($matches[0]) > 0) {
                $timeParts = explode(' - ', $timeStr);
                $startTimeStr = trim($timeParts[0]);
                $endTimeStr = trim(end($timeParts));
                $startTimeMatch = preg_match('/(\d{1,2})[.|:]\s?(\d{1,2})/', $startTimeStr, $startTimeMatches);
                $endTimeMatch = preg_match('/(\d{1,2})[.|:]\s?(\d{1,2})/', $endTimeStr, $endTimeMatches);

                if ($startTimeMatch && count($startTimeMatches) >= 3) {
                    $startDate->setTime(intval($startTimeMatches[1]), intval($startTimeMatches[2]));
                }

                if ($endTimeMatch && count($endTimeMatches) >= 3) {
                    $endDate->setTime(intval($endTimeMatches[1]), intval($endTimeMatches[2]));
                }
            } else {
                $startDate->setTime(0, 0);
                $endDate->setTime(23, 59);
            }

            // Langkah 3: Kembalikan hasil terformat
            return [
                'start' => $startDate->format('Y-m-d\TH:i'),
                'end' => $endDate->format('Y-m-d\TH:i')
            ];
        } catch (\Exception $e) {
            Log::error("Gagal mem-parsing datetime untuk '{$dateStr}' & '{$timeStr}'. Error: " . $e->getMessage());
            return null;
        }
    }
}
