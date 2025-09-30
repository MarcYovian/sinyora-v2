<?php

namespace App\Livewire\Admin\Pages\Content;

use App\Services\ContentService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Manage Home Content')]
class Home extends Component
{
    use WithFileUploads;

    public $content = [];
    public $uploads = [];

    public function mount(ContentService $contentService)
    {
        // The service is responsible for fetching and grouping the content
        $this->content = $contentService->getGroupedContent('home');
    }

    public function save(ContentService $contentService)
    {
        $this->validate([
            // Validasi file upload tetap sama
            'uploads.*.*' => 'nullable|image|max:2048',
            // Tambahkan validasi dasar untuk konten teks
            'content.*.*.value' => 'nullable|string',
        ]);

        $updates = [];

        foreach ($this->content as $section => $fields) {
            foreach ($fields as $key => $field) {
                $newValue = $field['value'];

                // Best Practice: Penanganan File Upload
                if ($field['type'] === 'image' && isset($this->uploads[$section][$key])) {
                    $file = $this->uploads[$section][$key];

                    // 1. Hapus file lama jika ada
                    if (!empty($field['value']) && Storage::disk('public')->exists(str_replace('storage/', '', $field['value']))) {
                        Storage::disk('public')->delete(str_replace('storage/', '', $field['value']));
                    }

                    // 2. Simpan file baru & dapatkan path relatif
                    $relativePath = $file->store('content', 'public');

                    // 3. Simpan path yang dapat diakses publik
                    $newValue = 'storage/' . $relativePath;
                }

                // Hanya tambahkan ke array jika nilainya berubah
                // Ini mencegah update yang tidak perlu jika hanya upload gambar
                if ($newValue !== $field['value']) {
                    $updates[] = [
                        'id' => $field['id'],
                        'value' => $newValue,
                    ];
                } else if ($field['type'] !== 'image') {
                    // untuk field non-gambar, selalu sertakan
                    $updates[] = [
                        'id' => $field['id'],
                        'value' => $newValue,
                    ];
                }
            }
        }

        // Panggil service yang sudah di-refactor
        if (!empty($updates)) {
            $contentService->updateContent('home', $updates);
        }

        $this->mount($contentService);
        $this->uploads = [];

        $this->dispatch('notify', title: 'Success', message: 'Home page content has been updated successfully.');
    }

    public function render()
    {
        return view('livewire.admin.pages.content.home');
    }
}
