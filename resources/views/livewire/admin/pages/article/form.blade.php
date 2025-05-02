<div>
    <header>
        <div class="max-w-7xl mx-auto py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                    {{ __('Buat Artikel Baru') }}
                </h2>
                <x-button tag="a" href="{{ route('admin.articles.index') }}" size="sm" variant="secondary"
                    class="flex items-center gap-2">
                    <x-heroicon-s-arrow-left class="h-4 w-4" />
                    Kembali
                </x-button>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto py-6">
        <form wire:submit.prevent="save" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left Column (Content) -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Main Content Card -->
                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800 dark:ring-1 dark:ring-gray-700">
                    <div class="p-6">
                        <!-- Title and Slug -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Title -->
                            <div>
                                <x-input-label for="title" :value="__('Judul Artikel')" />
                                <x-text-input wire:model.lazy="form.title" id="title" type="text"
                                    class="mt-2 w-full text-lg" placeholder="Judul Artikel" />
                                <x-input-error :messages="$errors->get('form.title')" />
                            </div>

                            <!-- Slug -->
                            <div>
                                <x-input-label for="slug" :value="__('Slug URL')" />
                                <x-text-input wire:model="form.slug" id="slug" type="text"
                                    class="mt-2 w-full text-lg" placeholder="judul-artikel" />
                                <x-input-error :messages="$errors->get('form.slug')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Content Editor -->
                        <div class="mt-6">
                            <x-input-label for="content" :value="__('Konten Artikel')" />
                            <div class="mt-2">
                                <x-trix-editor entangle="form.content" allowFileUploads />
                                <x-input-error :messages="$errors->get('form.content')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Excerpt -->
                        <div class="mt-6">
                            <x-input-label for="excerpt" :value="__('Ringkasan Artikel')" />
                            <textarea wire:model.live="form.excerpt" id="excerpt" rows="3"
                                class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-primary-600 dark:focus:ring-primary-600"
                                placeholder="Ringkasan singkat yang akan muncul di halaman daftar artikel"></textarea>
                            <div class="mt-1 flex justify-between">
                                <x-input-error :messages="$errors->get('form.excerpt')" />
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ strlen($form->excerpt) }}/200 karakter
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column (Metadata) -->
            <div class="space-y-6">
                <!-- Thumbnail Card -->
                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800 dark:ring-1 dark:ring-gray-700">
                    <div class="p-6">
                        <x-input-label for="thumbnail" :value="__('Thumbnail Artikel')" />
                        <div x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true"
                            x-on:livewire-upload-finish="isUploading = false"
                            x-on:livewire-upload-error="isUploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress" class="mt-4">
                            <div
                                class="group relative aspect-[16/9] w-full overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700">
                                <!-- Loading Indicator -->
                                <div x-show="isUploading" class="absolute right-2 top-2 z-10">
                                    <div
                                        class="h-6 w-6 animate-spin rounded-full border-2 border-white border-t-primary-500">
                                    </div>
                                </div>

                                <!-- Cancel/Remove Image Button (shown when image exists) -->
                                @if ($form->featured_image || $form->image)
                                    <button wire:click="removeImage" type="button"
                                        class="absolute right-2 top-2 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-red-500 p-1 text-white hover:bg-red-600 focus:outline-none">
                                        <x-heroicon-s-x-mark class="h-5 w-5" />
                                    </button>
                                @endif

                                @if ($form->featured_image || $form->image)
                                    <img class="h-full w-full object-cover object-center"
                                        src="{{ $form->image ? $form->image->temporaryUrl() : asset("storage/{$form->featured_image}") }}"
                                        alt="Thumbnail" />
                                @else
                                    <div class="flex h-full items-center justify-center text-gray-400">
                                        <x-heroicon-s-photo class="h-12 w-12" />
                                    </div>
                                @endif
                            </div>
                            <label for="image" class="mt-4 block w-full cursor-pointer">
                                <x-text-input wire:model="form.image" id="image" type="file" class="hidden"
                                    accept="image/jpeg,image/png" x-bind:disabled="isUploading" />
                                <div
                                    class="flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                    <x-heroicon-s-cloud-arrow-up class="mr-2 h-5 w-5" />
                                    {{ __('Upload Thumbnail') }}
                                </div>
                            </label>
                            <p class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">
                                Ukuran rekomendasi: 1200x630px <br>(format JPG/PNG, maksimal 2MB)
                            </p>
                            <x-input-error :messages="$errors->get('form.image')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Category & Tags Card -->
                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800 dark:ring-1 dark:ring-gray-700">
                    <div class="p-6">
                        <div class="space-y-6">
                            <!-- Category -->
                            <div>
                                <x-input-label for="category" :value="__('Kategori')" />
                                <x-select wire:model="form.category_id" id="category" class="mt-2 w-full">
                                    <option value="">{{ __('Pilih Kategori') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </x-select>
                                <x-input-error :messages="$errors->get('form.category_id')" class="mt-2" />
                            </div>

                            <!-- Tags -->
                            <div>
                                <x-input-label for="tags" :value="__('Tag')" />
                                <x-select wire:model="form.tags" id="tags" class="mt-2 w-full" multiple>
                                    @foreach ($tags as $tag)
                                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                    @endforeach
                                </x-select>
                                <x-input-error :messages="$errors->get('form.tags')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status & Actions Card -->
                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800 dark:ring-1 dark:ring-gray-700">
                    <div class="p-6">
                        <!-- Actions -->
                        <div class="space-y-3 ">
                            @if (!$form->published_at)
                                <x-button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="save">
                                        {{ __('Publikasikan Artikel') }}
                                    </span>
                                    <span wire:loading wire:target="save"
                                        class="flex items-center justify-center gap-2">
                                        <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                                        {{ __('Mempublikasikan...') }}
                                    </span>
                                </x-button>
                            @else
                                <x-button type="button" variant="primary" class="w-full"
                                    wire:loading.attr="disabled" wire:click="unpublish">
                                    <span wire:loading.remove wire:target="unpublish">
                                        {{ __('Unpublikasikan Artikel') }}
                                    </span>
                                    <span wire:loading wire:target="unpublish"
                                        class="flex items-center justify-center gap-2">
                                        <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                                        {{ __('Memunpublikasikan...') }}
                                    </span>
                                </x-button>
                            @endif

                            <x-button type="button" variant="warning" class="w-full" wire:click="saveDraft"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="saveDraft">Simpan Draft</span>
                                <span wire:loading wire:target="saveDraft"
                                    class="flex items-center justify-center gap-2">
                                    <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                                    Menyimpan...
                                </span>
                            </x-button>

                            <x-button type="button" variant="info" class="w-full" wire:click="preview"
                                wire:loading.attr="disabled">
                                <span>{{ __('Preview') }}</span>
                            </x-button>

                            @can('delete article')
                                @if ($form->article)
                                    <x-button type="button" variant="danger" class="w-full" wire:click="confirmDelete">
                                        {{ __('Hapus Artikel') }}
                                    </x-button>
                                @endif
                            @endcan

                            <x-button variant="secondary" class="w-full" href="{{ route('admin.articles.index') }}">
                                Batal
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <x-modal name="preview-modal" maxWidth="7xl">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-xl">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <x-heroicon-s-eye class="h-6 w-6 text-primary-500" />
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                        Preview Artikel
                    </h3>
                </div>
                <button type="button" @click="$dispatch('close')"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors duration-200">
                    <x-heroicon-s-x-mark class="h-6 w-6" />
                </button>
            </div>

            <!-- Modal Content -->
            <div class="mt-6 space-y-6 overflow-y-auto max-h-[70vh]">
                <!-- Thumbnail Preview -->
                @if ($form->featured_image || $form->image)
                    <div
                        class="relative aspect-[16/9] w-full overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700 shadow">
                        <img class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                            src="{{ $form->image ? $form->image->temporaryUrl() : $form->featured_image }}"
                            alt="Thumbnail Preview">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    </div>
                @endif

                <!-- Article Meta -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-s-bookmark class="h-5 w-5 text-gray-400" />
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Status</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                @if ($form->is_published)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Published
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        Draft
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <x-heroicon-s-tag class="h-5 w-5 text-gray-400" />
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Kategori</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ $this->categories->find($form->category_id)?->name ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <x-heroicon-s-hashtag class="h-5 w-5 text-gray-400" />
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Tags</p>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @forelse($this->tags->whereIn('id', $form->tags) as $tag)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $tag->name }}
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Article Content -->
                <div
                    class="prose prose-lg max-w-none dark:prose-invert prose-headings:font-bold prose-a:text-primary-600 hover:prose-a:text-primary-500 dark:prose-a:text-primary-400 dark:hover:prose-a:text-primary-300 prose-img:rounded-lg prose-img:shadow">
                    <h1
                        class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-6 pb-2 border-b dark:border-gray-700">
                        {{ $form->title }}
                    </h1>

                    @if ($form->excerpt)
                        <div
                            class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg mb-6 border-l-4 border-primary-500 dark:border-primary-400">
                            <p class="text-gray-600 dark:text-gray-300 italic font-medium">{{ $form->excerpt }}</p>
                        </div>
                    @endif

                    <div
                        class="mt-6 prose prose-lg max-w-none dark:prose-invert
                      prose-headings:font-bold prose-headings:text-gray-800 dark:prose-headings:text-gray-100
                      prose-h1:text-3xl prose-h1:md:text-4xl prose-h1:border-b prose-h1:pb-2 prose-h1:mb-6 prose-h1:border-gray-200 dark:prose-h1:border-gray-700
                      prose-h2:text-2xl prose-h2:md:text-3xl prose-h2:mt-8 prose-h2:mb-4
                      prose-h3:text-xl prose-h3:md:text-2xl prose-h3:mt-6 prose-h3:mb-3
                      prose-p:text-gray-600 dark:prose-p:text-gray-300 prose-p:mb-4
                      prose-a:text-primary-600 hover:prose-a:text-primary-500 dark:prose-a:text-primary-400 dark:hover:prose-a:text-primary-300 prose-a:underline prose-a:underline-offset-4
                      prose-strong:text-gray-800 dark:prose-strong:text-gray-200
                      prose-em:italic
                      prose-blockquote:border-l-4 prose-blockquote:border-primary-500 dark:prose-blockquote:border-primary-400 prose-blockquote:pl-4 prose-blockquote:py-1 prose-blockquote:text-gray-600 dark:prose-blockquote:text-gray-400
                      prose-ul:list-disc prose-ul:pl-6 prose-ul:my-4
                      prose-ol:list-decimal prose-ol:pl-6 prose-ol:my-4
                      prose-li:my-1
                      prose-img:rounded-lg prose-img:shadow-md prose-img:mx-auto prose-img:my-6
                      prose-table:w-full prose-table:my-6
                      prose-th:bg-gray-100 dark:prose-th:bg-gray-700 prose-th:p-3 prose-th:text-left
                      prose-td:p-3 prose-td:border-t prose-td:border-gray-200 dark:prose-td:border-gray-700
                      prose-pre:bg-gray-800 prose-pre:rounded-lg prose-pre:p-4 prose-pre:overflow-x-auto
                      prose-code:bg-gray-100 dark:prose-code:bg-gray-700 prose-code:px-2 prose-code:py-1 prose-code:rounded prose-code:text-sm
                      prose-hr:my-8 prose-hr:border-gray-200 dark:prose-hr:border-gray-700">
                        {!! $form->content !!}
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="mt-6 flex justify-end border-t pt-4 dark:border-gray-700">
                <x-button type="button" variant="secondary" @click="$dispatch('close')">
                    Tutup Preview
                </x-button>
            </div>
        </div>
    </x-modal>

    {{-- Modal Delete Confirmation --}}
    <x-modal name="delete-article-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this article?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Please confirm that you want to delete this article by clicking the button below.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-button type="button" variant="secondary" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-button>

                <x-button class="ms-3" type="button" variant="danger" wire:click="forceDelete">
                    {{ __('Delete Permanently') }}
                </x-button>

                <x-button class="ms-3" type="submit" variant="primary">
                    {{ __('Delete') }}
                </x-button>
            </div>
        </form>
    </x-modal>
</div>
