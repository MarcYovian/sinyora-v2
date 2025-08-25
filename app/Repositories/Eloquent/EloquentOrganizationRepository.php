<?php

namespace App\Repositories\Eloquent;

use App\Models\Organization;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentOrganizationRepository implements OrganizationRepositoryInterface
{
    //

    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return Organization::all();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Organization
    {
        return Organization::create($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id)
    {
        return Organization::find($id)->delete();
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): Organization
    {
        return Organization::find($id);
    }

    /**
     * @inheritDoc
     */
    public function getActiveOrganizations(): Collection
    {
        return Organization::active()->get();
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Organization
    {
        $organization = $this->findById($id);
        if ($organization) {
            $organization->update($data);
            return $organization;
        }
        throw new \Exception("Organization with ID {$id} not found.");
    }

    public function getAllOrderedByName(): Collection
    {
        return Organization::orderBy('name')->get();
    }
}
