<?php

namespace App\Observers;

use App\Models\Organization;
use Illuminate\Support\Facades\Log;

class OrganizationObserver
{
    /**
     * Handle the Organization "created" event.
     */
    public function created(Organization $organization): void
    {
        Log::info('Organization created', [
            'id' => $organization->id,
            'name' => $organization->name,
            'code' => $organization->code,
        ]);
    }

    /**
     * Handle the Organization "updated" event.
     */
    public function updated(Organization $organization): void
    {
        Log::info('Organization updated', [
            'id' => $organization->id,
            'name' => $organization->name,
            'changes' => $organization->getChanges(),
        ]);
    }

    /**
     * Handle the Organization "deleted" event.
     */
    public function deleted(Organization $organization): void
    {
        Log::info('Organization deleted', [
            'id' => $organization->id,
            'name' => $organization->name,
        ]);
    }

    /**
     * Handle the Organization "restored" event.
     */
    public function restored(Organization $organization): void
    {
        Log::info('Organization restored', ['id' => $organization->id]);
    }

    /**
     * Handle the Organization "force deleted" event.
     */
    public function forceDeleted(Organization $organization): void
    {
        Log::info('Organization force deleted', ['id' => $organization->id]);
    }
}
