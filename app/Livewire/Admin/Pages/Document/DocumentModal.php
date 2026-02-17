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
    public bool $isApiAvailable = true;
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

        // Cek ketersediaan BERT API
        $this->isApiAvailable = app(DocumentProcessingService::class)->checkApiHealth();
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
        $this->isApiAvailable = true;
    }

    /**
     * Memulai mode input manual dengan template data kosong.
     */
    public function startManualInput()
    {
        $this->form->analysisResult = [
            'id' => $this->doc->id,
            'file_name' => $this->doc->original_file_name,
            'type' => '',
            'text' => '',
            'document_information' => [
                'document_date' => '',
                'document_number' => '',
                'document_city' => '',
                'emitter_email' => '',
                'subjects' => [''],
                'recipients' => [['name' => '', 'position' => '']],
                'emitter_organizations' => [['name' => '']],
            ],
            'events' => [[
                'eventName' => '',
                'date' => '',
                'time' => '',
                'location' => '',
                'attendees' => '',
                'equipment' => [],
                'organizers' => [],
                'schedule' => [],
            ]],
            'signature_blocks' => [['name' => '', 'position' => '']],
        ];
        $this->isEditing = true;
        $this->processingStatus = 'Mode input manual aktif. Silakan isi data dokumen secara manual.';
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

    // -----------------------------------------------------------
    // Generic Array Helpers
    // -----------------------------------------------------------

    /**
     * Append an item to a nested array in analysisResult.
     */
    private function appendToAnalysisResult(string $path, mixed $template): void
    {
        if (!$this->isEditing) return;

        $current = data_get($this->form->analysisResult, $path, []);
        if (!is_array($current)) $current = [];
        $current[] = $template;
        data_set($this->form->analysisResult, $path, $current);
    }

    /**
     * Remove an item from a nested array in analysisResult and re-index.
     */
    private function removeFromAnalysisResult(string $path, int $index): void
    {
        if (!$this->isEditing) return;

        $current = data_get($this->form->analysisResult, $path, []);
        if (isset($current[$index])) {
            array_splice($current, $index, 1);
            data_set($this->form->analysisResult, $path, $current);
        }
    }

    // -----------------------------------------------------------
    // Item (Equipment) Methods
    // -----------------------------------------------------------

    public function addItem($kegiatanIndex)
    {
        $this->appendToAnalysisResult("events.{$kegiatanIndex}.equipment", ['item' => '', 'quantity' => 1]);
    }

    public function removeItem($kegiatanIndex, $itemIndex)
    {
        $this->removeFromAnalysisResult("events.{$kegiatanIndex}.equipment", $itemIndex);
    }

    public function removeKegiatan($index)
    {
        $this->removeFromAnalysisResult('events', $index);
    }

    // -----------------------------------------------------------
    // Document Information Methods
    // -----------------------------------------------------------

    public function addSubject()
    {
        $this->appendToAnalysisResult('document_information.subjects', '');
    }

    public function removeSubject($index)
    {
        $this->removeFromAnalysisResult('document_information.subjects', $index);
    }

    public function addRecipient()
    {
        $this->appendToAnalysisResult('document_information.recipients', ['name' => '', 'position' => '']);
    }

    public function removeRecipient($index)
    {
        $this->removeFromAnalysisResult('document_information.recipients', $index);
    }

    public function addOrganization()
    {
        $this->appendToAnalysisResult('document_information.emitter_organizations', ['nama' => '']);
    }

    public function removeOrganization($index)
    {
        $this->removeFromAnalysisResult('document_information.emitter_organizations', $index);
    }

    // -----------------------------------------------------------
    // Event Sub-item Methods
    // -----------------------------------------------------------

    public function addOrganizers(int $eventIndex)
    {
        $this->appendToAnalysisResult("events.{$eventIndex}.organizers", ['name' => '', 'contact' => '']);
    }

    public function removeOrganizer(int $eventIndex, int $organizerIndex)
    {
        $this->removeFromAnalysisResult("events.{$eventIndex}.organizers", $organizerIndex);
    }

    public function addScheduleItem($eventIndex)
    {
        $this->appendToAnalysisResult("events.{$eventIndex}.schedule", [
            'description' => '', 'duration' => '', 'startTime' => '', 'endTime' => '',
        ]);
    }

    public function removeScheduleItem($eventIndex, $itemIndex)
    {
        $this->removeFromAnalysisResult("events.{$eventIndex}.schedule", $itemIndex);
    }

    // -----------------------------------------------------------
    // Signature Block Methods
    // -----------------------------------------------------------

    public function addSigner()
    {
        $this->appendToAnalysisResult('signature_blocks', ['name' => '', 'position' => '']);
    }

    public function removeSigner($index)
    {
        $this->removeFromAnalysisResult('signature_blocks', $index);
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
