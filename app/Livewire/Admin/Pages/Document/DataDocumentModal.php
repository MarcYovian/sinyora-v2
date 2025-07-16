<?php

namespace App\Livewire\Admin\Pages\Document;

use App\Models\Asset;
use App\Models\Location as ModelsLocation;
use App\Models\Organization as ModelsOrganization;
use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class DataDocumentModal extends Component
{
    public Collection $data;
    public $allOrganizations;
    public $allLocations;
    public array $showLocationSelector = [];
    public array $editingLocationIndex = [];


    protected function rules()
    {
        return [
            // --- Document Information Validation ---
            'data.document_information.document_date.date' => [
                'required',
                'date',
                // Conditional: if original status was error, then it must be valid after manual input
                Rule::when($this->data['document_information']['document_date']['status'] === 'error', 'after_or_equal:1900-01-01'), // Example, adjust as needed
            ],
            'data.document_information.final_organization_id' => [
                'required',
                'exists:organizations,id', // Ensure the selected ID exists in your organizations table
            ],

            // --- Events Validation (Loop through each event) ---
            'data.events' => 'required|array|min:1',
            'data.events.*.eventName' => 'required|string|max:255',
            'data.events.*.fivetask_categories.nama' => 'required|string', // Assuming 'nama' is the category name

            // --- Event Dates Validation ---
            // If the original parsing for dates was an error, then all manually entered dates must be valid
            'data.events.*.dates' => 'required|array|min:1',
            'data.events.*.dates.*.start' => [
                'required',
                'date_format:Y-m-d\TH:i', // Matches datetime-local input format
                // Conditional validation for manual date entries if original was error
                Rule::when(
                    fn($input) => isset($input['data']['events'][$input['eventIndex']]['dates'][0]['status']) && $input['data']['events'][$input['eventIndex']]['dates'][0]['status'] === 'error',
                    'before_or_equal:data.events.*.dates.*.end' // Start must be before or equal to end
                ),
            ],
            'data.events.*.dates.*.end' => [
                'required',
                'date_format:Y-m-d\TH:i', // Matches datetime-local input format
                Rule::when(
                    fn($input) => isset($input['data']['events'][$input['eventIndex']]['dates'][0]['status']) && $input['data']['events'][$input['eventIndex']]['dates'][0]['status'] === 'error',
                    'after_or_equal:data.events.*.dates.*.start' // End must be after or equal to start
                ),
            ],

            'data.events.*' => [
                'required',
                'array',
                // Tambahkan aturan kustom di sini
                function ($attribute, $value, $fail) {
                    // $attribute akan menjadi 'data.events.0', 'data.events.1', dst.

                    // 1. Cek apakah ada lokasi yang sudah cocok dari dropdown
                    $hasMatchedLocation = collect(data_get($value, 'location_data'))->contains('match_status', 'matched');

                    // 2. Cek apakah ada lokasi mentah yang diisi
                    $hasRawLocation = !empty(data_get($value, 'location'));

                    // Jika tidak ada keduanya, maka validasi gagal.
                    if (!$hasMatchedLocation && !$hasRawLocation) {
                        $fail("Lokasi acara untuk '{$value['eventName']}' wajib diisi atau dipilih dari daftar.");
                    }
                },
            ],

            // --- Schedule/Rundown Validation ---
            'data.events.*.schedule' => 'nullable|array', // Can be empty if no schedule
            'data.events.*.schedule.*.description' => 'required|string|max:500',
            'data.events.*.schedule.*.startTime_processed.time' => [
                'nullable',
                'date_format:H:i', // Matches time input format
                Rule::when(
                    fn($input) => isset($input['data']['events'][$input['eventIndex']]['schedule'][$input['scheduleIndex']]['startTime_processed']['status']) && $input['data']['events'][$input['eventIndex']]['schedule'][$input['scheduleIndex']]['startTime_processed']['status'] === 'error',
                    'before_or_equal:data.events.*.schedule.*.endTime_processed.time'
                ),
            ],
            'data.events.*.schedule.*.endTime_processed.time' => [
                'nullable',
                'date_format:H:i',
                Rule::when(
                    fn($input) => isset($input['data']['events'][$input['eventIndex']]['schedule'][$input['scheduleIndex']]['endTime_processed']['status']) && $input['data']['events'][$input['eventIndex']]['schedule'][$input['scheduleIndex']]['endTime_processed']['status'] === 'error',
                    'after_or_equal:data.events.*.schedule.*.startTime_processed.time'
                ),
            ],

            // --- Equipment Validation ---
            'data.events.*.equipment' => 'nullable|array', // Can be empty if no equipment
            'data.events.*.equipment.*.quantity' => 'required|integer|min:1',
            'data.events.*.equipment.*.item_id' => [
                'required', // item_id must be present if item is used
                'exists:assets,id', // Ensure the selected asset ID exists
                // Conditional: if original match_status was not 'matched', then item_id must be selected.
                // This is implicitly handled by 'required' on `item_id`.
            ],
        ];
    }

    // You can customize error messages
    protected function messages()
    {
        return [
            'data.document_information.document_date.date.required' => 'Tanggal Dokumen wajib diisi.',
            'data.document_information.document_date.date.date' => 'Format Tanggal Dokumen tidak valid.',
            'data.document_information.final_organization_id.required' => 'Organisasi Penerbit wajib dipilih.',
            'data.document_information.final_organization_id.exists' => 'Organisasi Penerbit tidak valid.',

            'data.events.required' => 'Detail Acara tidak boleh kosong.',
            'data.events.*.eventName.required' => 'Nama Acara wajib diisi.',
            'data.events.*.dates.*.start.required' => 'Tanggal mulai kegiatan wajib diisi.',
            'data.events.*.dates.*.end.required' => 'Tanggal selesai kegiatan wajib diisi.',
            'data.events.*.dates.*.start.date_format' => 'Format tanggal mulai kegiatan tidak valid.',
            'data.events.*.dates.*.end.date_format' => 'Format tanggal selesai kegiatan tidak valid.',
            'data.events.*.dates.*.start.before_or_equal' => 'Tanggal mulai harus sebelum atau sama dengan tanggal selesai.',
            'data.events.*.dates.*.end.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',

            'data.events.*.location_data.*.location_id.required' => 'Lokasi acara wajib dipilih.',
            'data.events.*.location_data.*.location_id.exists' => 'Lokasi acara tidak valid.',

            'data.events.*.schedule.*.description.required' => 'Deskripsi jadwal wajib diisi.',
            'data.events.*.schedule.*.startTime_processed.time.required' => 'Waktu mulai jadwal wajib diisi.',
            'data.events.*.schedule.*.startTime_processed.time.date_format' => 'Format waktu mulai jadwal tidak valid (HH:MM).',
            'data.events.*.schedule.*.startTime_processed.time.before_or_equal' => 'Waktu mulai jadwal harus sebelum atau sama dengan waktu selesai.',
            'data.events.*.schedule.*.endTime_processed.time.required' => 'Waktu selesai jadwal wajib diisi.',
            'data.events.*.schedule.*.endTime_processed.time.date_format' => 'Format waktu selesai jadwal tidak valid (HH:MM).',
            'data.events.*.schedule.*.endTime_processed.time.after_or_equal' => 'Waktu selesai jadwal harus setelah atau sama dengan waktu mulai.',

            'data.events.*.equipment.*.quantity.required' => 'Jumlah peralatan wajib diisi.',
            'data.events.*.equipment.*.quantity.integer' => 'Jumlah peralatan harus berupa angka.',
            'data.events.*.equipment.*.quantity.min' => 'Jumlah peralatan minimal 1.',
            'data.events.*.equipment.*.item_id.required' => 'Peralatan wajib dipilih.',
            'data.events.*.equipment.*.item_id.exists' => 'Peralatan tidak valid.',
        ];
    }

    #[On('setDataDocument')]
    public function setData(array $data)
    {
        // 1. Simpan data yang diterima ke properti komponen
        $this->reset();
        $this->data = collect($data)->toRecursive();

        foreach ($this->data->get('events', []) as $index => $event) {
            $this->editingLocationIndex[$index] = null;
        }

        // 2. Lakukan pengecekan atau persiapan data di sini
        $this->allOrganizations = ModelsOrganization::orderBy('name')->get();
        $this->allLocations = ModelsLocation::orderBy('name')->get();
    }

    public function editLocation($eventIndex, $locIndex)
    {
        $this->editingLocationIndex[$eventIndex] = $locIndex;
    }

    public function cancelEditLocation($eventIndex)
    {
        $this->editingLocationIndex[$eventIndex] = null;
    }

    public function removeLocation($eventIndex, $locIndex)
    {
        $locations = data_get($this->data, "events.{$eventIndex}.location_data", collect());
        if ($locations instanceof Collection && $locations->has($locIndex)) {
            $locations->splice($locIndex, 1);
            data_set($this->data, "events.{$eventIndex}.location_data", $locations);
        }
    }

    public function mount()
    {
        $this->data = collect();
    }

    public function addLocation($eventIndex)
    {
        $newLocation = [
            'original_name' => 'Lokasi Baru',
            'name' => '',
            'location_id' => null,
            'match_status' => 'unmatched',
            'similarity_score' => 0
        ];

        $locations = data_get($this->data, "events.{$eventIndex}.location_data", collect());
        $locations->push($newLocation);
        data_set($this->data, "events.{$eventIndex}.location_data", $locations);

        // Langsung aktifkan mode edit untuk item yang baru ditambahkan
        $this->editingLocationIndex[$eventIndex] = $locations->count() - 1;
    }

    public function updateLocation($eventIndex, $locIndex, $locationId)
    {
        if (empty($locationId)) return;

        $location = ModelsLocation::find($locationId);
        if (!$location) return;

        $events = $this->data->get('events');
        if (!isset($events[$eventIndex])) return;

        $locationData = $events[$eventIndex]['location_data']->get($locIndex);
        if (!$locationData) return;

        $locationData['name'] = $location->name;
        $locationData['location_id'] = $location->id;
        $locationData['match_status'] = 'matched';

        $events[$eventIndex]['location_data']->put($locIndex, $locationData);

        $this->data->put('events', $events);
        // Matikan mode edit setelah pemilihan selesai
        $this->editingLocationIndex[$eventIndex] = null;
    }

    public function removeDateRange($eventIndex, $dateIndex)
    {
        if ($this->data['events'][$eventIndex]['dates']->count() > 1) {
            $this->data['events'][$eventIndex]['dates']->splice($dateIndex, 1);
        }
    }

    public function addDateRange($eventIndex)
    {
        $this->data['events'][$eventIndex]['dates']->push([
            'start' => null,
            'end' => null,
        ]);
    }

    #[Computed]
    public function availableAssets($eventIndex)
    {
        // Asumsi kita mengambil tanggal dari event pertama untuk menentukan ketersediaan
        $event = $this->data['events'][$eventIndex] ?? null;
        if (!$event || empty($event['dates'][0]['start'])) {
            return collect();
        }

        $startDate = $event['dates'][0]['start'];
        $endDate = $event['dates'][0]['end'];

        // Ini adalah query Anda sebelumnya, sekarang menjadi computed property
        $assets = Asset::query()
            ->where('is_active', true)
            ->withBorrowedQuantityBetween(startTime: $startDate, endTime: $endDate)
            ->get();

        return $assets->map(function ($asset) {
            $asset->borrowed_quantity = $asset->borrowings->sum(function ($borrowing) {
                return $borrowing->pivot->quantity ?? 0;
            });
            $asset->available_stock = $asset->quantity - $asset->borrowed_quantity;
            return $asset;
        });
    }

    public function getSelectedAssetIds($eventIndex)
    {
        // Ambil koleksi 'equipment' dari event yang sesuai
        $equipmentCollection = $this->data['events'][$eventIndex]['equipment'];

        return $equipmentCollection
            // Ambil hanya yang sudah ditautkan
            ->where('match_status', 'matched')
            // Ambil ID-nya saja
            ->pluck('item_id')
            // Hapus nilai null atau kosong
            ->filter()
            // Kembalikan sebagai array biasa
            ->toArray();
    }

    public function removeItem($collectionKey, $eventIndex = null, $itemIndex = null)
    {
        $path = match ($collectionKey) {
            'equipment' => "events.{$eventIndex}.equipment",
            // ... path lain jika diperlukan
            default => null,
        };

        if (!$path) return;

        $collection = data_get($this->data, $path);
        if ($collection instanceof Collection && isset($collection[$itemIndex])) {
            $collection->splice($itemIndex, 1);
            data_set($this->data, $path, $collection);
            toastr()->success(ucfirst($collectionKey) . ' telah dihapus dari daftar.');
        }
    }

    public function linkItem($collectionKey, $masterId, $eventIndex = null, $itemIndex = null)
    {
        if (empty($masterId)) return;

        // Tentukan model berdasarkan collectionKey
        $modelClass = match ($collectionKey) {
            'equipment' => Asset::class,
            'emitter_organizations' => ModelsOrganization::class, // Ganti dengan namespace model Anda
            'location_data' => ModelsLocation::class,           // Ganti dengan namespace model Anda
            default => null,
        };

        if (!$modelClass) return;

        $masterItem = $modelClass::find($masterId);
        if (!$masterItem) return;

        // Tentukan path data yang akan diupdate
        $path = match ($collectionKey) {
            'equipment' => "events.{$eventIndex}.equipment.{$itemIndex}",
            'emitter_organizations' => "document_information.emitter_organizations.{$itemIndex}",
            'location_data' => "events.{$eventIndex}.location_data.{$itemIndex}",
            default => null,
        };

        if (!$path) return;

        // Update data menggunakan data_set() untuk path dinamis
        data_set($this->data, "{$path}.match_status", 'matched');
        data_set($this->data, "{$path}.item_id", $masterItem->id); // Sesuaikan key jika perlu
        data_set($this->data, "{$path}.item", $masterItem->name);   // Sesuaikan key jika perlu

        toastr()->success(ucfirst($collectionKey) . ' berhasil ditautkan.');
    }

    public function saveCorrections()
    {
        try {
            // Pindahkan logika ke dalam blok try-catch agar semua error tertangkap
            $finalOrgId = data_get($this->data, 'document_information.final_organization_id');

            if (!$finalOrgId) {
                // Gunakan data_get untuk mengakses data dengan aman, mencegah error "Undefined array key"
                $organizations = data_get($this->data, 'document_information.emitter_organizations', collect());

                // Pastikan $organizations adalah collection sebelum memanggil method filter
                if ($organizations instanceof Collection) {
                    $matchedOrgs = $organizations
                        ->filter(fn($value) => data_get($value, 'match_status') === 'matched')
                        ->values(); // Keep it as a collection

                    if ($matchedOrgs->count() === 1) {
                        // Gunakan data_get lagi untuk keamanan
                        $orgId = data_get($matchedOrgs->first(), 'nama_organisasi_id');
                        if ($orgId) {
                            // Gunakan data_set untuk menetapkan nilai dengan aman
                            data_set($this->data, 'document_information.final_organization_id', $orgId);
                        }
                    }
                }
            }

            // Lakukan validasi setelah data dimanipulasi
            $this->validate();

            $this->dispatch('setDataForEvent', data: $this->data->toArray())->to(EventModal::class);

            // Pindahkan event open-modal setelah event setData untuk memastikan data sudah siap
            $this->dispatch('open-modal', 'event-modal');
            $this->dispatch('close-modal', 'document-data-modal');
        } catch (ValidationException $e) {
            // Tangani error validasi seperti biasa
            toastr()->error('Ada kesalahan dalam data yang dimasukkan. Silakan periksa kembali.');
            \Illuminate\Support\Facades\Log::error('Validation error in DataDocumentModal', [
                'errors' => $e->validator->errors()->all(),
                'data' => $this->data->toArray(),
            ]);
        } catch (\Throwable $th) {
            // TANGKAP SEMUA ERROR LAINNYA: Ini akan mencegah refresh halaman
            toastr()->error('Terjadi kesalahan tak terduga pada sistem.');
            \Illuminate\Support\Facades\Log::error('Unexpected error in saveCorrections', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'data' => $this->data->toArray(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.pages.document.data-document-modal');
    }
}
