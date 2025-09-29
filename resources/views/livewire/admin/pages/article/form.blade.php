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
                    <x-button size="sm" type="button" variant="secondary" wire:click="preview">
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
                <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800 dark:ring-1 dark:ring-gray-700">
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
                            <input type="file" wire:model="form.image" id="image-upload"
                                accept="image/jpeg,image/png" class="hidden">
                            <x-button size="sm" type="button"
                                onclick="document.getElementById('image-upload').click()" variant="secondary"
                                class="w-full">
                                Upload Gambar
                            </x-button>
                            @if ($form->image || $form->featured_image)
                                <x-button size="sm" type="button" wire:click="removeImage" variant="danger"
                                    class="w-full mt-2">
                                    Hapus Gambar
                                </x-button>
                            @endif
                        </div>
                        <x-input-error :messages="$errors->get('form.image')" class="mt-2" />
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
