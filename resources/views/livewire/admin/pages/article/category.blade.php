<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen Kategori Artikel') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Kelola kategori untuk pengelompokan artikel berita.') }}
        </p>
    </header>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="p-4 sm:p-6 space-y-4">
            {{-- Top Actions Bar --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                @can('create article category')
                    <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                        <x-heroicon-s-plus class="w-5 h-5" />
                        <span>{{ __('Tambah Kategori') }}</span>
                    </x-button>
                @endcan

                <div class="flex-grow flex flex-col sm:flex-row items-center gap-3">
                    <div class="w-full sm:w-auto sm:flex-grow">
                        <x-text-input wire:model.live.debounce.300ms="search" type="text" class="w-full"
                            placeholder="{{ __('Cari nama kategori...') }}" />
                    </div>
                    @if ($search)
                        <x-button type="button" wire:click="resetFilters" variant="secondary" class="w-full sm:w-auto">
                            {{ __('Reset') }}
                        </x-button>
                    @endif
                </div>
            </div>

            {{-- Indikator Loading --}}
            <div wire:loading.flex wire:target="search" class="items-center justify-center w-full py-4">
                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                    <x-heroicon-s-arrow-path class="h-5 w-5 animate-spin" />
                    <span>Memuat data...</span>
                </div>
            </div>

            <div wire:loading.remove wire:target="search">
                {{-- Tampilan Mobile (Card) --}}
                <div class="grid grid-cols-1 gap-4 md:hidden">
                    @forelse ($categories as $category)
                        <div wire:key="category-card-{{ $category->id }}"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden ring-1 ring-black ring-opacity-5">
                            <div class="p-4 border-b dark:border-gray-700 flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">
                                        {{ $category->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 font-mono mt-1">
                                        {{ $category->slug }}
                                    </p>
                                </div>
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button
                                            class="p-1 text-gray-500 dark:text-gray-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                            <x-heroicon-s-ellipsis-vertical class="w-5 h-5" />
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        @can('edit article category')
                                            <x-dropdown-link wire:click="edit({{ $category->id }})">
                                                Edit Kategori
                                            </x-dropdown-link>
                                        @endcan
                                        <div class="border-t border-gray-100 dark:border-gray-600"></div>
                                        @can('delete article category')
                                            <x-dropdown-link wire:click="confirmDelete({{ $category->id }})"
                                                class="text-red-600 dark:text-red-500">Hapus Kategori
                                            </x-dropdown-link>
                                        @endcan
                                    </x-slot>
                                </x-dropdown>
                            </div>
                            <div class="p-4 space-y-2 text-sm bg-gray-50 dark:bg-gray-900/50">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500 dark:text-gray-400">Total Artikel:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $category->articles_count ?? 0 }} Artikel
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada data kategori.') }}
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan Desktop (Tabel) --}}
                <div class="hidden md:block">
                    <x-table title="Data Kategori Artikel" :heads="$table_heads">
                        @forelse ($categories as $key => $category)
                            <tr wire:key="category-table-{{ $category->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    {{ $key + $categories->firstItem() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900 dark:text-gray-200">{{ $category->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                                        {{ $category->slug }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $category->articles_count ?? 0 }} Artikel
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end space-x-1">
                                        @can('edit article category')
                                            <x-button type="button" variant="warning" size="sm" class="!p-2"
                                                wire:click="edit({{ $category->id }})" title="Edit Kategori">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                <span class="sr-only">Edit</span>
                                            </x-button>
                                        @endcan

                                        @can('delete article category')
                                            <x-button type="button" variant="danger" size="sm" class="!p-2"
                                                wire:click="confirmDelete({{ $category->id }})" title="Hapus Kategori">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                                <span class="sr-only">Delete</span>
                                            </x-button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($table_heads) }}"
                                    class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    {{ __('Tidak ada data yang tersedia.') }}
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>
            </div>
        </div>

        <div class="px-4 md:px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $categories->links() }}
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <x-modal name="category-modal" :show="$errors->isNotEmpty()" maxWidth="lg" focusable>
        <form wire:submit="save" class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <div class="flex items-start justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $editId ? __('Edit Kategori') : __('Tambah Kategori Baru') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Silakan lengkapi informasi kategori di bawah ini.') }}
                    </p>
                </div>
                <button type="button" @click="$dispatch('close')"
                    class="p-2 -m-2 text-gray-400 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-600 dark:hover:text-gray-200 transition-all">
                    <x-heroicon-s-x-mark class="h-6 w-6" />
                </button>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-5">
                <!-- Name Field -->
                <div>
                    <x-input-label for="name" value="{{ __('Nama Kategori') }}" />
                    <x-text-input wire:model.lazy="form.name" id="name" type="text"
                        class="mt-1 block w-full" placeholder="{{ __('e.g. Berita, Pengumuman') }}"
                        wire:blur="generateSlug" />
                    <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
                </div>

                <!-- Slug Field -->
                <div>
                    <x-input-label for="slug" value="{{ __('Slug') }}" />
                    <div class="flex gap-2">
                        <x-text-input wire:model="form.slug" id="slug" type="text"
                            class="mt-1 block w-full font-mono" placeholder="{{ __('e.g. berita, pengumuman') }}" />
                        <button type="button" wire:click="generateSlug"
                            class="mt-1 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition whitespace-nowrap">
                            {{ __('Generate') }}
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Slug akan di-generate otomatis dari nama jika dikosongkan.') }}</p>
                    <x-input-error :messages="$errors->get('form.slug')" class="mt-2" />
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')" class="justify-center">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button type="submit" class="justify-center">
                    <span wire:loading.remove wire:target="save">
                        {{ $editId ? __('Update Kategori') : __('Simpan Kategori') }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                        <span>{{ __('Menyimpan...') }}</span>
                    </span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-modal name="delete-category-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Hapus Kategori Artikel?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Tindakan ini tidak dapat dibatalkan.') }}
            </p>

            <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-xs font-medium text-red-500 dark:text-red-400 uppercase">{{ __('Nama') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-red-900 dark:text-red-200">{{ $form->name }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-xs font-medium text-red-500 dark:text-red-400 uppercase">{{ __('Slug') }}</dt>
                        <dd class="mt-1 text-sm font-mono text-red-900 dark:text-red-200">{{ $form->slug }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Warning about related articles --}}
            @if (count($relatedArticles) > 0)
                <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                    <div class="flex items-start gap-3">
                        <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-amber-500 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-200">
                                {{ __('Peringatan: Artikel Terkait Akan Dihapus!') }}
                            </h4>
                            <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                {{ __('Menghapus kategori ini juga akan menghapus :count artikel berikut:', ['count' => count($relatedArticles)]) }}
                            </p>
                            <ul class="mt-2 space-y-1 max-h-32 overflow-y-auto">
                                @foreach ($relatedArticles as $article)
                                    <li class="text-sm text-amber-700 dark:text-amber-300 flex items-center gap-2">
                                        <x-heroicon-o-document-text class="w-4 h-4 flex-shrink-0" />
                                        <span class="truncate">{{ $article['title'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="flex items-center gap-3">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0" />
                        <p class="text-sm text-green-700 dark:text-green-300">
                            {{ __('Tidak ada artikel yang terkait dengan kategori ini.') }}
                        </p>
                    </div>
                </div>
            @endif

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-danger-button>
                    <span wire:loading.remove wire:target="delete">{{ __('Hapus Kategori') }}</span>
                    <span wire:loading wire:target="delete" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                        {{ __('Menghapus...') }}
                    </span>
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</div>
