<?php

namespace App\Livewire\Admin\Pages\Content;

use App\Services\ContentService;
use App\Services\ImageService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

#[Layout('layouts.app')]
#[Title('Manage Content Settings')]
class PageSettings extends Component
{
    use WithFileUploads;

    public $page = 'home';
    public $content = [];
    public $uploads = [];
    public $originalContent = []; // Track original values for dirty checking

    // List of available pages for the sidebar
    public $availablePages = [
        'home' => 'Home Page',
        'events' => 'Events Page',
        'articles' => 'Articles Page',
        'borrowing' => 'Borrowing Page',
        'about' => 'About Page',
    ];

    public function mount($page = 'home', ContentService $contentService)
    {
        if (!array_key_exists($page, $this->availablePages)) {
            $page = 'home';
        }

        $this->page = $page;
        $this->loadContent($contentService);
    }

    public function loadContent(ContentService $contentService)
    {
        $this->content = $contentService->getGroupedContent($this->page);

        // Store a snapshot of original values for dirty checking
        $this->originalContent = collect($this->content)
            ->map(fn($fields) => collect($fields)->map(fn($f) => $f['value'])->all())
            ->all();
    }

    public function updatedPage($value)
    {
        return $this->redirectRoute('admin.content.index', ['page' => $value], navigate: true);
    }

    /**
     * Build dynamic validation rules based on field types.
     */
    private function buildValidationRules(): array
    {
        $rules = [];

        foreach ($this->content as $section => $fields) {
            foreach ($fields as $key => $field) {
                $fieldPath = "content.{$section}.{$key}.value";

                $rules[$fieldPath] = match ($field['type']) {
                    'text' => ['nullable', 'string', 'max:255'],
                    'textarea' => ['nullable', 'string', 'max:5000'],
                    'image' => ['nullable'],
                    default => ['nullable', 'string'],
                };
            }
        }

        // Validate uploaded images
        $rules['uploads.*.*'] = ['nullable', 'image', 'max:2048'];

        return $rules;
    }

    public function save(ContentService $contentService, ImageService $imageService)
    {
        $this->validate($this->buildValidationRules());

        $updates = [];

        foreach ($this->content as $section => $fields) {
            foreach ($fields as $key => $field) {
                $newValue = $field['value'];
                $originalValue = $this->originalContent[$section][$key] ?? null;

                // Handle Image Uploads via ImageService — generate responsive thumbnails
                if ($field['type'] === 'image' && isset($this->uploads[$section][$key])) {
                    $file = $this->uploads[$section][$key];

                    // Delete old images (handles both legacy string and JSON array)
                    if (!empty($field['value'])) {
                        $oldValue = $field['value'];
                        $decoded = json_decode($oldValue, true);

                        if (is_array($decoded)) {
                            // JSON format: delete all responsive variants
                            foreach ($decoded as $oldPath) {
                                $imageService->delete(str_replace('storage/', '', $oldPath));
                            }
                        } else {
                            // Legacy single string format
                            $imageService->delete(str_replace('storage/', '', $oldValue));
                        }
                    }

                    // Generate 3 responsive thumbnails via ImageService
                    $thumbnails = $imageService->generateThumbnails($file, [
                        'mobile' => [480, null],
                        'tablet' => [768, null],
                        'desktop' => [1200, null],
                    ], [
                        'path' => 'content/' . $this->page,
                        'quality' => 85,
                        'format' => 'webp',
                        'resize_mode' => 'scale',
                    ]);

                    // Store as JSON with storage/ prefix
                    $newValue = json_encode(array_map(
                        fn($path) => 'storage/' . $path,
                        $thumbnails
                    ));
                }

                // Only add to updates if value actually changed (dirty check)
                if ($newValue !== $originalValue) {
                    $updates[] = [
                        'id' => $field['id'],
                        'value' => $newValue,
                    ];
                }
            }
        }

        if (!empty($updates)) {
            $contentService->updateContent($this->page, $updates);
        }

        // Reload content to reflect changes and clear uploads
        $this->loadContent($contentService);
        $this->uploads = [];

        flash()->success(Str::title($this->page) . ' content updated successfully.');
    }

    public function render()
    {
        return view('livewire.admin.pages.content.page-settings');
    }
}
