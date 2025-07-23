<?php

namespace App\Livewire\Admin\Pages\Document;

use App\Exceptions\ParsingFailedException;
use App\Models\Asset;
use App\Models\Document as ModelsDocument;
use App\Models\EventCategory;
use App\Models\Location as ModelsLocation;
use App\Models\Organization as ModelsOrganization;
use App\Services\DateParserService;
use Atomescrochus\StringSimilarities\Compare;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
    public bool $isWrongDateTime = false;

    public $masterAssets;
    public $masterOrganizations;
    public $masterLocations;

    public function rules()
    {
        return [
            // Validasi untuk tipe dokumen yang diedit
            'analysisResult.data.type' => 'required|string|in:undangan,peminjaman,perizinan',

            // Validasi data umum yang diedit
            'analysisResult.data.document_information.city' => 'nullable|string',
            'analysisResult.data.document_information.document_date' => 'required|string',
            'analysisResult.data.document_information.document_number' => 'nullable|string',
            'analysisResult.data.document_information.emitter_email' => 'nullable|string',
            'analysisResult.data.document_information.emitter_organizations' => 'required|array|min:1',
            'analysisResult.data.document_information.emitter_organizations.*.name' => 'required|string',
            'analysisResult.data.document_information.recipients' => 'required|array|min:1',
            'analysisResult.data.document_information.recipients.*.name' => 'required|string',
            'analysisResult.data.document_information.recipients.*.position' => 'nullable|string',
            'analysisResult.data.document_information.subjects' => 'required|array|min:1',
            'analysisResult.data.document_information.subjects.*' => 'required|string',

            // Validasi untuk setiap kegiatan yang diedit
            'analysisResult.data.events.*.attendees' => 'nullable|string',
            'analysisResult.data.events.*.date' => 'required|string',
            'analysisResult.data.events.*.equipment' => 'nullable|array',
            'analysisResult.data.events.*.equipment.*.item' => 'nullable|string',
            'analysisResult.data.events.*.equipment.*.quantity' => 'nullable|string',
            'analysisResult.data.events.*.eventName' => 'required|string',
            'analysisResult.data.events.*.location' => 'required|string',
            'analysisResult.data.events.*.organizers' => 'nullable|array',
            'analysisResult.data.events.*.organizers.*.name' => 'required|string',
            'analysisResult.data.events.*.organizers.*.contact' => 'required|string',
            'analysisResult.data.events.*.schedule' => 'nullable|array',
            'analysisResult.data.events.*.schedule.*.description' => 'required|string',
            'analysisResult.data.events.*.schedule.*.duration' => 'nullable|string',
            'analysisResult.data.events.*.schedule.*.startTime' => 'nullable|string',
            'analysisResult.data.events.*.schedule.*.endTime' => 'nullable|string',
            'analysisResult.data.events.*.time' => 'required|string',

            // Validasi untuk penanda tangan (data dinamis di analysisResult)
            'analysisResult.data.signature_blocks' => 'required|array|min:1',
            'analysisResult.data.signature_blocks.*.name' => 'required|string',
            'analysisResult.data.signature_blocks.*.position' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            // Pesan untuk Tipe Dokumen
            'analysisResult.data.type.required' => 'Tipe dokumen wajib diisi.',
            'analysisResult.data.type.string' => 'Tipe dokumen harus berupa teks.',
            'analysisResult.data.type.in' => 'Tipe dokumen harus salah satu dari: undangan, peminjaman, perizinan.',

            // Pesan untuk Informasi Umum Dokumen
            'analysisResult.data.document_information.city.string' => 'Kota dokumen harus berupa teks.',
            'analysisResult.data.document_information.document_date.required' => 'Tanggal dokumen wajib diisi.',
            'analysisResult.data.document_information.document_date.string' => 'Tanggal dokumen harus berupa teks.',
            'analysisResult.data.document_information.document_number.string' => 'Nomor dokumen harus berupa teks.',
            'analysisResult.data.document_information.emitter_email.string' => 'Email pengirim harus berupa teks.',
            'analysisResult.data.document_information.emitter_organizations.required' => 'Organisasi pengirim wajib diisi.',
            'analysisResult.data.document_information.emitter_organizations.array' => 'Organisasi pengirim harus berupa daftar.',
            'analysisResult.data.document_information.emitter_organizations.min' => 'Minimal ada satu organisasi pengirim.',
            'analysisResult.data.document_information.emitter_organizations.*.name.required' => 'Nama organisasi pengirim wajib diisi.',
            'analysisResult.data.document_information.emitter_organizations.*.name.string' => 'Nama organisasi pengirim harus berupa teks.',
            'analysisResult.data.document_information.recipients.required' => 'Penerima wajib diisi.',
            'analysisResult.data.document_information.recipients.array' => 'Penerima harus berupa daftar.',
            'analysisResult.data.document_information.recipients.min' => 'Minimal ada satu penerima.',
            'analysisResult.data.document_information.recipients.*.name.required' => 'Nama penerima wajib diisi.',
            'analysisResult.data.document_information.recipients.*.name.string' => 'Nama penerima harus berupa teks.',
            'analysisResult.data.document_information.recipients.*.position.string' => 'Jabatan penerima harus berupa teks.',
            'analysisResult.data.document_information.subjects.required' => 'Perihal wajib diisi.',
            'analysisResult.data.document_information.subjects.array' => 'Perihal harus berupa daftar.',
            'analysisResult.data.document_information.subjects.min' => 'Minimal ada satu perihal.',
            'analysisResult.data.document_information.subjects.*.required' => 'Setiap perihal wajib diisi.',
            'analysisResult.data.document_information.subjects.*.string' => 'Perihal harus berupa teks.',

            // Pesan untuk Setiap Kegiatan (Events)
            'analysisResult.data.events.*.attendees.string' => 'Peserta harus berupa teks.',
            'analysisResult.data.events.*.date.required' => 'Tanggal kegiatan wajib diisi.',
            'analysisResult.data.events.*.date.string' => 'Tanggal kegiatan harus berupa teks.',
            'analysisResult.data.events.*.equipment.array' => 'Daftar peralatan harus berupa daftar.',
            'analysisResult.data.events.*.equipment.*.item.string' => 'Nama peralatan harus berupa teks.',
            'analysisResult.data.events.*.equipment.*.quantity.string' => 'Kuantitas peralatan harus berupa teks.', // Catatan: Jika ini seharusnya numerik, ubah di rules() dan pesan ini
            'analysisResult.data.events.*.eventName.required' => 'Nama kegiatan wajib diisi.',
            'analysisResult.data.events.*.eventName.string' => 'Nama kegiatan harus berupa teks.',
            'analysisResult.data.events.*.location.required' => 'Lokasi kegiatan wajib diisi.',
            'analysisResult.data.events.*.location.string' => 'Lokasi kegiatan harus berupa teks.',
            'analysisResult.data.events.*.organizers.array' => 'Daftar penanggung jawab harus berupa daftar.',
            'analysisResult.data.events.*.organizers.*.name.required' => 'Nama penanggung jawab wajib diisi.',
            'analysisResult.data.events.*.organizers.*.name.string' => 'Nama penanggung jawab harus berupa teks.',
            'analysisResult.data.events.*.organizers.*.contact.required' => 'Kontak penanggung jawab wajib diisi.',
            'analysisResult.data.events.*.organizers.*.contact.string' => 'Kontak penanggung jawab harus berupa teks.',
            'analysisResult.data.events.*.schedule.array' => 'Daftar jadwal harus berupa daftar.',
            'analysisResult.data.events.*.schedule.*.description.required' => 'Deskripsi jadwal wajib diisi.',
            'analysisResult.data.events.*.schedule.*.description.string' => 'Deskripsi jadwal harus berupa teks.',
            'analysisResult.data.events.*.schedule.*.duration.string' => 'Durasi jadwal harus berupa teks.',
            'analysisResult.data.events.*.schedule.*.startTime.string' => 'Waktu mulai jadwal harus berupa teks.',
            'analysisResult.data.events.*.schedule.*.endTime.string' => 'Waktu selesai jadwal harus berupa teks.',
            'analysisResult.data.events.*.time.required' => 'Waktu kegiatan wajib diisi.',
            'analysisResult.data.events.*.time.string' => 'Waktu kegiatan harus berupa teks.',

            // Pesan untuk Penanda Tangan
            'analysisResult.data.signature_blocks.required' => 'Penanda tangan wajib diisi.',
            'analysisResult.data.signature_blocks.array' => 'Penanda tangan harus berupa daftar.',
            'analysisResult.data.signature_blocks.min' => 'Minimal ada satu penanda tangan.',
            'analysisResult.data.signature_blocks.*.name.required' => 'Nama penanda tangan wajib diisi.',
            'analysisResult.data.signature_blocks.*.name.string' => 'Nama penanda tangan harus berupa teks.',
            'analysisResult.data.signature_blocks.*.position.required' => 'Jabatan penanda tangan wajib diisi.',
            'analysisResult.data.signature_blocks.*.position.string' => 'Jabatan penanda tangan harus berupa teks.',
        ];
    }

    #[On('setDataForDetailDocument')]
    public function viewDetails($documentId)
    {
        $this->authorize('access', 'admin.documents.show');

        $this->reset([
            'doc',
            'analysisResult',
            'processingStatus',
            'isProcessing',
            'isEditing',
            'editableKegiatan',
            'editableData',
            'isSave',
            'isWrongDataAsset'
        ]);

        $this->doc = ModelsDocument::with(['submitter'])
            ->where('id', $documentId)
            ->first();

        if (!$this->doc) {
            toastr()->error('Dokumen tidak ditemukan.');
            return;
        }

        $isProcessed = in_array($this->doc->status, ['processed', 'done']);

        if ($isProcessed && !empty($this->doc->analysis_result)) {
            $decodedResult = json_decode($this->doc->analysis_result, true);
            if (is_array($decodedResult)) {
                $this->analysisResult = $decodedResult;

                if (!data_get($this->analysisResult, 'data.id')) {
                    data_set($this->analysisResult, 'data.id', $this->doc->id);
                }

                $this->processingStatus = 'Analisis telah selesai sebelumnya.';
            } else {
                $this->processingStatus = 'Gagal memuat hasil analisis (format tidak valid).';
                Log::error('Gagal melakukan json_decode pada analysis_result untuk dokumen ID: ' . $this->doc->id);
            }
        }
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
                ->post(config('services.bert_api.url') . "extract-information")
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

        // Langkah 3: Simpan data yang sudah final ke database
        $this->doc->update(['analysis_result' => json_encode($this->analysisResult)]);

        $this->isEditing = false;
        // Beri feedback ke user
        toastr()->success('Hasil analisis berhasil diperbarui.');
    }

    public function addItem($kegiatanIndex)
    {
        if (!$this->isEditing) return;

        // Pastikan 'barang_dipinjam' ada dan merupakan array
        if (!isset($this->analysisResult['data']['events'][$kegiatanIndex]['equipment']) || !is_array($this->analysisResult['data']['events'][$kegiatanIndex]['equipment'])) {
            $this->analysisResult['data']['events'][$kegiatanIndex]['equipment'] = [];
        }

        $this->analysisResult['data']['events'][$kegiatanIndex]['equipment'][] = [
            'item' => 'Barang Baru',
            'quantity' => 1,
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

        if (isset($this->analysisResult['data']['events'][$kegiatanIndex]['equipment'][$itemIndex])) {
            unset($this->analysisResult['data']['events'][$kegiatanIndex]['equipment'][$itemIndex]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->analysisResult['data']['events'][$kegiatanIndex]['equipment'] = array_values($this->analysisResult['data']['events'][$kegiatanIndex]['equipment']);
        }
    }

    public function removeKegiatan($index)
    {
        if (!$this->isEditing) return;

        // Hapus dari data utama yang akan disimpan
        if (isset($this->analysisResult['data']['events'][$index])) {
            unset($this->analysisResult['data']['events'][$index]);
            $this->analysisResult['data']['events'] = array_values($this->analysisResult['data']['events']);
        }
    }

    public function addSubject()
    {
        if (!$this->isEditing) return;

        if (!isset($this->analysisResult['data']['document_information']['subjects']) || !is_array($this->analysisResult['data']['document_information']['subjects'])) {
            $this->analysisResult['data']['document_information']['subjects'] = [];
        }

        // Tambahkan subjek baru
        $this->analysisResult['data']['document_information']['subjects'][] = '';
    }

    public function removeSubject($index)
    {
        if (!$this->isEditing) return;

        // Pastikan 'subjek' ada dan merupakan array
        if (!isset($this->analysisResult['data']['document_information']['subjects'][$index]) || !is_array($this->analysisResult['data']['document_information']['subjects'])) {
            // Jika tidak ada subjek, inisialisasi sebagai array kosong
            $this->analysisResult['data']['document_information']['subjects'] = [];
        }

        // Hapus subjek berdasarkan index
        if (isset($this->analysisResult['data']['document_information']['subjects'][$index])) {
            unset($this->analysisResult['data']['document_information']['subjects'][$index]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->analysisResult['data']['document_information']['subjects'] = array_values($this->analysisResult['data']['document_information']['subjects']);
        }
    }

    public function removeRecipient($index)
    {
        if (!$this->isEditing) return;

        // Pastikan 'penerima' ada dan merupakan array
        if (!isset($this->analysisResult['data']['document_information']['recipients'][$index]) || !is_array($this->analysisResult['data']['document_information']['recipients'])) {
            // Jika tidak ada penerima, inisialisasi sebagai array kosong
            $this->analysisResult['data']['document_information']['recipients'] = [];
        }

        // Hapus penerima berdasarkan index
        if (isset($this->analysisResult['data']['document_information']['recipients'][$index])) {
            unset($this->analysisResult['data']['document_information']['recipients'][$index]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->analysisResult['data']['document_information']['recipients'] = array_values($this->analysisResult['data']['document_information']['recipients']);
        }
    }

    public function addOrganization()
    {
        if (!$this->isEditing) return;

        if (!isset($this->analysisResult['data']['document_information']['emitter_organizations']) || !is_array($this->analysisResult['data']['document_information']['emitter_organizations'])) {
            $this->analysisResult['data']['document_information']['emitter_organizations'] = [];
        }

        // Tambahkan penerima baru
        $this->analysisResult['data']['document_information']['emitter_organizations'][] = [
            'nama' => '',
        ];
    }

    public function removeOrganization($index)
    {
        if (!$this->isEditing) return;

        // Pastikan 'penerima' ada dan merupakan array
        if (!isset($this->analysisResult['data']['document_information']['emitter_organizations'][$index]) || !is_array($this->analysisResult['data']['document_information']['emitter_organizations'])) {
            // Jika tidak ada penerima, inisialisasi sebagai array kosong
            $this->analysisResult['data']['document_information']['emitter_organizations'] = [];
        }

        // Hapus penerima berdasarkan index
        if (isset($this->analysisResult['data']['document_information']['emitter_organizations'][$index])) {
            unset($this->analysisResult['data']['document_information']['emitter_organizations'][$index]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->analysisResult['data']['document_information']['emitter_organizations'] = array_values($this->analysisResult['data']['document_information']['emitter_organizations']);
        }
    }

    public function addOrganizers(int $eventIndex)
    {
        // Pastikan array 'organizers' ada sebelum menambahkan
        if (!isset($this->analysisResult['data']['events'][$eventIndex]['organizers'])) {
            $this->analysisResult['data']['events'][$eventIndex]['organizers'] = [];
        }

        $this->analysisResult['data']['events'][$eventIndex]['organizers'][] = [
            'name' => '',
            'contact' => '',
        ];
    }

    public function removeOrganizer(int $eventIndex, int $organizerIndex)
    {
        if (isset($this->analysisResult['data']['events'][$eventIndex]['organizers'][$organizerIndex])) {
            unset($this->analysisResult['data']['events'][$eventIndex]['organizers'][$organizerIndex]);
            // Re-index array agar tidak ada "lubang"
            $this->analysisResult['data']['events'][$eventIndex]['organizers'] = array_values(
                $this->analysisResult['data']['events'][$eventIndex]['organizers']
            );
        }
    }

    public function addRecipient()
    {
        if (!$this->isEditing) return;

        // Pastikan 'penerima_surat' ada dan merupakan array
        if (!isset($this->analysisResult['data']['document_information']['recipients']) || !is_array($this->analysisResult['data']['document_information']['recipients'])) {
            $this->analysisResult['data']['document_information']['recipients'] = [];
        }

        // Tambahkan penerima baru
        $this->analysisResult['data']['document_information']['recipients'][] = [
            'name' => '',
            'position' => '',
        ];
    }

    public function addScheduleItem($eventIndex)
    {
        if (!$this->isEditing) return;

        // Pastikan 'jadwal' ada dan merupakan array
        if (!isset($this->analysisResult['data']['events'][$eventIndex]['schedule']) || !is_array($this->analysisResult['data']['events'][$eventIndex]['schedule'])) {
            $this->analysisResult['data']['events'][$eventIndex]['schedule'] = [];
        }

        // Tambahkan item jadwal baru
        $this->analysisResult['data']['events'][$eventIndex]['schedule'][] = [
            'description' => '',
            'duration' => '',
            'startTime' => '',
            'endTime' => '',
        ];
    }

    public function removeScheduleItem($eventIndex, $itemIndex)
    {
        if (!$this->isEditing) return;

        // Pastikan 'jadwal' ada dan merupakan array
        if (!isset($this->analysisResult['data']['events'][$eventIndex]['schedule']) || !is_array($this->analysisResult['data']['events'][$eventIndex]['schedule'])) {
            // Jika tidak ada jadwal, inisialisasi sebagai array kosong
            $this->analysisResult['data']['events'][$eventIndex]['schedule'] = [];
        }

        // Hapus item jadwal berdasarkan index
        if (isset($this->analysisResult['data']['events'][$eventIndex]['schedule'][$itemIndex])) {
            unset($this->analysisResult['data']['events'][$eventIndex]['schedule'][$itemIndex]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->analysisResult['data']['events'][$eventIndex]['schedule'] = array_values($this->analysisResult['data']['events'][$eventIndex]['schedule']);
        }
    }

    public function removeSigner($index)
    {
        if (!$this->isEditing) return;

        if (isset($this->analysisResult['data']['signature_blocks'][$index])) {
            unset($this->analysisResult['data']['signature_blocks'][$index]);
            $this->analysisResult['data']['signature_blocks'] = array_values($this->analysisResult['data']['signature_blocks']);
        }
    }

    public function addSigner()
    {
        if (!$this->isEditing) return;

        if (!isset($this->analysisResult['data']['signature_blocks']) || !is_array($this->analysisResult['data']['signature_blocks'])) {
            $this->analysisResult['data']['signature_blocks'] = [];
        }

        $this->analysisResult['data']['signature_blocks'][] = [
            'name' => '',
            'position' => '',
        ];
    }

    public function save()
    {
        // Pastikan ada data untuk diproses
        if (empty($this->analysisResult['data'])) {
            toastr()->error('Tidak ada data untuk disimpan.');
            return;
        }

        $data = $this->analysisResult['data'];
        $type = $data['type'] ?? null;

        try {
            $parseDate = DateParserService::parse(
                text: $data['document_information']['document_date'],
                fieldType: 'date'
            );
            if ($parseDate['type'] !== 'single') {
                $data['document_information']['document_date'] = [
                    'status' => 'error',
                    'type' => $parseDate['type'],
                    'date' => $data['document_information']['document_date'],
                    'messages' => "Tanggal dokumen wajib berupa tanggal tunggal, bukan rentang atau format lain.",
                ];
            } else {
                $data['document_information']['document_date'] = [
                    'status' => 'success',
                    'type' => $parseDate['type'],
                    'date' => $parseDate['date'],
                    'messages' => '',
                ];
            }
        } catch (ParsingFailedException $e) {
            $data['document_information']['document_date'] = [
                'status' => 'error',
                'type' => 'invalid',
                'date' => $data['document_information']['document_date'],
                'messages' => $e->getMessage(),
            ];
            $this->isWrongDateTime = true;
        }
        if (isset($data['events'])) {
            $this->categorizeEvents(events: $data['events']);
            $this->AllEventDateTime(events: $data['events']);
        }

        $normalizedEventDetails = $this->normalizeAssetNamesFuzzy($data['events']);
        $normalizedLocationDetails = $this->normalizeLocationNamesFuzzy($normalizedEventDetails);
        $normalizedOrganizations = $this->normalizeOrganizationNamesFuzzy($data['document_information']['emitter_organizations']);

        $data['events'] = $normalizedLocationDetails;
        $data['document_information']['emitter_organizations'] = $normalizedOrganizations;
        // dd(json_encode($data, JSON_PRETTY_PRINT));

        if ($this->isWrongDateTime || $this->isWrongDataAsset || $this->isWrongDataOrganization || $this->isWrongDataLocation) {
            Log::error('Data tidak valid ditemukan saat menyimpan dokumen.', [
                'isWrongDateTime' => $this->isWrongDateTime,
                'isWrongDataAsset' => $this->isWrongDataAsset,
                'isWrongDataOrganization' => $this->isWrongDataOrganization,
                'isWrongDataLocation' => $this->isWrongDataLocation,
            ]);
            $this->dispatch('close-modal', 'document-modal');
            $this->dispatch('setDataDocument', data: $data)->to(DataDocumentModal::class);
            $this->dispatch('open-modal', 'document-data-modal');
            return;
        } else {
            $this->dispatch('close-modal', 'document-modal');
            $this->dispatch('setDataForEvent', data: $data)->to(EventModal::class);
            $this->dispatch('open-modal', 'event-modal');
        }
    }

    public function render()
    {
        return view('livewire.admin.pages.document.document-modal');
    }

    private function normalizeOrganizationNamesFuzzy(array $orgs)
    {
        $comparison = new Compare();
        $this->masterOrganizations = ModelsOrganization::active()->get(['id', 'name']);
        $similarityThreshold = 0.6;
        foreach ($orgs as &$org) {
            $org['original_name'] = $org['name'];
            $extractedName = strtolower(trim($org['name']));
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
            }
        }
        unset($org);

        $matchedOrgs = array_filter($orgs, function ($org) {
            return $org['match_status'] === 'matched';
        });
        $matchedCount = count($matchedOrgs);
        $this->isWrongDataOrganization = $matchedCount !== 1;
        return $orgs;
    }

    private function normalizeAssetNamesFuzzy(array $events)
    { // Output: Semua status masih 'inactive'
        $comparison = new Compare();
        $this->masterAssets = Asset::active()->get(['id', 'name']);
        $similarityThreshold = 0.8;


        foreach ($events as &$event) {
            foreach ($event['equipment'] as &$asset) {
                // Pastikan format aset benar
                if (!isset($asset['item'])) {
                    continue;
                }

                $numericQuantity = preg_replace('/[^0-9]/', '', $asset['quantity']);
                $asset['quantity'] = (int) $numericQuantity;

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
        $similarityThreshold = 0.75;
        // dd($events);

        foreach ($events as $index => &$event) {
            // Pastikan format aset benar
            if (!isset($event['location'])) {
                continue;
            }

            $finalLocations = [];

            // 1. Jalankan ekspansi angka terlebih dahulu
            $potentiallyExpanded = $this->expandNumericRange($event['location']);

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

    private function getDateFormat($date)
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
            // 1. Proses tanggal dan waktu utama acara
            $this->processMainEventDateTime(event: $event);

            // 2. Proses waktu untuk setiap item dalam jadwal (rundown)
            $this->processScheduleTimes(event: $event);
        }

        unset($event);
    }

    private function processMainEventDateTime(array &$event)
    {
        // Panggil helper untuk menggabungkan tanggal dan waktu
        $datetimeResult = $this->combineDateTime($event['date'], $event['time']);

        if (is_null($datetimeResult)) {
            // Jika gagal, catat error dan set flag
            $event['dates'][] = [
                'start' => null,
                'end' => null,
                'status' => 'error',
                'messages' => "Format tanggal atau waktu utama tidak valid untuk event '{$event['eventName']}'"
            ];
            $this->isWrongDateTime = true;
        } else {
            // Jika berhasil, isi key 'dates' dengan hasilnya
            $event['dates'] = $datetimeResult;
        }
    }

    /**
     * Melakukan iterasi pada setiap jadwal dalam sebuah event dan memproses waktunya.
     */
    private function processScheduleTimes(array &$event)
    {
        if (empty($event['schedule'])) {
            return;
        }

        foreach ($event['schedule'] as &$scheduleItem) {
            // Gunakan helper yang sama untuk startTime dan endTime untuk menghindari duplikasi
            if (!empty($scheduleItem['startTime']) && !empty($scheduleItem['endTime'])) {
                $scheduleItem['startTime_processed'] = $this->parseSingleTime($scheduleItem['startTime']);
                $scheduleItem['endTime_processed'] = $this->parseSingleTime($scheduleItem['endTime']);

                // Jika salah satu gagal, set flag error utama
                if ($scheduleItem['startTime_processed']['status'] === 'error' || $scheduleItem['endTime_processed']['status'] === 'error') {
                    $this->isWrongDateTime = true;
                }
            } else {
                // Jika tidak ada waktu, set sebagai null
                $scheduleItem['startTime_processed'] = [
                    'status' => 'success',
                    'type' => 'single',
                    'time' => null,
                    'messages' => ''
                ];
                $scheduleItem['endTime_processed'] = [
                    'status' => 'success',
                    'type' => 'single',
                    'time' => null,
                    'messages' => ''
                ];
            }
        }
        unset($scheduleItem);
    }

    /**
     * Helper untuk mem-parsing satu string waktu dan mengembalikan struktur array yang konsisten.
     * Menghilangkan duplikasi blok try-catch.
     */
    private function parseSingleTime(string $timeString): array
    {
        try {
            $parsedTime = DateParserService::parse(text: $timeString, fieldType: 'time');

            // Logika ini bisa disederhanakan, karena kita hanya mengharapkan 'single' time
            // Namun untuk keamanan, kita cek tipenya.
            if (in_array($parsedTime['type'], ['single', 'range', 'range_open_end'])) {
                return [
                    'status' => 'success',
                    'type' => $parsedTime['type'],
                    // Ambil waktu pertama yang relevan dari hasil parse
                    'time' => $parsedTime['time'] ?? $parsedTime['start_time'],
                    'messages' => ''
                ];
            }

            // Jika tipe tidak terduga
            throw new Exception("Tipe waktu tidak terduga: {$parsedTime['type']}");
        } catch (ParsingFailedException | Exception $e) {
            Log::error("Gagal mem-parsing waktu untuk '{$timeString}'. Error: " . $e->getMessage());
            return [
                'status' => 'error',
                'type' => 'invalid',
                'time' => $timeString, // Kembalikan nilai asli
                'messages' => $e->getMessage()
            ];
        }
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
            // --- LANGKAH 1: PARSE TANGGAL & WAKTU DENGAN SERVICE ---
            // Panggil service untuk membedah string tanggal dan waktu.
            $parsedDate = DateParserService::parse($dateStr, 'date');
            $parsedTime = DateParserService::parse($timeStr, 'time');

            // --- LANGKAH 2: PROSES HASIL PARSING WAKTU ---
            $startTime = Carbon::createFromTime(0, 0); // Default waktu mulai
            $endTime = Carbon::createFromTime(23, 59, 59); // Default waktu selesai

            if ($parsedTime) {
                switch ($parsedTime['type']) {
                    case 'single':
                        $startTime = Carbon::parse($parsedTime['time']);
                        // Jika hanya satu waktu, asumsikan durasi 3 jam (sesuai logika awal)
                        $endTime = $startTime->copy()->addHours(3);
                        break;
                    case 'range':
                        $startTime = Carbon::parse($parsedTime['start_time']);
                        $endTime = Carbon::parse($parsedTime['end_time']);
                        break;
                    case 'range_open_end': // Format "19:00 - selesai"
                        $startTime = Carbon::parse($parsedTime['start_time']);
                        // Asumsikan durasi 3 jam jika "selesai"
                        $endTime = $startTime->copy()->addHours(3);
                        break;
                }
            }

            // --- LANGKAH 3: PROSES HASIL PARSING TANGGAL ---
            $eventDates = [];
            if ($parsedDate) {
                switch ($parsedDate['type']) {
                    case 'single':
                        $eventDates[] = Carbon::parse($parsedDate['date']);
                        break;
                    case 'list':
                        foreach ($parsedDate['dates'] as $date) {
                            $eventDates[] = Carbon::parse($date);
                        }
                        break;
                    case 'range':
                        // Buat periode dari tanggal mulai hingga selesai
                        $period = CarbonPeriod::create($parsedDate['start_date'], $parsedDate['end_date']);
                        foreach ($period as $date) {
                            $eventDates[] = $date;
                        }
                        break;
                }
            } else {
                // Jika tanggal tidak bisa diparsing, tidak ada yang bisa diproses.
                throw new Exception("Format tanggal '{$dateStr}' tidak valid.");
            }


            // --- LANGKAH 4: GABUNGKAN SETIAP TANGGAL DENGAN WAKTU ---
            $result = [];
            foreach ($eventDates as $date) {
                $startDateTime = $date->copy()->setTimeFrom($startTime);
                $endDateTime = $date->copy()->setTimeFrom($endTime);

                // Handle kasus jika waktu selesai melewati tengah malam (misal: 22:00 - 01:00)
                if ($endDateTime->lt($startDateTime)) {
                    $endDateTime->addDay();
                }

                $result[] = [
                    'start' => $startDateTime->format('Y-m-d\TH:i'), // Format standar: YYYY-MM-DDTHH:MM
                    'end'   => $endDateTime->format('Y-m-d\TH:i'),
                ];
            }

            return $result;
        } catch (ParsingFailedException | Exception $e) {
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
            $namaKegiatan = strtolower($event['eventName'] ?? '');

            $searchText = $namaKegiatan;

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
            $event['fivetask_categories'] = [
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
