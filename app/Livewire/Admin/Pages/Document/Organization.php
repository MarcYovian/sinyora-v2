<?php

namespace App\Livewire\Admin\Pages\Document;

use App\Livewire\Forms\EventForm;
use App\Models\LicensingDocument;
use App\Models\Organization as ModelsOrganization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class Organization extends Component
{
    public array $data = [];
    public bool $hasUnmatchedOrganizations = false;
    public $allOrganizations;
    public $unmatchedOrganizationName = '';
    public $unmatchedOrganizationIndex = '';
    public $selectedOrganizationId = null;
    public $newOrganizationName = '';
    public $newOrganizationCode = '';
    public bool $showCreateForm = false;

    #[On('setDataForOrganization')]
    public function setData(array $data)
    {
        $this->reset(
            'data',
            'hasUnmatchedOrganizations',
            'unmatchedOrganizationName',
            'selectedOrganizationId',
            'newOrganizationName',
            'newOrganizationCode',
            'showCreateForm'
        );

        $this->data = $data;

        // Ambil semua organisasi dari DB untuk dropdown
        $this->allOrganizations = ModelsOrganization::orderBy('name')->get();
        $this->checkForUnmatchedOrganizations();
    }

    private function checkForUnmatchedOrganizations()
    {
        $this->hasUnmatchedOrganizations = false;
        foreach ($this->data['informasi_umum_dokumen']['organisasi'] ?? [] as $index => $organisasi) {
            if (($organisasi['match_status'] ?? 'unmatched') === 'unmatched') {
                $this->unmatchedOrganizationName = $organisasi['original_name'] ?? 'Tidak Dikenali';
                $this->unmatchedOrganizationIndex = $index;
                $this->hasUnmatchedOrganizations = true;
                return; // Cukup temukan satu yang tidak cocok
            }
        }
    }

    /**
     * Menampilkan atau menyembunyikan form untuk membuat organisasi baru.
     */
    public function toggleCreateForm()
    {
        $this->showCreateForm = !$this->showCreateForm;
        if ($this->showCreateForm) {
            $this->selectedOrganizationId = null; // Reset pilihan jika ingin membuat baru
            $this->newOrganizationName = $this->unmatchedOrganizationName;
            $this->newOrganizationCode = '';
        }
    }

    /**
     * Membuat organisasi baru berdasarkan input pengguna.
     */
    public function createNewOrganization()
    {
        $this->validate(['newOrganizationName' => 'required|string|max:255|unique:organizations,name']);

        $newOrg = ModelsOrganization::create(['name' => $this->newOrganizationName, 'code' => $this->newOrganizationCode]);

        // Muat ulang daftar organisasi dan langsung pilih yang baru dibuat
        $this->allOrganizations = ModelsOrganization::orderBy('name')->get();
        $this->selectedOrganizationId = $newOrg->id;

        // Reset dan sembunyikan form create
        $this->newOrganizationName = '';
        $this->showCreateForm = false;

        toastr()->success('Organisasi baru berhasil dibuat.');
    }

    public function save()
    {
        $this->validate(['selectedOrganizationId' => 'required|exists:organizations,id']);

        $this->data['informasi_umum_dokumen']['organisasi'][$this->unmatchedOrganizationIndex]['nama_organisasi_id'] = $this->selectedOrganizationId;
        $this->data['informasi_umum_dokumen']['organisasi'][$this->unmatchedOrganizationIndex]['nama_organisasi'] = $this->allOrganizations->find($this->selectedOrganizationId)->name;
        $this->data['informasi_umum_dokumen']['organisasi'][$this->unmatchedOrganizationIndex]['match_status'] = 'matched';

        $type = $this->data['type'];

        if ($type === 'perizinan') {
            foreach ($this->data['detail_kegiatan'] as $event) {
                foreach ($event['location_data'] as $location) {
                    if (($location['match_status'] ?? 'unmatched') === 'unmatched') {
                        $this->dispatch('close-modal', 'organization-modal');
                        $this->dispatch('setDataForLocation', data: $this->data)->to(Location::class);
                        $this->dispatch('open-modal', 'location-modal');
                        return;
                    }
                }

                $this->dispatch('close-modal', 'organization-modal');
                $this->dispatch('setDataForEvent', data: $this->data)->to(EventModal::class);
                $this->dispatch('open-modal', 'event-modal');
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.pages.document.organization');
    }
}
