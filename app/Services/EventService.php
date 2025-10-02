<?php

namespace App\Services;

use App\Repositories\Contracts\EventRepositoryInterface;

class EventService
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected EventRepositoryInterface $eventRepository) {}

    public function getAllEvents()
    {
        return $this->eventRepository->all();
    }

    public function getMassEvents()
    {
        return $this->eventRepository->getMassEvents();
    }

    public function getUpcomingMassSchedule($limit = 3)
    {
        $now = now();
        $upcomingSchedules = collect();

        // Ambil semua event misa yang relevan
        $massEvents = $this->eventRepository->getMassEvents();

        // Iterasi setiap event untuk mencari jadwal berulang (recurrence) yang akan datang
        foreach ($massEvents as $event) {
            foreach ($event->eventRecurrences as $recurrence) {
                // Gabungkan tanggal dan waktu menjadi satu objek Carbon untuk perbandingan
                $scheduleDateTime = $recurrence->date->copy()->setTimeFromTimeString($recurrence->time_start->format('H:i:s'));

                // Jika jadwal ada di masa depan, tambahkan ke koleksi
                if ($scheduleDateTime->isFuture()) {
                    $upcomingSchedules->push([
                        'name' => $event->name,
                        'date' => $recurrence->date,
                        'time_start' => $recurrence->time_start,
                        'datetime' => $scheduleDateTime, // Tambahkan untuk sorting
                    ]);
                }
            }
        }

        return $upcomingSchedules->sortBy('datetime')->take($limit);
    }
}
