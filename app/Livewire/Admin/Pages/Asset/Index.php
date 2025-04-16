<?php

namespace App\Livewire\Admin\Pages\Asset;

use App\Livewire\Forms\AssetForm;
use App\Models\Asset;
use App\Models\AssetCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    #[Layout('layouts.app')]

    public AssetForm $form;
    public $categories;
    public $search = '';
    public $editId = null;
    public $deleteId = null;

    public function mount()
    {
        $this->categories = AssetCategory::all();
    }
    public function create()
    {
        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'asset-modal');
    }

    public function edit($id)
    {
        $this->editId = $id;
        $asset = Asset::find($id);
        $this->form->setAsset($asset);
        $this->dispatch('open-modal', 'asset-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->form->update();
            $this->editId = null;
            toastr()->success('Category updated successfully');
        } else {
            $this->form->store();
            toastr()->success('Category created successfully');
        }
        $this->dispatch('close-modal', 'asset-modal');
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $asset = Asset::find($id);
        $this->form->setAsset($asset);
        $this->dispatch('open-modal', 'delete-asset-confirmation');
    }

    public function delete()
    {
        if ($this->deleteId) {
            $this->form->delete();
            $this->deleteId = null;
            toastr()->success('Category deleted successfully');
        }
        $this->dispatch('close-modal', 'delete-asset-confirmation');
    }

    public function removeImage()
    {
        $this->form->removeImage();
    }

    public function render()
    {
        $table_heads = ['#', 'image', 'Name', 'Code', 'Quantity', 'Storage', 'Status', 'Actions'];

        $assets = Asset::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->latest()->paginate(5);
        return view('livewire.admin.pages.asset.index', [
            'table_heads' => $table_heads,
            'assets' => $assets
        ]);
    }
}
