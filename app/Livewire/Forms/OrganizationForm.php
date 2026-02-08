<?php

namespace App\Livewire\Forms;

use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Form;

class OrganizationForm extends Form
{
    public ?Organization $organization;

    #[Validate('required')]
    public string $name = '';
    #[Validate('nullable')]
    public string $description = '';
    #[Validate('required')]
    public string $code = '';
    #[Validate('required')]
    public $is_active = 0;

    public function setOrganization(?Organization $organization)
    {
        $this->organization = $organization;

        if ($this->organization) {
            $this->name = $organization->name;
            $this->description = $organization->description ?? '';
            $this->code = $organization->code;
            $this->is_active = $organization->is_active;
        }
    }

    public function store(): Organization
    {
        $this->validate();

        try {
            $organization = Organization::create([
                'name' => $this->name,
                'description' => $this->description,
                'code' => $this->code,
                'is_active' => $this->is_active,
            ]);

            Log::info('Organization created via form', [
                'organization_id' => $organization->id,
                'organization_name' => $organization->name,
                'user_id' => auth()->id(),
            ]);

            $this->reset();

            return $organization;
        } catch (\Exception $e) {
            Log::error('Failed to create organization', [
                'user_id' => auth()->id(),
                'data' => [
                    'name' => $this->name,
                    'code' => $this->code,
                ],
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function update(): Organization
    {
        $this->validate();

        try {
            $this->organization->update([
                'name' => $this->name,
                'description' => $this->description,
                'code' => $this->code,
                'is_active' => $this->is_active,
            ]);

            Log::info('Organization updated via form', [
                'organization_id' => $this->organization->id,
                'organization_name' => $this->organization->name,
                'user_id' => auth()->id(),
            ]);

            $organization = $this->organization;
            $this->reset();

            return $organization;
        } catch (\Exception $e) {
            Log::error('Failed to update organization', [
                'organization_id' => $this->organization?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function delete(): bool
    {
        if (!$this->organization) {
            Log::warning('Delete attempt with no organization set', ['user_id' => auth()->id()]);
            return false;
        }

        try {
            $orgId = $this->organization->id;
            $orgName = $this->organization->name;

            $this->organization->delete();

            Log::info('Organization deleted via form', [
                'organization_id' => $orgId,
                'organization_name' => $orgName,
                'user_id' => auth()->id(),
            ]);

            $this->reset();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete organization', [
                'organization_id' => $this->organization?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}

