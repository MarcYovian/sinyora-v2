<?php

namespace App\Services;

use App\DataTransferObjects\LetterData;
use App\DataTransferObjects\UpdateDocumentAnalysisData;
use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentProcessingService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected DocumentRepositoryInterface $documentRepository
    ) {}

    /**
     * Memanggil API eksternal, memproses, dan menyimpan hasil analisis mentah.
     *
     * @throws Exception jika terjadi kegagalan.
     * @return array Hasil analisis yang berhasil.
     */
    public function analyzeDocumentFromApi(Document $doc): LetterData
    {
        try {
            $absolutePath = Storage::disk('public')->path($doc->document_path);

            if (!file_exists($absolutePath)) {
                throw new Exception('File fisik tidak ditemukan di server.');
            }

            $response = Http::acceptJson()
                ->timeout(120)
                ->attach('file', file_get_contents($absolutePath), $doc->original_file_name)
                ->post(config('services.bert_api.url') . "extract-information")
                ->throw();

            $analysisResult = LetterData::from($response->json('data'));

            $analysisResult->id = $doc->id;

            $documentData = new UpdateDocumentAnalysisData(
                status: DocumentStatus::PROCESSED,
                processed_by: Auth::id(),
                processed_at: now(),
                analysis_result: $analysisResult,
            );

            $this->documentRepository->updateAnalysisResult($doc->id, $documentData);

            return $analysisResult;
        } catch (ConnectionException $e) {
            Log::error('BERT API Connection Error: ' . $e->getMessage());
            throw new Exception('Analisis gagal karena waktu pemrosesan habis atau koneksi terputus.');
        } catch (RequestException $e) {
            Log::error('BERT API Request Error: ' . $e->response->body());
            throw new Exception('Layanan analisis dokumen sedang mengalami kendala.');
        } catch (Exception $e) {
            Log::error('Document Processing Error: ' . $e->getMessage());
            throw $e; // Lempar kembali exception umum
        }
    }
}
