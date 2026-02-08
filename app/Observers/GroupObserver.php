<?php

namespace App\Observers;

use App\Models\Group;
use Illuminate\Support\Facades\Log;

class GroupObserver
{
    /**
     * Handle the Group "created" event.
     */
    public function created(Group $group): void
    {
        Log::info('Group created', [
            'id' => $group->id,
            'name' => $group->name,
        ]);
    }

    /**
     * Handle the Group "updated" event.
     */
    public function updated(Group $group): void
    {
        Log::info('Group updated', [
            'id' => $group->id,
            'name' => $group->name,
            'changes' => $group->getChanges(),
        ]);
    }

    /**
     * Handle the Group "deleted" event.
     */
    public function deleted(Group $group): void
    {
        Log::info('Group deleted', [
            'id' => $group->id,
            'name' => $group->name,
        ]);
    }

    /**
     * Handle the Group "restored" event.
     */
    public function restored(Group $group): void
    {
        Log::info('Group restored', ['id' => $group->id]);
    }

    /**
     * Handle the Group "force deleted" event.
     */
    public function forceDeleted(Group $group): void
    {
        Log::info('Group force deleted', ['id' => $group->id]);
    }
}
