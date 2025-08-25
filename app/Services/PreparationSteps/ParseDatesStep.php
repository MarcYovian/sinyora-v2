<?php

namespace App\Services\PreparationSteps;

use App\Exceptions\ParsingFailedException;
use App\Services\DateParserService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Closure;
use Exception;
use Illuminate\Support\Facades\Log;

class ParseDatesStep implements PreparationStepInterface
{
    public function __construct(protected DateParserService $dateParser) {}

    public function handle(array $data, Closure $next): array
    {
        try {
            $data = $this->process($data);
            Log::info('ParseDatesStep selesai');
            return $next($data);
            //code...
        } catch (Exception $e) {
            // Log error tapi jangan throw exception
            Log::error('ParseDatesStep gagal: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e
            ]);

            // Tambahkan informasi error ke data
            $data['processing_errors'][] = [
                'step' => 'ParseDatesStep',
                'error' => $e->getMessage(),
                'timestamp' => now()
            ];

            // Kembalikan data asli agar step berikutnya tetap jalan
            return $next($data);
        }
    }

    public function process(array $data): array
    {
        try {
            $originalDocDate = data_get($data, 'document_information.document_date');
            if ($originalDocDate) {
                $parsedDate = $this->dateParser->parse($originalDocDate, 'date');

                if ($parsedDate['type'] !== 'single') {
                    // Jika tipe bukan single (misal: rentang), hasilnya adalah error
                    $result = [
                        'status' => 'error',
                        'type' => $parsedDate['type'],
                        'date' => $originalDocDate,
                        'messages' => "Tanggal dokumen wajib berupa tanggal tunggal.",
                    ];
                } else {
                    // Jika sukses, simpan hasilnya
                    $result = [
                        'status' => 'success',
                        'type' => $parsedDate['type'],
                        'date' => $parsedDate['date'],
                        'messages' => '',
                    ];
                }
                data_set($data, 'document_information.document_date', $result);
            }
        } catch (ParsingFailedException $e) {
            // Jika parsing gagal total
            data_set($data, 'document_information.document_date', [
                'status' => 'error',
                'type' => 'invalid',
                'date' => data_get($data, 'document_information.document_date'),
                'messages' => $e->getMessage(),
            ]);
            Log::error('Gagal parsing tanggal utama dokumen: ' . $e->getMessage());
        }

        $events = data_get($data, 'events', []);
        if (empty($events)) {
            return $data;
        }

        // Proses setiap event menggunakan referensi
        foreach ($events as &$event) {
            // 1. Proses tanggal dan waktu utama acara
            $this->processMainEventDateTime($event);

            // 2. Proses waktu untuk setiap item dalam jadwal (rundown)
            if (!empty($event['schedule'])) {
                $this->processScheduleTimes($event['schedule']);
            }
        }
        unset($event);

        // Set kembali data events yang sudah dimodifikasi
        data_set($data, 'events', $events);
        return $data;
    }

    /**
     * Memproses tanggal & waktu utama dari sebuah event dan menambahkan key 'parsed_dates'.
     */
    private function processMainEventDateTime(array &$event): void
    {
        $dateStr = $event['date'] ?? '';
        $timeStr = $event['time'] ?? '';

        try {
            // Panggil service untuk membedah string tanggal dan waktu.
            $parsedDate = $this->dateParser->parse($dateStr, 'date');
            $parsedTime = $this->dateParser->parse($timeStr, 'time');

            // Logika untuk menggabungkan hasil parsing tanggal dan waktu
            $combinedDates = $this->combineParsedResults($parsedDate, $parsedTime);

            // Tambahkan key baru 'parsed_dates' ke event dengan status sukses
            $event['parsed_dates'] = [
                'status'  => 'success',
                'dates'   => $combinedDates,
                'message' => null,
            ];
        } catch (ParsingFailedException | Exception $e) {
            Log::error("Gagal mem-parsing datetime untuk '{$dateStr}' & '{$timeStr}'. Error: " . $e->getMessage());
            // Tambahkan key 'parsed_dates' ke event dengan status error
            $event['parsed_dates'] = [
                'status'  => 'error',
                'dates'   => [],
                'message' => $e->getMessage(),
            ];
        }

        Log::info("Parsed dates for event: " . json_encode($event['parsed_dates']));
    }

    /**
     * Memproses waktu mulai dan selesai untuk setiap item di dalam jadwal (rundown).
     */
    private function processScheduleTimes(array &$schedule): void
    {
        foreach ($schedule as &$item) {
            // Proses Start Time
            $item['parsed_start_time'] = $this->parseSingleTime($item['startTime'] ?? null);
            // Proses End Time
            $item['parsed_end_time'] = $this->parseSingleTime($item['endTime'] ?? null);
        }
        unset($item);
    }

    /**
     * Helper untuk mem-parsing satu string waktu dan mengembalikan struktur array yang konsisten.
     */
    private function parseSingleTime(?string $timeString): array
    {
        if (empty($timeString)) {
            return ['status' => 'success', 'time' => null, 'message' => null];
        }

        try {
            $parsedTime = $this->dateParser->parse($timeString, 'time');
            return [
                'status'  => 'success',
                'time'    => $parsedTime['time'] ?? $parsedTime['start_time'] ?? null,
                'message' => null,
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'time'    => $timeString, // Kembalikan nilai asli
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Menggabungkan hasil parsing tanggal dan waktu menjadi rentang datetime yang konkret.
     */
    private function combineParsedResults(array $parsedDate, array $parsedTime): array
    {
        $startTime = Carbon::createFromTime(0, 0);
        $endTime = Carbon::createFromTime(23, 59, 59);

        switch ($parsedTime['type']) {
            case 'single':
                $startTime = Carbon::parse($parsedTime['time']);
                $endTime = $startTime->copy()->addHours(3); // Asumsi durasi 3 jam
                break;
            case 'range':
                $startTime = Carbon::parse($parsedTime['start_time']);
                $endTime = Carbon::parse($parsedTime['end_time']);
                break;
            case 'range_open_end':
                $startTime = Carbon::parse($parsedTime['start_time']);
                $endTime = $startTime->copy()->addHours(3); // Asumsi durasi 3 jam
                break;
        }

        $eventDates = [];
        switch ($parsedDate['type']) {
            case 'single':
                $eventDates[] = Carbon::parse($parsedDate['date']);
                break;
            case 'list':
                foreach ($parsedDate['dates'] as $date) $eventDates[] = Carbon::parse($date);
                break;
            case 'range':
                $period = CarbonPeriod::create($parsedDate['start_date'], $parsedDate['end_date']);
                foreach ($period as $date) $eventDates[] = $date;
                break;
        }

        $result = [];
        foreach ($eventDates as $date) {
            $startDateTime = $date->copy()->setTimeFrom($startTime);
            $endDateTime = $date->copy()->setTimeFrom($endTime);
            if ($endDateTime->lt($startDateTime)) {
                $endDateTime->addDay();
            }
            $result[] = [
                'start' => $startDateTime->format('Y-m-d\TH:i'), // Format Y-m-d H:i:s
                'end'   => $endDateTime->format('Y-m-d\TH:i'),
            ];
        }
        return $result;
    }
}
