<?php

namespace App\Livewire\Admin\Pages\Document;

use App\Models\Asset;
use App\Models\Document as ModelsDocument;
use App\Models\EventCategory;
use App\Models\Location as ModelsLocation;
use App\Models\Organization as ModelsOrganization;
use Atomescrochus\StringSimilarities\Compare;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class DocumentModal extends Component
{

    public ?ModelsDocument $doc = null;

    // State Management Properties
    public ?array $analysisResult = null;
    public string $processingStatus = '';
    public bool $isProcessing = false;
    public bool $isEditing = false;
    public array $editableKegiatan = [];
    public array $editableData = [];

    public bool $isSave = false;
    public bool $isWrongDataAsset = false;
    public bool $isWrongDataOrganization = false;
    public bool $isWrongDataLocation = false;

    public $masterAssets;
    public $masterOrganizations;
    public $masterLocations;

    public function rules()
    {
        return [
            // Validasi data umum yang diedit
            'editableData.doc_date' => 'required|date_format:Y-m-d',
            'analysisResult.data.informasi_umum_dokumen.perihal_surat' => 'required|string|max:255',
            'analysisResult.data.informasi_umum_dokumen.nomor_surat' => 'required|string',
            'analysisResult.data.type' => 'required|string',
            'analysisResult.data.informasi_umum_dokumen.penerima_surat' => 'required|array|min:1',
            'analysisResult.data.informasi_umum_dokumen.penerima_surat.*.name' => 'required|string',
            'analysisResult.data.informasi_umum_dokumen.penerima_surat.*.position' => 'nullable|string',

            // Validasi untuk setiap kegiatan yang diedit
            'editableKegiatan.*.nama_kegiatan_utama' => 'required|string',
            'editableKegiatan.*.lokasi_kegiatan' => 'required|string',
            'editableKegiatan.*.dates.*.start' => 'required|date_format:Y-m-d\TH:i',
            'editableKegiatan.*.dates.*.end' => 'required|date_format:Y-m-d\TH:i|after_or_equal:editableKegiatan.*.dates.*.start',

            // Validasi untuk item yang dipinjam (data dinamis di analysisResult)
            'analysisResult.data.detail_kegiatan.*.barang_dipinjam' => 'nullable|array',
            'analysisResult.data.detail_kegiatan.*.barang_dipinjam.*.item' => 'required|string',
            'analysisResult.data.detail_kegiatan.*.barang_dipinjam.*.jumlah' => 'required|integer|min:1',

            // Validasi untuk penanda tangan (data dinamis di analysisResult)
            'analysisResult.data.blok_penanda_tangan' => 'required|array|min:1',
            'analysisResult.data.blok_penanda_tangan.*.nama' => 'required|string',
            'analysisResult.data.blok_penanda_tangan.*.jabatan' => 'required|string',
        ];
    }

    #[On('setDataForDetailDocument')]
    public function viewDetails($documentId)
    {
        $this->authorize('access', 'admin.documents.show');

        $this->doc = null;
        $this->analysisResult = null;
        $this->processingStatus = '';
        $this->isProcessing = false;
        $this->isEditing = false;
        $this->editableKegiatan = [];
        $this->editableData = [];
        $this->isSave = false;
        $this->isWrongDataAsset = false;

        $this->doc = ModelsDocument::with(['submitter'])
            ->where('id', $documentId)
            ->first();

        if ($this->doc->status === 'processed' && !empty($this->doc->analysis_result)) {
            $this->analysisResult = json_decode($this->doc->analysis_result, true);
            if (!isset($this->analysisResult['data']['id'])) {
                $this->analysisResult['data']['id'] = $this->doc->id;
            }
            $this->processingStatus = 'Analisis telah selesai sebelumnya.';
        }

        // dd($this->analysisResult);
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', 'document-modal');
        $this->dispatch('reset-data');
    }

    #[On('reset-data')]
    public function resetData()
    {
        $this->doc = null;
        $this->analysisResult = null;
        $this->processingStatus = '';
        $this->isProcessing = false;
        $this->isEditing = false;
        $this->editableKegiatan = [];
        $this->editableData = [];
        $this->isSave = false;
        $this->isWrongDataAsset = false;
    }

    #[On('start-processing')]
    public function processDocument()
    {
        // Pastikan dokumen ada
        if (!$this->doc) {
            $this->processingStatus = 'Error: Dokumen tidak ditemukan.';
            return;
        }

        // Reset state sebelumnya
        $this->isProcessing = true;
        $this->processingStatus = 'Membaca dokumen, silakan tunggu...';
        $this->analysisResult = null; // Reset hasil sebelumnya

        $this->dispatch('analysis');
    }

    #[On('analysis')]
    public function analysis()
    {
        try {
            $absolutePath = Storage::disk('public')->path($this->doc->document_path);

            if (!file_exists($absolutePath)) {
                throw new Exception('File fisik tidak ditemukan di server.');
            }

            $response = Http::acceptJson()
                ->timeout(120)
                ->attach('file', file_get_contents($absolutePath), $this->doc->original_file_name)
                ->post(config('services.bert_api.url'))
                ->throw(); // Otomatis throw exception untuk status 4xx/5xx

            $this->analysisResult = $response->json();

            if (is_array($this->analysisResult)) {
                $this->analysisResult['data']['id'] = $this->doc->id;
            } else {
                $this->processingStatus = 'Error: Format respons dari layanan analisis tidak valid.';
                $this->isProcessing = false;
                return;
            }

            $this->doc->update([
                'status' => 'processed',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'analysis_result' => $this->analysisResult,
            ]);

            $this->processingStatus = 'Analisis Selesai.';
        } catch (ConnectionException $e) {
            $this->processingStatus = 'Error: Analisis dokumen gagal karena waktu pemrosesan habis atau koneksi terputus. Mohon coba lagi beberapa saat.';
            Log::error('BERT API Connection Error: ' . $e->getMessage());
        } catch (RequestException $e) {
            $this->processingStatus = 'Error: Layanan analisis dokumen sedang mengalami kendala. Mohon hubungi administrator jika masalah berlanjut.';
            Log::error('BERT API Request Error: ' . $e->response->body(), [
                'status' => $e->response->status(),
                'response' => $e->response->json()
            ]);
        } catch (Exception $e) {
            $this->processingStatus = 'Error: Terjadi kesalahan teknis yang tidak terduga pada sistem. Silakan coba lagi atau hubungi administrator.';
            Log::error('Document Processing Error: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function prepareReanalysis()
    {
        $this->isProcessing = true;
        $this->processingStatus = 'Mempersiapkan untuk analisis ulang...';
        $this->analysisResult = null;

        $this->dispatch('start-processing');
    }

    /**
     * Mengaktifkan mode edit.
     */
    public function editAnalysis()
    {
        $this->isEditing = true;
        // dd(json_encode($this->analysisResult['data']['detail_kegiatan']));

        $this->editableData['doc_date'] = $this->getDate($this->analysisResult['data']['informasi_umum_dokumen']['tanggal_surat_dokumen'] ?? null);

        $this->editableKegiatan = []; // Reset

        foreach ($this->analysisResult['data']['detail_kegiatan'] ?? [] as $index => $kegiatan) {
            $datetime = $this->combineDateTime($kegiatan['tanggal_kegiatan'], $kegiatan['jam_kegiatan']);
            // dd($datetime);
            $this->editableKegiatan[$index] = [
                'nama_kegiatan_utama' => $kegiatan['nama_kegiatan_utama'],
                'lokasi_kegiatan'     => $kegiatan['lokasi_kegiatan'],
                'dates'               => $datetime,
            ];
        }
    }

    /**
     * Membatalkan mode edit dan mengembalikan data ke state semula.
     */
    public function cancelEdit()
    {
        $this->isEditing = false;
        // Muat ulang data original dari properti doc
        $this->analysisResult = $this->doc->analysis_result ? json_decode($this->doc->analysis_result, true) : null;
    }

    /**
     * Menyimpan perubahan dari mode edit ke database.
     */
    public function saveAnalysis()
    {
        if (!$this->isEditing) return;

        // Langkah 1: Validasi data dari form
        $this->validate();

        // Update tanggal surat dokumen
        $this->analysisResult['data']['informasi_umum_dokumen']['tanggal_surat_dokumen'] = $this->setDateFormat($this->editableData['doc_date']);

        // Update setiap detail kegiatan
        foreach ($this->editableKegiatan as $index => $editedKegiatan) {
            // Ambil array 'dates' yang sudah diedit oleh pengguna
            $editedDates = $editedKegiatan['dates'];

            // Panggil helper baru untuk merekonstruksi string tanggal dan jam
            $reconstructedTanggal = $this->reconstructDateString($editedDates);
            $reconstructedJam = $this->reconstructTimeString($editedDates);

            // Update data di analysisResult
            $this->analysisResult['data']['detail_kegiatan'][$index]['nama_kegiatan_utama'] = $editedKegiatan['nama_kegiatan_utama'];
            $this->analysisResult['data']['detail_kegiatan'][$index]['lokasi_kegiatan'] = $editedKegiatan['lokasi_kegiatan'];

            // Simpan kembali string tanggal dan jam yang sudah direkonstruksi
            $this->analysisResult['data']['detail_kegiatan'][$index]['tanggal_kegiatan'] = $reconstructedTanggal;
            $this->analysisResult['data']['detail_kegiatan'][$index]['jam_kegiatan'] = $reconstructedJam;

            // Simpan juga data terstruktur 'dates' hasil editan, ini bisa berguna untuk proses lain
            // (misalnya saat menyimpan ke tabel Event utama)
            $this->analysisResult['data']['detail_kegiatan'][$index]['dates'] = $editedDates;
        }

        // Langkah 3: Simpan data yang sudah final ke database
        $this->doc->update(['analysis_result' => json_encode($this->analysisResult)]);

        $this->isEditing = false;
        // Beri feedback ke user
        toastr()->success('Hasil analisis berhasil diperbarui.');
    }

    public function addDate($kegiatanIndex)
    {
        if (!$this->isEditing) return;

        // Tambahkan pasangan start/end kosong ke array 'dates'
        $this->editableKegiatan[$kegiatanIndex]['dates'][] = [
            'start' => '',
            'end' => ''
        ];
    }

    public function removeDate($kegiatanIndex, $dateIndex)
    {
        if (!$this->isEditing) return;

        if (isset($this->editableKegiatan[$kegiatanIndex]['dates'][$dateIndex])) {
            unset($this->editableKegiatan[$kegiatanIndex]['dates'][$dateIndex]);
            // Re-index array untuk menjaga konsistensi
            $this->editableKegiatan[$kegiatanIndex]['dates'] = array_values($this->editableKegiatan[$kegiatanIndex]['dates']);
        }
    }

    /**
     * [BARU] Menambahkan item barang kosong ke daftar peminjaman.
     *
     * @param int $kegiatanIndex Index dari kegiatan yang akan ditambahkan item.
     */
    public function addItem($kegiatanIndex)
    {
        if (!$this->isEditing) return;

        // Pastikan 'barang_dipinjam' ada dan merupakan array
        if (!isset($this->analysisResult['data']['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam']) || !is_array($this->analysisResult['data']['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'])) {
            $this->analysisResult['data']['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'] = [];
        }

        $this->analysisResult['data']['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'][] = [
            'item' => 'Barang Baru',
            'jumlah' => 1,
        ];
    }

    /**
     * [BARU] Menghapus item barang dari daftar peminjaman.
     *
     * @param int $kegiatanIndex Index dari kegiatan.
     * @param int $itemIndex Index dari item yang akan dihapus.
     */
    public function removeItem($kegiatanIndex, $itemIndex)
    {
        if (!$this->isEditing) return;

        if (isset($this->analysisResult['data']['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'][$itemIndex])) {
            unset($this->analysisResult['data']['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'][$itemIndex]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->analysisResult['data']['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'] = array_values($this->analysisResult['data']['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam']);
        }
    }

    public function removeKegiatan($index)
    {
        if (!$this->isEditing) return;

        // Hapus dari data utama yang akan disimpan
        if (isset($this->analysisResult['data']['detail_kegiatan'][$index])) {
            unset($this->analysisResult['data']['detail_kegiatan'][$index]);
            $this->analysisResult['data']['detail_kegiatan'] = array_values($this->analysisResult['data']['detail_kegiatan']);
        }

        // Hapus juga dari array yang digunakan untuk binding form edit
        if (isset($this->editableKegiatan[$index])) {
            unset($this->editableKegiatan[$index]);
            $this->editableKegiatan = array_values($this->editableKegiatan);
        }
    }

    public function removeRecipient($index)
    {
        if (!$this->isEditing) return;

        // Pastikan 'penerima' ada dan merupakan array
        if (!isset($this->analysisResult['data']['informasi_umum_dokumen']['penerima_surat'][$index]) || !is_array($this->analysisResult['data']['informasi_umum_dokumen']['penerima_surat'])) {
            // Jika tidak ada penerima, inisialisasi sebagai array kosong
            $this->analysisResult['data']['informasi_umum_dokumen']['penerima_surat'] = [];
        }

        // Hapus penerima berdasarkan index
        if (isset($this->analysisResult['data']['informasi_umum_dokumen']['penerima_surat'][$index])) {
            unset($this->analysisResult['data']['informasi_umum_dokumen']['penerima_surat'][$index]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->analysisResult['data']['informasi_umum_dokumen']['penerima_surat'] = array_values($this->analysisResult['data']['informasi_umum_dokumen']['penerima_surat']);
        }
    }

    public function addOrganization()
    {
        if (!$this->isEditing) return;

        if (!isset($this->analysisResult['data']['informasi_umum_dokumen']['organisasi']) || !is_array($this->analysisResult['data']['informasi_umum_dokumen']['organisasi'])) {
            $this->analysisResult['data']['informasi_umum_dokumen']['organisasi'] = [];
        }

        // Tambahkan penerima baru
        $this->analysisResult['data']['informasi_umum_dokumen']['organisasi'][] = [
            'nama' => '',
        ];
    }

    public function removeOrganization($index)
    {
        if (!$this->isEditing) return;

        // Pastikan 'penerima' ada dan merupakan array
        if (!isset($this->analysisResult['data']['informasi_umum_dokumen']['organisasi'][$index]) || !is_array($this->analysisResult['data']['informasi_umum_dokumen']['organisasi'])) {
            // Jika tidak ada penerima, inisialisasi sebagai array kosong
            $this->analysisResult['data']['informasi_umum_dokumen']['organisasi'] = [];
        }

        // Hapus penerima berdasarkan index
        if (isset($this->analysisResult['data']['informasi_umum_dokumen']['organisasi'][$index])) {
            unset($this->analysisResult['data']['informasi_umum_dokumen']['organisasi'][$index]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->analysisResult['data']['informasi_umum_dokumen']['organisasi'] = array_values($this->analysisResult['data']['informasi_umum_dokumen']['organisasi']);
        }
    }

    public function addRecipient()
    {
        if (!$this->isEditing) return;

        // Pastikan 'penerima_surat' ada dan merupakan array
        if (!isset($this->analysisResult['data']['informasi_umum_dokumen']['penerima_surat']) || !is_array($this->analysisResult['data']['informasi_umum_dokumen']['penerima_surat'])) {
            $this->analysisResult['data']['informasi_umum_dokumen']['penerima_surat'] = [];
        }

        // Tambahkan penerima baru
        $this->analysisResult['data']['informasi_umum_dokumen']['penerima_surat'][] = [
            'name' => '',
            'position' => '',
        ];
    }

    public function removeSigner($index)
    {
        if (!$this->isEditing) return;

        if (isset($this->analysisResult['data']['blok_penanda_tangan'][$index])) {
            unset($this->analysisResult['data']['blok_penanda_tangan'][$index]);
            $this->analysisResult['data']['blok_penanda_tangan'] = array_values($this->analysisResult['data']['blok_penanda_tangan']);
        }
    }

    public function addSigner()
    {
        if (!$this->isEditing) return;

        if (!isset($this->analysisResult['data']['blok_penanda_tangan']) || !is_array($this->analysisResult['data']['blok_penanda_tangan'])) {
            $this->analysisResult['data']['blok_penanda_tangan'] = [];
        }

        $this->analysisResult['data']['blok_penanda_tangan'][] = [
            'nama' => '',
            'jabatan' => '',
        ];
    }

    public function save()
    {
        // Pastikan ada data untuk diproses
        if (empty($this->analysisResult['data'])) {
            // Handle error, mungkin dengan notifikasi
            return;
        }

        $data = $this->analysisResult['data'];
        $type = $data['type'] ?? null;

        if (isset($data['detail_kegiatan'])) {
            $this->categorizeEvents(events: $data['detail_kegiatan']);
            $this->AllEventDateTime(events: $data['detail_kegiatan']);
        }

        $normalizedEventDetails = $this->normalizeAssetNamesFuzzy($data['detail_kegiatan']);
        $normalizedLocationDetails = $this->normalizeLocationNamesFuzzy($normalizedEventDetails);
        $normalizedOrganizations = $this->normalizeOrganizationNamesFuzzy($data['informasi_umum_dokumen']['organisasi']);

        $this->analysisResult['data']['detail_kegiatan'] = $normalizedLocationDetails;
        $this->analysisResult['data']['informasi_umum_dokumen']['organisasi'] = $normalizedOrganizations;

        if ($type === 'peminjaman' && isset($data['detail_kegiatan'])) {
            if ($this->isWrongDataAsset) {
                $this->dispatch('close-modal', 'document-modal');
                $this->dispatch('setDataForBorrowing', borrowingData: $this->analysisResult['data'])->to(Borrowing::class);
                $this->dispatch('open-modal', 'borrowing-modal');
            } else {
                $this->dispatch('close-modal', 'document-modal');
                $this->dispatch('setDataForEvent', data: $this->analysisResult['data'])->to(EventModal::class);
                $this->dispatch('open-modal', 'event-modal');
            }
        } else if ($type === 'perizinan') {
            if ($this->isWrongDataAsset) {
                $this->dispatch('close-modal', 'document-modal');
                $this->dispatch('setDataForBorrowing', borrowingData: $this->analysisResult['data'])->to(Borrowing::class);
                $this->dispatch('open-modal', 'borrowing-modal');
            } elseif ($this->isWrongDataOrganization) {
                $this->dispatch('close-modal', 'document-modal');
                $this->dispatch('setDataForOrganization', data: $this->analysisResult['data'])->to(Organization::class);
                $this->dispatch('open-modal', 'organization-modal');
            } elseif ($this->isWrongDataLocation) {
                $this->dispatch('close-modal', 'document-modal');
                $this->dispatch('setDataForLocation', data: $this->analysisResult['data'])->to(Location::class);
                $this->dispatch('open-modal', 'location-modal');
            } else {
                $this->dispatch('close-modal', 'document-modal');
                $this->dispatch('setDataForEvent', data: $this->analysisResult['data'])->to(EventModal::class);
                $this->dispatch('open-modal', 'event-modal');
            }
        } else if ($type === 'undangan') {
            $this->dispatch('close-modal', 'document-modal');
            $this->dispatch('setDataForEvent', data: $this->analysisResult['data'])->to(EventModal::class);
            $this->dispatch('open-modal', 'event-modal');
        }
    }

    public function storePerizinan()
    {
        $form = new \App\Livewire\Forms\EventForm();
    }

    public function render()
    {
        return view('livewire.admin.pages.document.document-modal');
    }

    private function normalizeOrganizationNamesFuzzy(array $orgs)
    {
        $comparison = new Compare();
        $this->masterOrganizations = ModelsOrganization::active()->get(['id', 'name']);
        $similarityThreshold = 0.8;
        foreach ($orgs as &$org) {
            $org['original_name'] = $org['nama'];
            $extractedName = strtolower(trim($org['nama']));
            $bestMatch = null;
            $highestScore = 0;

            foreach ($this->masterOrganizations as $masterOrg) {
                $masterOrgName = strtolower(trim($masterOrg->name));
                $score = $comparison->jaroWinkler($extractedName, $masterOrgName);

                if ($score > $highestScore) {
                    $highestScore = $score;
                    $bestMatch = $masterOrg;
                }
            }

            if ($bestMatch && $highestScore >= $similarityThreshold) {
                $org['nama_organisasi'] = $bestMatch->name;
                $org['nama_organisasi_id'] = $bestMatch->id;
                $org['match_status'] = 'matched';
                $org['similarity_score'] = round($highestScore, 2);
            } else {
                $org['nama_organisasi_id'] = null;
                $org['match_status'] = 'unmatched';
                $org['similarity_score'] = round($highestScore, 2);
                $this->isWrongDataOrganization = true;
            }
        }
        unset($org);

        return $orgs;
    }

    private function normalizeAssetNamesFuzzy(array $events)
    { // Output: Semua status masih 'inactive'
        $comparison = new Compare();
        $this->masterAssets = Asset::active()->get(['id', 'name']);
        $similarityThreshold = 0.8;


        foreach ($events as &$event) {
            foreach ($event['barang_dipinjam'] as &$asset) {
                // Pastikan format aset benar
                if (!isset($asset['item'])) {
                    continue;
                }

                $asset['original_name'] = $asset['item'];
                $extractedName = strtolower(trim($asset['item']));
                $bestMatch = null;
                $highestScore = 0;

                foreach ($this->masterAssets as $masterAsset) {
                    $masterAssetName = strtolower(trim($masterAsset->name));
                    $score = $comparison->jaroWinkler($extractedName, $masterAssetName);

                    if ($score > $highestScore) {
                        $highestScore = $score;
                        $bestMatch = $masterAsset;
                    }
                }

                if ($bestMatch && $highestScore >= $similarityThreshold) {
                    $asset['item'] = $bestMatch->name;
                    $asset['item_id'] = $bestMatch->id;
                    $asset['match_status'] = 'matched';
                    $asset['similarity_score'] = round($highestScore, 2);
                } else {
                    $asset['item_id'] = null;
                    $asset['match_status'] = 'unmatched';
                    $asset['similarity_score'] = round($highestScore, 2);
                    $this->isWrongDataAsset = true;
                }
            }
            unset($asset);
        }
        unset($event);

        return $events;
    }
    private function normalizeLocationNamesFuzzy(array $events)
    {
        $comparison = new Compare();
        $this->masterLocations = ModelsLocation::active()->get(['id', 'name']);
        $similarityThreshold = 0.7;
        // dd($events);

        foreach ($events as $index => &$event) {
            // Pastikan format aset benar
            if (!isset($event['lokasi_kegiatan'])) {
                continue;
            }

            $finalLocations = [];

            // 1. Jalankan ekspansi angka terlebih dahulu
            $potentiallyExpanded = $this->expandNumericRange($event['lokasi_kegiatan']);

            // 2. Untuk setiap hasilnya, jalankan pemisahan berdasarkan kata kunci 'dan', '&', ','
            foreach ($potentiallyExpanded as $locationPart) {
                $reconstructed = $this->splitAndReconstructLocations($locationPart);
                $finalLocations = array_merge($finalLocations, $reconstructed);
            }

            // Gunakan array_unique untuk memastikan tidak ada duplikasi
            $locations = array_unique($finalLocations);

            $event['location_data'] = [];

            foreach ($locations as $location) {
                $singleLocationData = [
                    'original_name' => $location
                ];

                $extractedName = strtolower(trim($location));
                $bestMatch = null;
                $highestScore = 0;

                foreach ($this->masterLocations as $masterLocation) {
                    $masterLocationName = strtolower(trim($masterLocation->name));
                    $score = $comparison->jaroWinkler($extractedName, $masterLocationName);

                    if ($score > $highestScore) {
                        $highestScore = $score;
                        $bestMatch = $masterLocation;
                    }
                }

                if ($bestMatch && $highestScore >= $similarityThreshold) {
                    $singleLocationData['name'] = $bestMatch->name;
                    $singleLocationData['location_id'] = $bestMatch->id;
                    $singleLocationData['match_status'] = 'matched';
                    $singleLocationData['similarity_score'] = round($highestScore, 2);
                } else {
                    $singleLocationData['location_id'] = null;
                    $singleLocationData['match_status'] = 'unmatched';
                    $singleLocationData['similarity_score'] = round($highestScore, 2);
                    $this->isWrongDataLocation = true;
                }
                $event['location_data'][] = $singleLocationData;
            }
        }
        unset($event);

        return $events;
    }

    private function getDate($date)
    {
        if (empty($date)) return null;

        try {
            Carbon::setLocale('id_ID');
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
            $date = str_ireplace(array_keys($indonesianMonths), array_values($indonesianMonths), $date);
            return Carbon::parse($date)->format('Y-m-d');
        } catch (Exception $e) {
            Log::error("Gagal mem-parsing tanggal '{$date}'. Error: " . $e->getMessage());
            return 'Format tidak valid';
        }
    }

    private function setDateFormat($date)
    {
        if (empty($date)) return null;

        try {
            Carbon::setLocale('id_ID');
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
            $date = str_ireplace(array_keys($indonesianMonths), array_values($indonesianMonths), $date);
            return Carbon::parse($date)->translatedFormat('d F Y');
        } catch (Exception $e) {
            Log::error("Gagal mem-parsing tanggal '{$date}'. Error: " . $e->getMessage());
            return null;
        }
    }

    private function AllEventDateTime(array &$events)
    {
        foreach ($events as &$event) {
            $datetime = $this->combineDateTime($event['tanggal_kegiatan'], $event['jam_kegiatan']);

            $event['dates'] = $datetime;
        }

        unset($event);
    }


    /**
     * [CORE LOGIC] Helper cerdas untuk menggabungkan berbagai format tanggal & waktu.
     * Mampu menangani rentang tanggal dan berbagai format waktu.
     */
    private function combineDateTime($dateStr, $timeStr)
    {
        if (empty($dateStr) || empty($timeStr)) {
            return null;
        }

        try {
            // --- LANGKAH 1: PARSE WAKTU ---
            // Gunakan preg_match_all untuk menemukan semua waktu (HH:MM atau HH.MM)
            preg_match_all('/(\d{1,2})[.:](\d{2})/', $timeStr, $timeMatches, PREG_SET_ORDER);

            $startTime = Carbon::createFromTime(0, 0); // Default start time
            $endTime = Carbon::createFromTime(23, 59, 59); // Default end time

            if (count($timeMatches) >= 1) {
                // Ambil waktu pertama sebagai waktu mulai
                $startHour = intval($timeMatches[0][1]);
                $startMinute = intval($timeMatches[0][2]);
                $startTime->setTime($startHour, $startMinute);

                if (count($timeMatches) >= 2) {
                    // Jika ada waktu kedua, gunakan sebagai waktu selesai
                    $endHour = intval($timeMatches[1][1]);
                    $endMinute = intval($timeMatches[1][2]);
                    $endTime->setTime($endHour, $endMinute);
                } else {
                    // Jika hanya ada satu waktu, waktu selesai adalah waktu mulai + 3 jam
                    $endTime = $startTime->copy()->addHours(3);
                }
            }

            // --- LANGKAH 2: PARSE TANGGAL (LOGIKA UTAMA) ---
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


            $parsedDates = [];

            // A. Ekstrak Konteks: Bulan dan Tahun. Ambil yang terakhir jika ada beberapa.
            $month = null;
            $year = null;
            if (preg_match('/([a-zA-Z]+)\s+(\d{4})/', $dateStrEnglish, $monthYearMatches)) {
                $month = $monthYearMatches[1];
                $year = $monthYearMatches[2];
            }

            // B. Ekstrak Aktor: Semua angka yang merupakan hari.
            preg_match_all('/\b(\d{1,2})\b/', $dateStrEnglish, $dayMatches);

            // C. Gabungkan Kembali
            if ($month && $year && count($dayMatches[1]) > 0) {
                foreach ($dayMatches[1] as $day) {
                    $fullDateStr = "$day $month $year";
                    try {
                        $parsedDates[] = Carbon::parse($fullDateStr);
                    } catch (Exception $e) {
                        // Abaikan jika ada angka yang bukan tanggal, misal nomor urut 'D7'
                        continue;
                    }
                }
            } else {
                // Fallback jika formatnya sangat sederhana atau tidak terduga
                $cleanedPart = preg_replace('/^\w+,\s*/', '', trim($dateStrEnglish));
                $parsedDates[] = Carbon::parse($cleanedPart);
            }

            // --- LANGKAH 3: GABUNGKAN SETIAP TANGGAL DENGAN WAKTU ---
            $result = [];
            foreach ($parsedDates as $date) {
                $startDateTime = $date->copy()->setTimeFrom($startTime);
                $endDateTime = $date->copy()->setTimeFrom($endTime);

                // Handle kasus jika waktu selesai melewati tengah malam (misal: 22:00-01:00)
                if ($endDateTime->lt($startDateTime)) {
                    $endDateTime->addDay();
                }

                $result[] = [
                    'start' => $startDateTime->format('Y-m-d\TH:i'),
                    'end'   => $endDateTime->format('Y-m-d\TH:i'),
                ];
            }

            return $result;
        } catch (Exception $e) {
            Log::error("Gagal mem-parsing datetime untuk '{$dateStr}' & '{$timeStr}'. Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper untuk memecah nilai datetime-local kembali ke format string semula.
     */
    private function splitDateTime($datetimeLocalStr)
    {
        if (empty($datetimeLocalStr)) return ['-', '-'];

        try {
            Carbon::setLocale('id_ID');
            $date = Carbon::parse($datetimeLocalStr);

            $newDateStr = $date->translatedFormat('l, d F Y');
            // Catatan: Informasi rentang waktu/tanggal asli akan hilang saat disimpan ulang.
            // Sistem akan menyimpan waktu dan tanggal mulai yang baru.
            $newTimeStr = $date->translatedFormat('H.i') . ' WIB';

            return [$newDateStr, $newTimeStr];
        } catch (Exception $e) {
            return ['Format tidak valid', 'Format tidak valid'];
        }
    }

    /**
     * Memecah string lokasi yang mengandung kata hubung dan merekonstruksi bagian yang terpotong.
     * Contoh: "Aula A dan B" -> ["Aula A", "Aula B"]
     *
     * @param string $locationString String lokasi yang akan diproses.
     * @return array Array berisi nama lokasi yang sudah lengkap.
     */
    private function splitAndReconstructLocations(string $locationString): array
    {
        // 1. Gunakan regex untuk memecah berdasarkan 'dan', '&', atau ',' dengan spasi yang fleksibel
        // \b memastikan 'dan' adalah kata utuh, bukan bagian dari kata lain (misal: "landasan")
        $parts = preg_split('/\\s*(\bdan\b|&|,)\\s*/i', $locationString);

        // Jika tidak ada pemisahan atau string kosong, kembalikan apa adanya dalam array
        if (count($parts) <= 1) {
            return [$locationString];
        }

        $reconstructed = [];
        $base = trim($parts[0]); // Bagian pertama selalu dianggap lengkap
        $reconstructed[] = $base;

        // Cari posisi spasi terakhir di bagian pertama untuk menemukan "prefix"
        $lastSpacePos = strrpos($base, ' ');
        $prefix = ($lastSpacePos !== false) ? substr($base, 0, $lastSpacePos + 1) : '';

        // 2. Lakukan loop untuk merekonstruksi bagian selanjutnya
        for ($i = 1; $i < count($parts); $i++) {
            $currentPart = trim($parts[$i]);

            // Jika bagian saat ini sudah mengandung prefix (misal: "Ruang A dan Ruang B"),
            // maka tidak perlu direkonstruksi.
            if (str_starts_with(strtolower($currentPart), strtolower(trim($prefix)))) {
                $reconstructed[] = $currentPart;
            } else {
                // Jika tidak, gabungkan prefix dengan bagian saat ini
                $reconstructed[] = $prefix . $currentPart;
            }
        }

        return $reconstructed;
    }

    /**
     * Mengenali dan memecah string dengan pola "prefix" diikuti urutan angka.
     * Contoh: "Ruang Rapat lantai 1 2 3" -> ["Ruang Rapat lantai 1", "Ruang Rapat lantai 2", "Ruang Rapat lantai 3"]
     *
     * @param string $locationString
     * @return array
     */
    private function expandNumericRange(string $locationString): array
    {
        // Regex untuk menangkap: (grup 1: teks prefix) diikuti oleh (grup 2: satu atau lebih angka di akhir string)
        $pattern = '/^(.*?\D)\s*((?:\d+\s*)+)$/';

        if (preg_match($pattern, $locationString, $matches)) {
            // $matches[1] akan berisi prefix, contoh: "kapel santo yohanes rasul lantai "
            $prefix = trim($matches[1]);

            // $matches[2] akan berisi semua angka, contoh: "1 2 "
            $numberSequence = trim($matches[2]);

            // Pecah angka-angka tersebut menjadi array
            $numbers = preg_split('/\\s+/', $numberSequence);

            $expandedLocations = [];
            foreach ($numbers as $number) {
                if (!empty($number)) {
                    $expandedLocations[] = $prefix . ' ' . $number;
                }
            }
            return $expandedLocations;
        }

        // Jika pola tidak cocok, kembalikan string asli dalam sebuah array
        return [$locationString];
    }

    /**
     * [BARU] Menambahkan kategori Pancatugas ke setiap kegiatan yang diekstrak.
     *
     * @param array &$events Referensi ke array detail_kegiatan.
     * @return void
     */
    private function categorizeEvents(array &$events)
    {
        // Definisikan keyword untuk setiap kategori dalam array asosiatif PHP
        $pancatugasKeywords = [
            'Liturgia (Peribadatan)' => ['misa', 'koor', 'paduan suara', 'adorasi', 'sakramen', 'rosario', 'ibadat', 'paskah', 'natal', 'kasula', 'perlengkapan misa'],
            'Kerygma (Pewartaan)' => ['katekese', 'pendalaman iman', 'alkitab', 'seminar', 'rekoleksi', 'retret', 'pewartaan', 'firman', 'pelajaran', 'injil'],
            'Koinonia (Persekutuan)' => ['rapat', 'gathering', 'makrab', 'arisan', 'ramah tamah', 'outbond', 'olahraga', 'persekutuan', 'pertemuan', 'camping'],
            'Diakonia (Pelayanan)' => ['diakonia', 'pelayanan', 'bakti sosial', 'baksos', 'panti asuhan', 'donor darah', 'aksi sosial', 'kunjungan sakit'],
            'Martyria (Kesaksian)' => ['martyria', 'kesaksian', 'kewirausahaan', 'pameran karya', 'sharing iman', 'usaha dana']
        ];

        // Loop untuk setiap kegiatan menggunakan referensi (&) agar bisa diubah langsung
        foreach ($events as &$event) {
            $category = 'Koinonia (Persekutuan)'; // Kategori default
            $categoryId = EventCategory::where('name', $category)->pluck('id')->first();

            // Gabungkan nama dan deskripsi kegiatan untuk pencarian keyword
            $namaKegiatan = strtolower($event['nama_kegiatan_utama'] ?? '');
            $deskripsiKegiatan = is_array($event['deskripsi_tambahan_kegiatan'])
                ? strtolower(implode(' ', $event['deskripsi_tambahan_kegiatan']))
                : strtolower($event['deskripsi_tambahan_kegiatan'] ?? '');

            $searchText = $namaKegiatan . ' ' . $deskripsiKegiatan;

            // Cari keyword yang cocok
            $found = false;
            foreach ($pancatugasKeywords as $cat => $keywords) {
                foreach ($keywords as $keyword) {
                    // str_contains() adalah cara efisien untuk mencari substring di PHP 8+
                    if (str_contains($searchText, $keyword)) {
                        $category = $cat;
                        $categoryId = EventCategory::where('name', $cat)->pluck('id')->first();
                        $found = true;
                        break; // Keluar dari loop keyword jika sudah ketemu
                    }
                }
                if ($found) {
                    break; // Keluar dari loop kategori jika sudah ketemu
                }
            }

            // Tambahkan key baru ke array kegiatan
            $event['kategori_pancatugas'] = [
                'nama' => $category,
                'id' => $categoryId
            ];
        }
        // Pastikan untuk menghapus referensi setelah loop selesai
        unset($event);
    }

    /**
     * [BARU] Mengubah array 'dates' kembali menjadi satu string tanggal yang mudah dibaca.
     * @param array $dates Array berisi pasangan ['start' => ..., 'end' => ...]
     * @return string
     */
    private function reconstructDateString(array $dates): string
    {
        if (empty($dates)) {
            return '';
        }

        $carbonDates = array_map(function ($datePair) {
            return Carbon::parse($datePair['start']);
        }, $dates);

        // Jika hanya ada satu tanggal
        if (count($carbonDates) === 1) {
            return $carbonDates[0]->translatedFormat('l, d F Y');
        }

        // Cek apakah semua tanggal berada di bulan dan tahun yang sama
        $firstMonth = $carbonDates[0]->format('F Y');
        $allSameMonth = true;
        foreach ($carbonDates as $date) {
            if ($date->format('F Y') !== $firstMonth) {
                $allSameMonth = false;
                break;
            }
        }

        // Jika semua di bulan yang sama (kasus paling umum)
        if ($allSameMonth) {
            $dayOfWeek = $carbonDates[0]->translatedFormat('l');
            $monthYear = $carbonDates[0]->translatedFormat('F Y');
            $days = array_map(fn($date) => $date->format('d'), $carbonDates);

            // Buat daftar hari dengan "dan" di antara dua terakhir
            if (count($days) > 1) {
                $lastDay = array_pop($days);
                $dayList = implode(', ', $days) . ' dan ' . $lastDay;
            } else {
                $dayList = $days[0];
            }
            return "{$dayOfWeek}, {$dayList} {$monthYear}";
        } else {
            // Jika beda bulan, gabungkan dengan format lengkap dan pemisah " - "
            return implode(' - ', array_map(fn($date) => $date->translatedFormat('l, d F Y'), $carbonDates));
        }
    }

    /**
     * [BARU] Mengambil informasi waktu dari array 'dates' dan menjadikannya satu string.
     * @param array $dates Array berisi pasangan ['start' => ..., 'end' => ...]
     * @return string
     */
    private function reconstructTimeString(array $dates): string
    {
        if (empty($dates)) {
            return '';
        }

        // Asumsi: waktu sama untuk semua jadwal. Ambil dari jadwal pertama.
        $firstDatePair = $dates[0];
        $startTime = Carbon::parse($firstDatePair['start'])->format('H.i');
        $endTime = Carbon::parse($firstDatePair['end'])->format('H.i');

        if ($startTime === $endTime) {
            return "{$startTime} WIB";
        }

        return "{$startTime} - {$endTime} WIB";
    }
}
