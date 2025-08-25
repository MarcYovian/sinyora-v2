<?php

namespace App\DataTransferObjects;

use App\Enums\DocumentStatus;

class StoreDocumentData
{
    public function __construct(
        public readonly string $document_path,
        public readonly string $original_file_name,
        public readonly string $mime_type,
        public readonly DocumentStatus $status
    ) {}
}
