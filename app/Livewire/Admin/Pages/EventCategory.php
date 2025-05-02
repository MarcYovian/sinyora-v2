<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\EventCategoryForm;
use App\Models\EventCategory as ModelsEventCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class EventCategory extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public EventCategoryForm $form;
    public $search = '';
    public $editId = null;
    public $deleteId = null;

    public function create()
    {
        $this->authorize('access', 'admin.event-categories.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'category-modal');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.event-categories.edit');

        $this->editId = $id;
        $category = ModelsEventCategory::find($id);
        $this->form->setCategory($category);
        $this->dispatch('open-modal', 'category-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.event-categories.edit');

            $this->form->update();
            $this->editId = null;
            $this->dispatch('updateSuccess');
        } else {
            $this->authorize('access', 'admin.event-categories.create');

            $this->form->store();
            $this->dispatch('createSuccess');
        }
        $this->dispatch('close-modal', 'category-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.event-categories.destroy');

        $this->deleteId = $id;
        $category = ModelsEventCategory::find($id);
        $this->form->setCategory($category);
        $this->dispatch('open-modal', 'delete-category-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.event-categories.destroy');

        if ($this->deleteId) {
            $this->form->delete();
            $this->deleteId = null;
            $this->dispatch('deleteSuccess');
        }
        $this->dispatch('close-modal', 'delete-category-confirmation');
    }

    public function render()
    {
        $this->authorize('access', 'admin.event-categories.index');

        $table_heads = ['#', 'Name', 'Color', 'Status', 'Actions'];

        $categories = ModelsEventCategory::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->latest()->paginate(5);
        return view('livewire.admin.pages.event-category', [
            'categories' => $categories,
            'table_heads' => $table_heads,
        ]);
    }
}
