<?php

namespace App\Livewire\Admin\Pages\Document;

use App\Livewire\Forms\DocumentCorrectionForm;
use App\Models\Asset;
use App\Models\Location as ModelsLocation;
use App\Models\Organization as ModelsOrganization;
use App\Repositories\Contracts\AssetRepositoryInterface;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Services\DocumentDataCorrectionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class DataDocumentModal extends Component
{
    public DocumentCorrectionForm $form;
    public $allOrganizations;
    public $allLocations;
    public array $editingLocationIndex = [];

    #[On('setDataDocument')]
    public function setData(array $data)
    {
        $this->form->setData($data);
        foreach ($this->form->events as $index => $event) {
            $this->editingLocationIndex[$index] = null;
        }

        $this->allOrganizations = app(OrganizationRepositoryInterface::class)->getAllOrderedByName();
        $this->allLocations = app(LocationRepositoryInterface::class)->getAllOrderedByName();
    }

    public function editLocation($eventIndex, $locIndex)
    {
        $this->editingLocationIndex[$eventIndex] = $locIndex;
    }

    public function cancelEditLocation($eventIndex)
    {
        if ($this->editingLocationIndex[$eventIndex] === null) return;

        if (empty($this->form->events[$eventIndex]['location_data'][$this->editingLocationIndex[$eventIndex]]['name'])) {
            $this->removeLocation($eventIndex, $this->editingLocationIndex[$eventIndex]);
        }

        $this->editingLocationIndex[$eventIndex] = null;
    }

    private function handleServiceCall(callable $serviceCall): void
    {
        $currentData = $this->form->getData();
        $updatedData = $serviceCall($currentData);
        $this->form->setData($updatedData);
    }

    public function removeLocation(int $eventIndex, int $locIndex): void
    {
        $this->handleServiceCall(fn($data) => app(DocumentDataCorrectionService::class)->removeLocation($data, $eventIndex, $locIndex));
    }

    public function addLocation(int $eventIndex): void
    {
        $this->handleServiceCall(fn($data) => app(DocumentDataCorrectionService::class)->addLocation($data, $eventIndex));
        $locationsCount = count($this->form->events[$eventIndex]['location_data'] ?? []);
        $this->editingLocationIndex[$eventIndex] = $locationsCount - 1;
    }

    public function updateLocation(int $eventIndex, int $locIndex, int $locationId): void
    {
        if (empty($locationId)) return;
        $this->handleServiceCall(fn($data) => app(DocumentDataCorrectionService::class)->updateLocation($data, $eventIndex, $locIndex, $locationId));
        $this->cancelEditLocation($eventIndex);
    }

    public function acceptAsExternalLocation(int $eventIndex, int $locIndex): void
    {
        $this->form->events[$eventIndex]['location_data'][$locIndex]['source'] = 'custom';
    }

    public function selectLocationFromDB(int $eventIndex, int $locIndex): void
    {
        $this->form->events[$eventIndex]['location_data'][$locIndex]['source'] = 'location';
    }

    public function removeDateRange(int $eventIndex, int $dateIndex): void
    {
        $this->handleServiceCall(fn($data) => app(DocumentDataCorrectionService::class)->removeDateRange($data, $eventIndex, $dateIndex));
    }

    public function addDateRange(int $eventIndex): void
    {
        $this->handleServiceCall(fn($data) => app(DocumentDataCorrectionService::class)->addDateRange($data, $eventIndex));
    }

    public function removeItem(string $collectionKey, int $eventIndex, int $itemIndex): void
    {
        $this->handleServiceCall(fn($data) => app(DocumentDataCorrectionService::class)->removeItem($data, $collectionKey, $eventIndex, $itemIndex));
    }

    public function linkItem(string $collectionKey, $masterId, $eventIndex = null, $itemIndex = null): void
    {
        $this->handleServiceCall(fn($data) => app(DocumentDataCorrectionService::class)->linkItem($data, $collectionKey, $masterId, $eventIndex, $itemIndex));
    }

    #[Computed]
    public function availableAssets(int $eventIndex)
    {
        $event = $this->form->events[$eventIndex] ?? null;
        $startDate = data_get($event, 'parsed_dates.dates.0.start');
        $endDate = data_get($event, 'parsed_dates.dates.0.end');

        if (!$startDate || !$endDate) {
            return collect();
        }
        return app(AssetRepositoryInterface::class)->getAvailableAssetsBetween($startDate, $endDate);
    }

    public function saveCorrections(): void
    {
        try {
            $this->form->validate();
            $validatedData = $this->form->getData();

            // Logika kecil untuk menentukan final_organization_id bisa tetap di sini atau dipindah ke service
            if (!data_get($validatedData, 'document_information.final_organization_id')) {
                $matchedOrgs = collect(data_get($validatedData, 'document_information.emitter_organizations', []))
                    ->where('match_status', 'matched');
                if ($matchedOrgs->count() === 1) {
                    data_set($validatedData, 'document_information.final_organization_id', $matchedOrgs->first()['nama_organisasi_id']);
                }
            }

            $this->dispatch('setDataForEvent', data: $validatedData)->to(EventModal::class);
            $this->dispatch('open-modal', 'event-modal');
            $this->dispatch('close-modal', 'document-data-modal');
        } catch (ValidationException $e) {
            flash()->error('Ada kesalahan dalam data yang dimasukkan. Silakan periksa kembali.');
            Log::error('Validation error in DataDocumentModal', ['errors' => $e->errors(), 'data' => $this->form->getData()]);
            throw ValidationException::withMessages($e->errors());
        } catch (\Throwable $th) {
            flash()->error('Terjadi kesalahan tak terduga pada sistem.');
            Log::error('Unexpected error in saveCorrections', ['message' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
        }
    }

    public function render()
    {
        return view('livewire.admin.pages.document.data-document-modal');
    }
}
