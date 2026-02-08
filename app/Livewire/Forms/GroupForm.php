<?php

namespace App\Livewire\Forms;

use App\Models\Group;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Form;

class GroupForm extends Form
{
    public ?Group $group;

    #[Validate('required')]
    public string $name = '';

    public function setGroup(?Group $group)
    {
        $this->group = $group;
        $this->name = $group->name;
    }

    public function store(): Group
    {
        $this->validate();
        try {
            $group = Group::create([
                'name' => $this->name,
            ]);

            Log::info('Group created via form', [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'user_id' => auth()->id(),
            ]);

            $this->reset();

            return $group;
        } catch (\Exception $e) {
            Log::error('Failed to create group', [
                'user_id' => auth()->id(),
                'name' => $this->name,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function update(): Group
    {
        $this->validate();
        try {
            $this->group->update([
                'name' => $this->name,
            ]);

            Log::info('Group updated via form', [
                'group_id' => $this->group->id,
                'group_name' => $this->group->name,
                'user_id' => auth()->id(),
            ]);

            $group = $this->group;
            $this->reset();

            return $group;
        } catch (\Exception $e) {
            Log::error('Failed to update group', [
                'group_id' => $this->group?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function delete(): bool
    {
        if (!$this->group) {
            Log::warning('Delete attempt with no group set', ['user_id' => auth()->id()]);
            return false;
        }

        try {
            $groupId = $this->group->id;
            $groupName = $this->group->name;

            $this->group->delete();

            Log::info('Group deleted via form', [
                'group_id' => $groupId,
                'group_name' => $groupName,
                'user_id' => auth()->id(),
            ]);

            $this->reset();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete group', [
                'group_id' => $this->group?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}

