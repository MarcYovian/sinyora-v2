<?php

namespace App\Livewire\Admin\Pages\Document;

use App\Models\Location as ModelsLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;


class Location extends Component
{
    public array $data = [];
    public bool $hasUnmatchedLocations = false;
    public $allLocations;
    public array $selectedLocationIds = [];
    public array $showCreateForms = [];
    public array $newLocationNames = [];

    #[On('setDataForLocation')]
    public function setData(array $data)
    {
        $this->reset(
            'data',
            'hasUnmatchedLocations',
        );

        $this->data = $data;
        dd($data);
        foreach ($this->data['detail_kegiatan'] as $index => &$event) {
            // Proses datetime
            $datetime = $this->combineDateTime($event['tanggal_kegiatan'], $event['jam_kegiatan']);
            $event['start_date'] = $datetime['start'];
            $event['end_date'] = $datetime['end'];

            // Inisialisasi state untuk setiap kegiatan
            $this->showCreateForms[$index] = false;
            $this->newLocationNames[$index] = '';

            // Jika lokasi sudah cocok, langsung isi ID-nya
            if (($event['location_data']['match_status'] ?? 'unmatched') === 'matched') {
                $this->selectedLocationIds[$index] = $event['location_data']['location_id'];
            } else {
                $this->selectedLocationIds[$index] = null;
            }
        }
        unset($event);

        // Ambil semua organisasi dari DB untuk dropdown
        $this->allLocations = ModelsLocation::orderBy('name')->get();
        $this->checkForUnmatchedLocations();
    }

    private function checkForUnmatchedLocations()
    {
        $this->hasUnmatchedLocations = false;
        foreach ($this->data['detail_kegiatan'] ?? [] as $index => $event) {
            if (!$this->selectedLocationIds[$index]) {
                $this->hasUnmatchedLocations = true;
                return; // Cukup temukan satu yang belum terisi
            }
        }
    }

    public function updatedSelectedLocationIds()
    {
        // Setiap kali pilihan berubah, cek ulang apakah semua sudah terisi
        $this->checkForUnmatchedLocations();
    }

    public function toggleCreateForm(int $index)
    {
        $this->showCreateForms[$index] = !$this->showCreateForms[$index];
        if ($this->showCreateForms[$index]) {
            $this->selectedLocationIds[$index] = null; // Reset pilihan jika ingin buat baru
        }
        $this->checkForUnmatchedLocations();
    }

    public function createNewLocation(int $index)
    {
        $this->validate([
            "newLocationNames.{$index}" => 'required|string|max:255|unique:locations,name'
        ]);

        $newLocation = ModelsLocation::create(['name' => $this->newLocationNames[$index]]);
        $this->allLocations = ModelsLocation::orderBy('name')->get();
        $this->selectedLocationIds[$index] = $newLocation->id;
        $this->showCreateForms[$index] = false;

        $this->checkForUnmatchedLocations();
        flash()->success('Lokasi baru berhasil dibuat.');
    }


    public function render()
    {
        return view('livewire.admin.pages.document.location');
    }

    private function combineDateTime($dateStr, $timeStr)
    {
        if (empty($dateStr) || empty($timeStr)) return null;

        try {
            $indonesianMonths = [
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
            $dateStrEnglish = str_ireplace(array_keys($indonesianMonths), array_values($indonesianMonths), $dateStr);

            Carbon::setLocale('id_ID');
            // --- Langkah 1: Ambil tanggal mulai dari string ---
            // Jika ada rentang tanggal (e.g., "sabtu... - minggu..."), ambil bagian pertama.
            $dateParts = explode(' - ', $dateStrEnglish);
            $startDateStr = trim($dateParts[0]); // Ambil elemen pertama
            $endDateStr = trim(end($dateParts)); // Ambil elemen terakhir
            $startdatePart = preg_replace('/^\w+,\s*/', '', $startDateStr);
            $enddatePart = preg_replace('/^\w+,\s*/', '', $endDateStr);
            $startDate = Carbon::parse(trim($startdatePart));
            $endDate = Carbon::parse(trim($enddatePart));
            // Gunakan preg_match_all untuk menemukan semua kecocokan
            preg_match_all('/(\d{1,2})[.|:]\s?(\d{1,2})/', $timeStr, $matches);

            // --- Langkah 2: Ambil waktu mulai dari string ---
            if (count($matches[0]) > 0) {
                $timeParts = explode(' - ', $timeStr);
                $startTimeStr = trim($timeParts[0]);
                $endTimeStr = trim(end($timeParts));
                $startTimeMatch = preg_match('/(\d{1,2})[.|:]\s?(\d{1,2})/', $startTimeStr, $startTimeMatches);
                $endTimeMatch = preg_match('/(\d{1,2})[.|:]\s?(\d{1,2})/', $endTimeStr, $endTimeMatches);

                if ($startTimeMatch && count($startTimeMatches) >= 3) {
                    $startHour = intval($startTimeMatches[1]);
                    $startMinute = intval($startTimeMatches[2]);
                    $startDate->setTime($startHour, $startMinute);
                }

                if ($endTimeMatch && count($endTimeMatches) >= 3) {
                    $endHour = intval($endTimeMatches[1]);
                    $endMinute = intval($endTimeMatches[2]);
                    $endDate->setTime($endHour, $endMinute);
                }
            } else {
                // Jika tidak ada waktu yang ditemukan, gunakan waktu default
                $startDate->setTime(0, 0); // Set ke tengah malam
                $endDate->setTime(23, 59); // Set ke akhir hari
            }
            // --- Langkah 3: Gabungkan tanggal dan waktu ---
            return [
                'start' => $startDate->format('Y-m-d\TH:i'),
                'end' => $endDate->format('Y-m-d\TH:i')
            ];
        } catch (\Exception $e) {
            Log::error("Gagal mem-parsing datetime untuk '{$dateStr}' & '{$timeStr}'. Error: " . $e->getMessage());
            return null; // Kembalikan null jika parsing gagal agar form tidak error
        }
    }
}
