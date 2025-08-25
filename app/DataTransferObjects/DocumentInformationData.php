<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class DocumentInformationData extends Data
{
    public function __construct(
        public ?string $document_city,
        public ?string $document_date,
        public ?string $document_number,
        public ?string $emitter_email,

        #[DataCollectionOf(OrganizationData::class)]
        public ?DataCollection $emitter_organizations,

        #[DataCollectionOf(SignatureData::class)]
        public ?DataCollection $recipients,

        /** @var string[] */
        public ?array $subjects,
    ) {}
}
