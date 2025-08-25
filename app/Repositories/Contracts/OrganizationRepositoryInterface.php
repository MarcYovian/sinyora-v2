<?php

namespace App\Repositories\Contracts;

use App\Models\Organization;
use Illuminate\Support\Collection;

interface OrganizationRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): Organization;
    public function create(array $data): Organization;
    public function update(int $id, array $data): Organization;
    public function delete(int $id);
    public function getActiveOrganizations(): Collection;
    public function getAllOrderedByName(): Collection;
}
