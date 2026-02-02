<?php

namespace App\Livewire\Admin\Pages\Document;

use App\Enums\DocumentStatus;
use App\Livewire\Forms\DocumentForm;
use App\Models\Document as ModelsDocument;
use App\Services\DocumentDataPreparationService;
use App\Services\DocumentProcessingService;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class DocumentModal extends Component
{

    public ?ModelsDocument $doc = null;
    public DocumentForm $form;

    // State Management Properties
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

    #[On('setDataForDetailDocument')]
    public function viewDetails($documentId)
    {
        $this->authorize('access', 'admin.documents.show');
        $this->resetValidation();
        $this->form->clear();
        $this->doc = ModelsDocument::with('submitter')->find($documentId);

        if (!$this->doc) {
            flash()->error('Dokumen tidak ditemukan.');
            return;
        }

        $isProcessed = in_array($this->doc->status, [DocumentStatus::PROCESSED, DocumentStatus::DONE]);

        if ($isProcessed && $this->doc->analysis_result) {
            try {
                $analysisResult = $this->doc->analysis_result;

                if (is_null($analysisResult->id)) {
                    $analysisResult->id = $this->doc->id;
                }

                $this->form->setAnalysisResult($analysisResult);

                $this->processingStatus = 'Analisis telah selesai sebelumnya.';
            } catch (Exception $e) {
                flash()->error('Gagal memuat hasil analisis sebelumnya.');
                Log::error('Gagal memuat analysis_result dari DB untuk dokumen ID: ' . $this->doc->id, ['error' => $e->getMessage()]);
            }
        }
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', 'document-modal');
        $this->resetData();
    }

    public function resetData()
    {
        $this->doc = null;
        $this->processingStatus = '';
        $this->isProcessing = false;
        $this->isEditing = false;
        $this->isSave = false;
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
        $this->form->analysisResult = []; // Reset hasil sebelumnya

        $this->dispatch('analysis');
    }

    #[On('analysis')]
    public function analysis()
    {
        try {
            $analysisResult = app(DocumentProcessingService::class)->analyzeDocumentFromApi($this->doc);
            $this->form->setAnalysisResult($analysisResult);
            // dd($this->form->analysisResult);
            $this->processingStatus = 'Analisis Selesai.';
        } catch (Exception $e) {
            $this->processingStatus = 'Error: ' . $e->getMessage();
            Log::error('Gagal saat memanggil DocumentProcessingService: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function prepareReanalysis()
    {
        $this->isProcessing = true;
        $this->form->analysisResult = [];
        $this->processingStatus = 'Mempersiapkan untuk analisis ulang...';

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
        $this->form->analysisResult = $this->doc->analysis_result->toArray() ?: [];
    }

    public function saveAnalysis()
    {
        if (!$this->isEditing) return;

        // Langkah 1: Validasi data dari form
        $this->validate();

        // Langkah 3: Simpan data yang sudah final ke database
        $this->doc->update(['analysis_result' => $this->form->analysisResult]);

        $this->isEditing = false;
        // Beri feedback ke user
        flash()->success('Hasil analisis berhasil diperbarui.');
    }

    public function addItem($kegiatanIndex)
    {
        if (!$this->isEditing) return;

        // Pastikan 'equipment' ada dan merupakan array
        if (!isset($this->form->analysisResult['events'][$kegiatanIndex]['equipment']) || !is_array($this->form->analysisResult['events'][$kegiatanIndex]['equipment'])) {
            $this->form->analysisResult['events'][$kegiatanIndex]['equipment'] = [];
        }

        // Tambahkan item equipment baru
        $this->form->analysisResult['events'][$kegiatanIndex]['equipment'][] = ['item' => '', 'quantity' => 1];
    }

    public function removeItem($kegiatanIndex, $itemIndex)
    {
        if (!$this->isEditing) return;

        // Pastikan equipment ada dan item index valid
        if (isset($this->form->analysisResult['events'][$kegiatanIndex]['equipment'][$itemIndex])) {
            // Hapus item berdasarkan index
            unset($this->form->analysisResult['events'][$kegiatanIndex]['equipment'][$itemIndex]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->form->analysisResult['events'][$kegiatanIndex]['equipment'] = array_values($this->form->analysisResult['events'][$kegiatanIndex]['equipment']);
        }
    }

    public function removeKegiatan($index)
    {
        if (!$this->isEditing) return;

        if (isset($this->form->analysisResult['events'][$index])) {
            unset($this->form->analysisResult['events'][$index]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->form->analysisResult['events'] = array_values($this->form->analysisResult['events']);
        }
    }

    public function addSubject()
    {
        if (!$this->isEditing) return;

        if (!isset($this->form->analysisResult['document_information']['subjects']) || !is_array($this->form->analysisResult['document_information']['subjects'])) {
            $this->form->analysisResult['document_information']['subjects'] = [];
        }

        // Tambahkan subjek baru
        $this->form->analysisResult['document_information']['subjects'][] = '';
    }

    public function removeSubject($index)
    {
        if (!$this->isEditing) return;

        // Pastikan 'subjek' ada dan merupakan array
        if (!isset($this->form->analysisResult['document_information']['subjects'][$index]) || !is_array($this->form->analysisResult['document_information']['subjects'])) {
            // Jika tidak ada subjek, inisialisasi sebagai array kosong
            $this->form->analysisResult['document_information']['subjects'] = [];
        }

        // Hapus subjek berdasarkan index
        if (isset($this->form->analysisResult['document_information']['subjects'][$index])) {
            unset($this->form->analysisResult['document_information']['subjects'][$index]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->form->analysisResult['document_information']['subjects'] = array_values($this->form->analysisResult['document_information']['subjects']);
        }
    }

    public function removeRecipient($index)
    {
        if (!$this->isEditing) return;

        // Pastikan 'penerima' ada dan merupakan array
        if (!isset($this->form->analysisResult['document_information']['recipients'][$index]) || !is_array($this->form->analysisResult['document_information']['recipients'])) {
            // Jika tidak ada penerima, inisialisasi sebagai array kosong
            $this->form->analysisResult['document_information']['recipients'] = [];
        }

        // Hapus penerima berdasarkan index
        if (isset($this->form->analysisResult['document_information']['recipients'][$index])) {
            unset($this->form->analysisResult['document_information']['recipients'][$index]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->form->analysisResult['document_information']['recipients'] = array_values($this->form->analysisResult['document_information']['recipients']);
        }
    }

    public function addOrganization()
    {
        if (!$this->isEditing) return;

        if (!isset($this->form->analysisResult['document_information']['emitter_organizations']) || !is_array($this->form->analysisResult['document_information']['emitter_organizations'])) {
            $this->form->analysisResult['document_information']['emitter_organizations'] = [];
        }

        // Tambahkan penerima baru
        $this->form->analysisResult['document_information']['emitter_organizations'][] = [
            'nama' => '',
        ];
    }

    public function removeOrganization($index)
    {
        if (!$this->isEditing) return;
        // Pastikan 'penerima' ada dan merupakan array
        if (!isset($this->form->analysisResult['document_information']['emitter_organizations'][$index]) || !is_array($this->form->analysisResult['document_information']['emitter_organizations'])) {
            // Jika tidak ada penerima, inisialisasi sebagai array kosong
            $this->form->analysisResult['document_information']['emitter_organizations'] = [];
        }

        // Hapus penerima berdasarkan index
        if (isset($this->form->analysisResult['document_information']['emitter_organizations'][$index])) {
            unset($this->form->analysisResult['document_information']['emitter_organizations'][$index]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->form->analysisResult['document_information']['emitter_organizations'] = array_values($this->form->analysisResult['document_information']['emitter_organizations']);
        }
    }

    public function addOrganizers(int $eventIndex)
    {
        // Pastikan array 'organizers' ada sebelum menambahkan
        if (!isset($this->form->analysisResult['events'][$eventIndex]['organizers'])) {
            $this->form->analysisResult['events'][$eventIndex]['organizers'] = [];
        }

        $this->form->analysisResult['events'][$eventIndex]['organizers'][] = [
            'name' => '',
            'contact' => '',
        ];
    }

    public function removeOrganizer(int $eventIndex, int $organizerIndex)
    {
        if (isset($this->form->analysisResult['events'][$eventIndex]['organizers'][$organizerIndex])) {
            unset($this->form->analysisResult['events'][$eventIndex]['organizers'][$organizerIndex]);
            // Re-index array agar tidak ada "lubang"
            $this->form->analysisResult['events'][$eventIndex]['organizers'] = array_values(
                $this->form->analysisResult['events'][$eventIndex]['organizers']
            );
        }
    }

    public function addRecipient()
    {
        if (!$this->isEditing) return;

        // Pastikan 'penerima_surat' ada dan merupakan array
        if (!isset($this->form->analysisResult['document_information']['recipients']) || !is_array($this->form->analysisResult['document_information']['recipients'])) {
            $this->form->analysisResult['document_information']['recipients'] = [];
        }

        // Tambahkan penerima baru
        $this->form->analysisResult['document_information']['recipients'][] = [
            'name' => '',
            'position' => '',
        ];
    }

    public function addScheduleItem($eventIndex)
    {
        if (!$this->isEditing) return;

        // Pastikan 'jadwal' ada dan merupakan array
        if (!isset($this->form->analysisResult['events'][$eventIndex]['schedule']) || !is_array($this->form->analysisResult['events'][$eventIndex]['schedule'])) {
            $this->form->analysisResult['events'][$eventIndex]['schedule'] = [];
        }

        // Tambahkan item jadwal baru
        $this->form->analysisResult['events'][$eventIndex]['schedule'][] = [
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
        if (!isset($this->form->analysisResult['events'][$eventIndex]['schedule']) || !is_array($this->form->analysisResult['events'][$eventIndex]['schedule'])) {
            // Jika tidak ada jadwal, inisialisasi sebagai array kosong
            $this->form->analysisResult['events'][$eventIndex]['schedule'] = [];
        }

        // Hapus item jadwal berdasarkan index
        if (isset($this->form->analysisResult['events'][$eventIndex]['schedule'][$itemIndex])) {
            unset($this->form->analysisResult['events'][$eventIndex]['schedule'][$itemIndex]);
            // Re-index array untuk menghindari masalah di sisi frontend
            $this->form->analysisResult['events'][$eventIndex]['schedule'] = array_values($this->form->analysisResult['events'][$eventIndex]['schedule']);
        }
    }

    public function removeSigner($index)
    {
        if (!$this->isEditing) return;

        if (isset($this->form->analysisResult['signature_blocks'][$index])) {
            unset($this->form->analysisResult['signature_blocks'][$index]);
            $this->form->analysisResult['signature_blocks'] = array_values($this->form->analysisResult['signature_blocks']);
        }
    }

    public function addSigner()
    {
        if (!$this->isEditing) return;

        if (!isset($this->form->analysisResult['signature_blocks']) || !is_array($this->form->analysisResult['signature_blocks'])) {
            $this->form->analysisResult['signature_blocks'] = [];
        }

        $this->form->analysisResult['signature_blocks'][] = [
            'name' => '',
            'position' => '',
        ];
    }

    public function save()
    {
        $letterData = $this->form->toDto();

        // 2. Serahkan semua pekerjaan ke service dengan satu panggilan method
        $result = app(DocumentDataPreparationService::class)->prepareAndNormalize($letterData);

        if ($result->hasErrors) {
            $this->dispatch('close-modal', 'document-modal');
            $this->dispatch('setDataDocument', data: $result->preparedData)->to(DataDocumentModal::class);
            $this->dispatch('open-modal', 'document-data-modal');
        } else {
            $this->dispatch('close-modal', 'document-modal');
            $this->dispatch('setDataForEvent', data: $result->preparedData)->to(EventModal::class);
            $this->dispatch('open-modal', 'event-modal');
        }
    }

    public function render()
    {
        return view('livewire.admin.pages.document.document-modal');
    }
}
