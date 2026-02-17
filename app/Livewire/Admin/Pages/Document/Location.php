<?php

namespace App\Livewire\Admin\Pages\Document;

use App\Models\Location as ModelsLocation;
use App\Services\DateTimeHelperService;
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

        foreach ($this->data['detail_kegiatan'] as $index => &$event) {
            // Proses datetime
            $datetime = DateTimeHelperService::combineDateTime($event['tanggal_kegiatan'], $event['jam_kegiatan']);
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
}
