<?php

namespace App\Repositories\Contracts;

use App\Models\LicensingDocument;
use Illuminate\Support\Collection;

interface LicensingDocumentRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): LicensingDocument;
    public function findById(int $id): ?LicensingDocument;
    public function update(int $id, array $data): LicensingDocument;
    public function delete(int $id): bool;
}
