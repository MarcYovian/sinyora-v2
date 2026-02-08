<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\GroupForm;
use App\Models\Group;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Groups extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public GroupForm $form;

    #[Url(as: 'q')]
    public $search = '';

    public $editId = null;
    public $deleteId = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->authorize('access', 'admin.groups.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'group-modal');
    }

    public function edit($id)
    {
        try {
            $this->authorize('access', 'admin.groups.edit');

            $group = Group::findOrFail($id);
            $this->editId = $id;
            $this->form->setGroup($group);
            $this->dispatch('open-modal', 'group-modal');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Group not found for edit', ['group_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('Group not found.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized group edit attempt', ['group_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('You are not authorized to edit this group.');
        } catch (\Exception $e) {
            Log::error('Failed to load group for edit', [
                'group_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to load group. Please try again.');
        }
    }

    public function save()
    {
        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.groups.edit');

                $this->form->update();
                $this->editId = null;
                flash()->success('Group updated successfully');
                Log::info('Group updated via Livewire', ['user_id' => auth()->id()]);
            } else {
                $this->authorize('access', 'admin.groups.create');

                $this->form->store();
                flash()->success('Group created successfully');
                Log::info('Group created via Livewire', ['user_id' => auth()->id()]);
            }

            $this->dispatch('close-modal', 'group-modal');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized group save attempt', [
                'user_id' => auth()->id(),
                'edit_id' => $this->editId,
            ]);
            flash()->error('You are not authorized to perform this action.');
        } catch (\Exception $e) {
            Log::error('Failed to save group', [
                'user_id' => auth()->id(),
                'edit_id' => $this->editId,
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to save group. Please try again.');
        }
    }

    public function confirmDelete($id)
    {
        try {
            $this->authorize('access', 'admin.groups.destroy');

            $group = Group::findOrFail($id);
            $this->deleteId = $id;
            $this->form->setGroup($group);
            $this->dispatch('open-modal', 'delete-group-confirmation');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Group not found for delete confirmation', ['group_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('Group not found.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized group delete attempt', ['group_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('You are not authorized to delete this group.');
        } catch (\Exception $e) {
            Log::error('Failed to prepare group deletion', [
                'group_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to prepare deletion. Please try again.');
        }
    }

    public function delete()
    {
        try {
            $this->authorize('access', 'admin.groups.destroy');

            if ($this->deleteId) {
                $groupName = $this->form->name;
                $this->form->delete();
                $this->deleteId = null;

                flash()->success('Group deleted successfully');
                Log::info('Group deleted via Livewire', [
                    'group_name' => $groupName,
                    'user_id' => auth()->id(),
                ]);
            }

            $this->dispatch('close-modal', 'delete-group-confirmation');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized group delete attempt', [
                'user_id' => auth()->id(),
                'delete_id' => $this->deleteId,
            ]);
            flash()->error('You are not authorized to delete this group.');
        } catch (\Exception $e) {
            Log::error('Failed to delete group', [
                'user_id' => auth()->id(),
                'delete_id' => $this->deleteId,
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to delete group. Please try again.');
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('access', 'admin.groups.index');

        $table_heads = ['No', 'Name', 'Actions'];

        $groups = Group::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->latest()->paginate(5);

        return view('livewire.admin.pages.groups', [
            'groups' => $groups,
            'table_heads' => $table_heads,
        ]);
    }
}

