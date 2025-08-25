<?php

namespace App\Services;

use App\DataTransferObjects\LetterData;
use App\DataTransferObjects\PreparationResultData;
use App\Services\PreparationSteps\CategorizeEventsStep;
use App\Services\PreparationSteps\NormalizeAssetsStep;
use App\Services\PreparationSteps\NormalizeLocationsStep;
use App\Services\PreparationSteps\NormalizeOrganizationsStep;
use App\Services\PreparationSteps\ParseDatesStep;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Log;

class DocumentDataPreparationService
{
    protected array $steps = [
        ParseDatesStep::class,
        CategorizeEventsStep::class,
        NormalizeOrganizationsStep::class,
        NormalizeLocationsStep::class,
        NormalizeAssetsStep::class,
    ];

    public function prepareAndNormalize(LetterData $letterData): PreparationResultData
    {
        $dataAsArray = $letterData->toArray();
        $dataAsArray['processing_errors'] = [];

        Log::info('Memulai pipeline dengan steps:', $this->steps);

        $processedData = app(Pipeline::class)
            ->send($dataAsArray)
            ->through($this->steps)
            ->thenReturn();

        $hasBusinessErrors  = $this->checkForErrors($processedData);
        $hasProcessingErrors = !empty($processedData['processing_errors']);

        Log::info('--- PIPELINE SELESAI ---', [
            'hasBusinessErrors' => $hasBusinessErrors,
            'hasProcessingErrors' => $hasProcessingErrors
        ]);

        return new PreparationResultData(
            preparedData: $processedData,
            hasErrors: $hasBusinessErrors
        );
    }

    private function checkForErrors(array $data): bool
    {
        // Cek error parsing tanggal
        if (data_get($data, 'document_information.document_date.status') === 'error') {
            return true;
        }

        foreach (data_get($data, 'events', []) as $event) {
            // Cek error parsing tanggal/waktu di level event
            if (data_get($event, 'parsed_dates.status') === 'error') return true;

            // Cek error matching lokasi
            foreach (data_get($event, 'location_data', []) as $location) {
                if (data_get($location, 'match_status') === 'unmatched') return true;
            }

            // Cek error matching aset
            foreach (data_get($event, 'equipment', []) as $asset) {
                if (data_get($asset, 'match_status') === 'unmatched') return true;
            }
        }

        // Cek error matching organisasi
        foreach (data_get($data, 'document_information.emitter_organizations', []) as $org) {
            if (data_get($org, 'match_status') === 'unmatched') return true;
        }

        return false;
    }
}
