<?php

namespace App\Livewire\Traits;

use Carbon\Carbon;

trait CalendarLayoutTrait
{
    /**
     * Menghitung properti layout untuk acara yang tumpang tindih,
     * dengan logika penggabungan untuk acara yang identik.
     * @param \Illuminate\Support\Collection $dayEvents
     */
    private function calculateLayoutProperties(\Illuminate\Support\Collection $dayEvents)
    {
        if ($dayEvents->isEmpty()) {
            return;
        }

        $sortedEvents = $dayEvents->sortBy(function ($r) {
            return $r->date->copy()->setTimeFromTimeString($r->getRawOriginal('time_start'));
        });

        $groups = []; // Akan menyimpan grup acara yang saling tumpang tindih

        foreach ($sortedEvents as $event) {
            $placed = false;
            foreach ($groups as &$group) {
                // Cek jika event ini tumpang tindih dengan event terakhir di grup
                $lastEventInGroup = end($group);
                $lastEventEnd = $lastEventInGroup->date->copy()->setTimeFromTimeString($lastEventInGroup->getRawOriginal('time_end'));
                if ($lastEventEnd->isBefore($lastEventInGroup->date->copy()->setTimeFromTimeString($lastEventInGroup->getRawOriginal('time_start')))) $lastEventEnd->addDay();

                $currentEventStart = $event->date->copy()->setTimeFromTimeString($event->getRawOriginal('time_start'));

                if ($currentEventStart->lt($lastEventEnd)) {
                    $group[] = $event;
                    $placed = true;
                    break;
                }
            }
            if (!$placed) {
                $groups[] = [$event]; // Buat grup baru
            }
        }

        // Proses setiap grup untuk menentukan layout
        foreach ($groups as $group) {
            $this->processCollisionGroup($group);
        }
    }

    private function processCollisionGroup(array $group)
    {
        // Kelompokkan sub-grup berdasarkan event yang identik (nama & waktu sama)
        $subGroups = collect($group)->groupBy(function ($recurrence) {
            $start = Carbon::parse($recurrence->getRawOriginal('time_start'))->format('H:i');
            $end = Carbon::parse($recurrence->getRawOriginal('time_end'))->format('H:i');
            return $recurrence->event->name . '|' . $start . '|' . $end; // Kunci unik
        });

        $columnCount = $subGroups->count();
        $columnIndex = 0;

        foreach ($subGroups as $identicalEvents) {
            $width = 100 / $columnCount;
            $left = $columnIndex * $width;

            // Ambil event pertama sebagai "master" untuk ditampilkan
            $masterEvent = $identicalEvents->first();
            $masterEvent->is_grouped_master = true;

            // Kumpulkan semua lokasi dari event yang identik
            $groupedLocations = new \Illuminate\Support\Collection();
            foreach ($identicalEvents as $event) {
                // Gabungkan lokasi dari tabel 'locations' dan 'customLocations'
                $event->event->locations->each(function ($loc) use (&$groupedLocations) {
                    $groupedLocations->push($loc);
                });
                $event->event->customLocations->each(function ($loc) use (&$groupedLocations) {
                    $groupedLocations->push($loc);
                });
            }
            $masterEvent->grouped_locations = $groupedLocations;

            // Buat gradient dari warna lokasi
            $locationColors = $groupedLocations->pluck('color')->filter()->values()->all();
            $masterEvent->computed_background_gradient = $this->createGradient($locationColors);

            // Terapkan layout ke semua event dalam sub-grup ini
            foreach ($identicalEvents as $event) {
                $event->layout_width = $width;
                $event->layout_left = $left;
                $event->layout_zindex = 15 + $columnIndex;
            }

            $columnIndex++;
        }
    }

    /**
     * Membuat CSS linear-gradient dari array warna hex.
     * @param array $hexColors
     * @return string
     */
    private function createGradient(array $hexColors): string
    {
        if (empty($hexColors)) {
            return 'linear-gradient(to right, #e2e8f0, #e2e8f0)'; // Default abu-abu
        }
        if (count($hexColors) === 1) {
            return "linear-gradient(to right, {$hexColors[0]}, {$hexColors[0]})";
        }
        // Membuat gradien dari beberapa warna
        return 'linear-gradient(to right, ' . implode(', ', $hexColors) . ')';
    }
}
