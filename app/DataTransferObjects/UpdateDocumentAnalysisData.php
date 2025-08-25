<?php

namespace App\DataTransferObjects;

use App\Enums\DocumentStatus;
use Spatie\LaravelData\Data;

class UpdateDocumentAnalysisData extends Data
{
    public function __construct(
        public readonly DocumentStatus $status,
        public readonly string $processed_by,
        public readonly string $processed_at,
        public readonly LetterData $analysis_result
    ) {}
}
