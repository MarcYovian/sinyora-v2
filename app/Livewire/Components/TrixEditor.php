<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class TrixEditor extends Component
{
    use WithFileUploads;

    #[Modelable()]
    public $value = '';

    #[Validate('file|image|max:2048')]
    public $file;

    public function completeFileUpload($attachmentId)
    {
        // Pastikan file ada sebelum diproses
        if (!$this->file instanceof TemporaryUploadedFile) {
            return;
        }

        // Simpan file ke storage/app/public/attachments
        $path = $this->file->store('attachments', 'public');
        $url = Storage::disk('public')->url($path);

        // Kirim event kembali ke browser untuk memberitahu Trix
        $this->dispatch('trix-upload-completed', [
            'url' => $url,
            'attachmentId' => $attachmentId
        ]);

        // Reset properti file setelah selesai untuk upload berikutnya
        $this->reset('file');
    }

    public function render()
    {
        return view('livewire.components.trix-editor');
    }
}
