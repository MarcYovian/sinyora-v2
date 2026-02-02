<?php

namespace App\Livewire\Admin\Pages\Asset;

use App\Livewire\Forms\AssetForm;
use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithFileUploads, AuthorizesRequests;

    #[Layout('layouts.app')]

    public AssetForm $form;
    public $categories;
    public $search = '';
    public $editId = null;
    public $deleteId = null;

    public function mount()
    {
        $this->authorize('access', 'admin.assets.index');

        $this->categories = AssetCategory::all();
    }

    public function updatedFormImage()
    {
        Log::info('Upload masuk', [
            'token_match' => request()->session()->token() === request()->header('X-CSRF-TOKEN'),
            'session_exists' => session()->has('_token'),
            'csrf_token' => request()->session()->token(),
            'file_sementara' => [
                'originalName' => $this->form->image->getClientOriginalName(),
                'size' => $this->form->image->getSize(),
                'mime' => $this->form->image->getMimeType(),
            ]
        ]);
    }

    public function updatedFormName()
    {
        $this->form->slug = str($this->form->name)->slug();
    }

    public function create()
    {
        $this->authorize('access', 'admin.assets.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'asset-modal');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.assets.edit');

        $this->editId = $id;
        $asset = Asset::find($id);
        $this->form->setAsset($asset);
        $this->dispatch('open-modal', 'asset-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.assets.edit');

            $this->form->update();
            $this->editId = null;
            flash()->success('Category updated successfully');
        } else {
            $this->authorize('access', 'admin.assets.create');

            $this->form->store();
            flash()->success('Category created successfully');
        }

        $this->dispatch('close-modal', 'asset-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.assets.destroy');

        $this->deleteId = $id;
        $asset = Asset::find($id);
        $this->form->setAsset($asset);
        $this->dispatch('open-modal', 'delete-asset-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.assets.destroy');

        if ($this->deleteId) {
            $this->form->delete();
            $this->deleteId = null;
            flash()->success('Category deleted successfully');
        }
        $this->dispatch('close-modal', 'delete-asset-confirmation');
    }

    public function removeImage()
    {
        $this->form->removeImage();
    }

    public function render()
    {
        $this->authorize('access', 'admin.assets.index');

        $table_heads = ['#', 'image', 'Name', 'Code', 'Quantity', 'Storage', 'Status', 'Actions'];

        $assets = Asset::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%')
                ->orWhere('storage_location', 'like', '%' . $this->search . '%');
        })->latest()->paginate(5);

        return view('livewire.admin.pages.asset.index', [
            'table_heads' => $table_heads,
            'assets' => $assets
        ]);
    }
}
