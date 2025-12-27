<div>
    {{-- =================================================================================== --}}
    {{-- HEADER & ACTION BAR (STICKY) --}}
    {{-- =================================================================================== --}}
    <div class="sticky top-0 z-10 bg-gray-100 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
        <header class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                {{-- Judul Halaman dan Tombol Kembali --}}
                <div class="flex items-center gap-4">
                    <x-button size="sm" tag="a" href="{{ route('admin.articles.index') }}" size="sm"
                        variant="secondary" class="!p-2">
                        <x-heroicon-s-arrow-left class="h-3 w-3" />
                        <span class="sr-only">Kembali</span>
                    </x-button>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                            {{ $form->article ? 'Edit Artikel' : 'Buat Artikel Baru' }}
                        </h1>
                        @if ($form->article)
                            <div class="text-xs text-gray-500">
                                Terakhir disimpan: {{ $form->article->updated_at->format('d M Y, H:i') }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Tombol Aksi Utama --}}
                <div class="flex items-center gap-3">
                    <x-button size="sm" type="button" variant="secondary" wire:click="preview"
                        x-on:open-preview.window="window.open($event.detail.url, '_blank')">
                        <x-heroicon-o-eye class="h-4 w-3 mr-1" />
                        Preview
                    </x-button>
                    <x-button size="sm" type="button" wire:click="saveAsDraft" variant="secondary">
                        Simpan Draft
                    </x-button>

                    @if ($form->article && $form->article->is_published)
                        <x-button size="sm" type="button" wire:click="saveAndPublish">
                            Update
                        </x-button>
                    @else
                        <x-button size="sm" type="button" wire:click="saveAndPublish">
                            Publish
                        </x-button>
                    @endif
                </div>
            </div>
        </header>
    </div>

    {{-- =================================================================================== --}}
    {{-- MAIN FORM CONTENT --}}
    {{-- =================================================================================== --}}
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">

            {{-- Kolom Kiri: Konten Utama --}}
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800 dark:ring-1 dark:ring-gray-700">
                    <div>
                        <x-input-label for="title" :value="__('Judul Artikel')" />
                        <x-text-input wire:model.lazy="form.title" id="title" class="block mt-1 w-full text-lg"
                            type="text" name="title" required />
                        <x-input-error :messages="$errors->get('form.title')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="slug" :value="__('Slug')" />
                        <div class="flex items-center mt-1">
                            <span
                                class="text-sm text-gray-500 bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-l-md border border-r-0 dark:border-gray-600">
                                {{ url('/articles/') }}/
                            </span>
                            <x-text-input wire:model="form.slug" id="slug" class="block w-full rounded-l-none"
                                type="text" name="slug" />
                        </div>
                        <x-input-error :messages="$errors->get('form.slug')" class="mt-2" />
                    </div>
                </div>

                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800 dark:ring-1 dark:ring-gray-700">
                    <div class="p-6">
                        <x-input-label for="content" :value="__('Konten')" class="mb-1" />
                        <x-trix-editor entangle="form.content" allowFileUploads />
                        <x-input-error :messages="$errors->get('form.content')" class="mt-2" />
                    </div>
                </div>
                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800 dark:ring-1 dark:ring-gray-700">
                    <div class="p-6">
                        <x-input-label for="excerpt" :value="__('Excerpt / Kutipan Singkat')" class="mb-1" />
                        <textarea wire:model.lazy="form.excerpt" id="excerpt" rows="4"
                            class="form-textarea block w-full dark:bg-gray-900 dark:border-gray-600 rounded-md shadow-sm"></textarea>
                        <x-input-error :messages="$errors->get('form.excerpt')" class="mt-2" />
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Sidebar Pengaturan --}}
            <div class="space-y-6 lg:col-span-1">
                {{-- Status Card --}}
                <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800 dark:ring-1 dark:ring-gray-700">
                    <h3 class="font-semibold mb-4 text-gray-800 dark:text-gray-200">Status</h3>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Status Publikasi</span>
                        @if ($form->is_published)
                            <x-status-badge :status="App\Enums\ArticleStatus::PUBLISHED" />
                        @else
                            <x-status-badge :status="App\Enums\ArticleStatus::DRAFT" />
                        @endif
                    </div>
                    @if ($form->article?->published_at)
                        <div class="flex items-center justify-between mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <span>Tanggal Publikasi</span>
                            <span>{{ $form->article->published_at->format('d M Y') }}</span>
                        </div>
                    @endif
                </div>

                {{-- Kategori & Tags Card --}}
                <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800 dark:ring-1 dark:ring-gray-700">
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="category_id" :value="__('Kategori')" />
                            <x-select wire:model="form.category_id" id="category_id" class="mt-1 w-full">
                                <option value="">Pilih Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </x-select>
                            <x-input-error :messages="$errors->get('form.category_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="tags" :value="__('Tags')" />
                            <livewire:components.select wire:model="form.tags" model="{{ \App\Models\Tag::class }}"
                                displayColumn="name" searchColumn="name" :initialSelected="$form->tags" :key="'tags-select-' . ($article?->id ?? 'new')" />
                            <x-input-error :messages="$errors->get('form.tags')" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- Featured Image Card --}}
                <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800 dark:ring-1 dark:ring-gray-700"
                    x-data="{
                        imageError: '',
                        isValidating: false,
                        validateImage(input) {
                            this.imageError = '';
                            this.isValidating = true;
                            
                            const file = input.files[0];
                            if (!file) {
                                this.isValidating = false;
                                return;
                            }
                            
                            // Check file size (2MB max)
                            const maxSize = 2 * 1024 * 1024;
                            if (file.size > maxSize) {
                                this.imageError = 'Ukuran file terlalu besar. Maksimal 2MB.';
                                input.value = '';
                                this.isValidating = false;
                                return;
                            }
                            
                            // Check image dimensions
                            const img = new Image();
                            img.onload = () => {
                                const width = img.width;
                                const height = img.height;
                                const ratio = width / height;
                                
                                if (width <= height) {
                                    this.imageError = `Gambar harus horizontal (landscape). Ukuran saat ini: ${width}×${height}px.`;
                                    input.value = '';
                                } else if (ratio < 1.5) {
                                    this.imageError = `Rasio gambar terlalu kotak. Minimal 3:2. Rasio saat ini: ${ratio.toFixed(2)}:1.`;
                                    input.value = '';
                                } else if (ratio > 3.0) {
                                    this.imageError = `Rasio gambar terlalu lebar. Maksimal 3:1. Rasio saat ini: ${ratio.toFixed(2)}:1.`;
                                    input.value = '';
                                } else {
                                    // Valid - trigger Livewire upload
                                    this.imageError = '';
                                    @this.upload('form.image', file);
                                }
                                this.isValidating = false;
                            };
                            img.onerror = () => {
                                this.imageError = 'File yang dipilih bukan gambar yang valid.';
                                input.value = '';
                                this.isValidating = false;
                            };
                            img.src = URL.createObjectURL(file);
                        }
                    }">
                    <h3 class="font-semibold mb-4 text-gray-800 dark:text-gray-200">Gambar Utama</h3>
                    <div class="mt-2">
                        @if ($form->image)
                            <img src="{{ $form->image->temporaryUrl() }}" alt="Preview"
                                class="w-full h-auto rounded-lg">
                        @elseif($form->featured_image)
                            <img src="{{ asset('storage/' . $form->featured_image) }}" alt="Featured Image"
                                class="w-full h-auto rounded-lg">
                        @endif

                        <div class="mt-4">
                            <input type="file" id="image-upload-input"
                                accept="image/jpeg,image/png,image/gif,image/webp" class="hidden"
                                @change="validateImage($event.target)">
                            <x-button size="sm" type="button"
                                onclick="document.getElementById('image-upload-input').click()" variant="secondary"
                                class="w-full" x-bind:disabled="isValidating">
                                <span x-show="!isValidating">Upload Gambar</span>
                                <span x-show="isValidating" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Memvalidasi...
                                </span>
                            </x-button>
                            @if ($form->image || $form->featured_image)
                                <x-button size="sm" type="button" wire:click="removeImage" variant="danger"
                                    class="w-full mt-2">
                                    Hapus Gambar
                                </x-button>
                            @endif
                        </div>
                        
                        {{-- Frontend Validation Error --}}
                        <div x-show="imageError" x-cloak class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                            <p class="text-sm text-red-600 dark:text-red-400" x-text="imageError"></p>
                        </div>
                        
                        {{-- Backend Validation Error --}}
                        <x-input-error :messages="$errors->get('form.image')" class="mt-2" />

                        {{-- Image Guidelines --}}
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <p class="text-xs font-medium text-blue-800 dark:text-blue-300 mb-1">📐 Panduan Gambar:</p>
                            <ul class="text-xs text-blue-700 dark:text-blue-400 space-y-0.5">
                                <li>• Ukuran rekomendasi: <strong>1920 × 1080 px</strong> (16:9)</li>
                                <li>• Rasio yang diterima: <strong>3:2</strong> hingga <strong>3:1</strong></li>
                                <li>• Orientasi: <strong>Horizontal (landscape) saja</strong></li>
                                <li>• Format: JPG, PNG, GIF, WebP (max 2MB)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Aksi Berbahaya Card --}}
                @if ($form->article)
                    <div
                        class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800 dark:ring-1 dark:ring-gray-700 border border-red-300 dark:border-red-700">
                        <h3 class="font-semibold mb-4 text-red-600 dark:text-red-400">Zona Berbahaya</h3>
                        <div class="space-y-3">
                            @if ($form->article->is_published)
                                <x-button size="sm" type="button" wire:click="unpublish" variant="secondary"
                                    class="w-full">
                                    Batalkan Publikasi
                                </x-button>
                            @endif
                            <x-button size="sm" type="button" wire:click="confirmDelete" variant="danger"
                                class="w-full">
                                Hapus Artikel
                            </x-button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- =================================================================================== --}}
    {{-- MODALS --}}
    {{-- =================================================================================== --}}
    <x-modal name="preview-modal" max-width="4xl">
        <div class="p-6 bg-white dark:bg-gray-800">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                {{ $form->title ?: 'Judul Artikel Anda' }}</h2>
            <div class="prose dark:prose-invert max-w-none">
                {!! $form->content !!}
            </div>
            <div class="mt-6 pt-4 border-t dark:border-gray-700">
                <x-button size="sm" type="button" variant="secondary" @click="$dispatch('close')">
                    Tutup Preview
                </x-button>
            </div>
        </div>
    </x-modal>

    <x-modal name="delete-article-confirmation" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Apakah Anda yakin ingin menghapus artikel ini?
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Aksi ini tidak dapat dibatalkan. Artikel akan dipindahkan ke sampah (bisa dihapus permanen nanti).
            </p>
            <div class="mt-6 flex justify-end">
                <x-button size="sm" type="button" variant="secondary" @click="$dispatch('close')">
                    Batal
                </x-button>
                <x-button size="sm" class="ms-3" type="button" variant="danger" wire:click="delete">
                    Ya, Hapus
                </x-button>
                <x-button size="sm" class="ms-3" type="button" variant="danger" wire:click="forceDelete">
                    Hapus Permanen
                </x-button>
            </div>
        </div>
    </x-modal>
</div>
