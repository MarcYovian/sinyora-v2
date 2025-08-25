<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class LetterData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $file_name,
        public ?string $type,
        public ?string $text,
        public ?DocumentInformationData $document_information,

        #[DataCollectionOf(EventData::class)]
        public ?DataCollection $events,

        #[DataCollectionOf(SignatureData::class)]
        public ?DataCollection $signature_blocks,
    ) {}
}
