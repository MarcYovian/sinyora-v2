<div>
    {{-- =================================================================== --}}
    {{-- HEADER --}}
    {{-- =================================================================== --}}
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen Artikel') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Kelola semua artikel yang ada di dalam sistem.
        </p>
    </header>

    {{-- =================================================================== --}}
    {{-- KONTROL & FILTER --}}
    {{-- =================================================================== --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                @can('access', 'admin.articles.create')
                    <x-button type="button" variant="primary" href="{{ route('admin.articles.create') }}"
                        class="items-center w-full sm:w-auto gap-2">
                        <x-heroicon-s-plus class="w-5 h-5" />
                        <span>{{ __('Buat Artikel') }}</span>
                    </x-button>
                @endcan

                <div class="w-full sm:w-auto sm:flex-grow">
                    <x-search wire:model.live.debounce.300ms="search" placeholder="Cari artikel berdasarkan judul..." />
                </div>
            </div>
        </div>

        {{-- =================================================================== --}}
        {{-- DAFTAR KONTEN (RESPONSIVE: CARD -> TABLE) --}}
        {{-- =================================================================== --}}
        <div class="relative overflow-x-auto">
            {{-- Tampilan Tabel untuk Tablet/Laptop (md and up) --}}
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 hidden md:table">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        @foreach ($table_heads as $head)
                            <th scope="col" class="px-6 py-3">{{ $head }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($articles as $article)
                        <tr wire:key="article-desktop-{{ $article->id }}"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">{{ $loop->iteration + $articles->firstItem() - 1 }}</td>
                            <td class="px-6 py-4">
                                <img src="{{ $article->featured_image ? asset('storage/' . $article->featured_image) : 'https://via.placeholder.com/150' }}"
                                    alt="{{ $article->title }}" class="w-16 h-10 object-cover rounded">
                            </td>
                            <th scope="row"
                                class="px-6 py-4 font-medium text-gray-900 dark:text-white max-w-xs truncate">
                                {{ $article->title }}
                            </th>
                            <td class="px-6 py-4">
                                {{ $article->published_at ? $article->published_at->format('d M Y') : '-' }}</td>
                            <td class="px-6 py-4">
                                <x-badge :color="$article->is_published ? 'success' : 'secondary'">
                                    {{ $article->is_published ? 'Published' : 'Draft' }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4">{{ $article->category->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $article->user->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <x-button tag="a" href="{{ route('articles.show', $article) }}" target="_blank"
                                        variant="secondary" size="sm" class="!p-2">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                        <span class="sr-only">Show</span>
                                    </x-button>
                                    @can('access', 'admin.articles.edit')
                                        <x-button tag="a" href="{{ route('admin.articles.edit', $article->id) }}"
                                            variant="secondary" size="sm" class="!p-2">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                            <span class="sr-only">Edit</span>
                                        </x-button>
                                    @endcan
                                    @can('access', 'admin.articles.destroy')
                                        <x-button type="button" variant="danger" size="sm" class="!p-2"
                                            wire:click="confirmDelete({{ $article->id }})">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                            <span class="sr-only">Delete</span>
                                        </x-button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($table_heads) }}" class="text-center py-8">
                                <div class="flex flex-col items-center justify-center">
                                    <x-heroicon-o-document-magnifying-glass class="w-12 h-12 text-gray-400" />
                                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mt-2">Belum Ada
                                        Artikel</h3>
                                    <p class="text-sm text-gray-500 mt-1">Buat artikel baru untuk memulai.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Tampilan Card untuk Mobile (default, hidden on md) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 md:hidden">
                @forelse ($articles as $article)
                    <div wire:key="article-mobile-{{ $article->id }}"
                        class="bg-white dark:bg-gray-900 rounded-lg shadow-md overflow-hidden ring-1 ring-gray-200 dark:ring-gray-700">
                        <img src="{{ $article->featured_image ? asset('storage/' . $article->featured_image) : 'https://via.placeholder.com/150' }}"
                            alt="{{ $article->title }}" class="w-full h-32 object-cover">
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <span
                                    class="text-xs font-semibold text-blue-600 dark:text-blue-400">{{ $article->category->name ?? 'N/A' }}</span>
                                <x-badge :color="$article->is_published ? 'success' : 'secondary'">
                                    {{ $article->is_published ? 'Published' : 'Draft' }}
                                </x-badge>
                            </div>
                            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mt-1 truncate">
                                {{ $article->title }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                By {{ $article->user->name ?? 'N/A' }} &bull;
                                {{ $article->published_at ? $article->published_at->format('d M Y') : 'Not Published' }}
                            </p>
                        </div>
                        <div
                            class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700 mt-4 pt-4 flex items-center justify-end space-x-2">
                            <x-button tag="a" href="{{ route('articles.show', $article) }}" target="_blank"
                                variant="secondary" size="sm">
                                Show
                            </x-button>
                            @can('access', 'admin.articles.edit')
                                <x-button tag="a" href="{{ route('admin.articles.edit', $article->id) }}"
                                    variant="secondary" size="sm">
                                    Edit
                                </x-button>
                            @endcan
                            @can('access', 'admin.articles.destroy')
                                <x-button type="button" variant="danger" size="sm"
                                    wire:click="confirmDelete({{ $article->id }})">
                                    Delete
                                </x-button>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="col-span-1 sm:col-span-2 text-center py-8">
                        <div class="flex flex-col items-center justify-center">
                            <x-heroicon-o-document-magnifying-glass class="w-12 h-12 text-gray-400" />
                            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mt-2">Belum Ada Artikel</h3>
                            <p class="text-sm text-gray-500 mt-1">Buat artikel baru untuk memulai.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- PAGINASI --}}
        @if ($articles->hasPages())
            <div class="p-4 sm:p-6 border-t border-gray-200 dark:border-gray-700">
                {{ $articles->links() }}
            </div>
        @endif
    </div>

    {{-- =================================================================== --}}
    {{-- MODALS (Show & Delete) --}}
    {{-- =================================================================== --}}
    @if ($article)
        <x-modal name="preview-modal" max-width="4xl">
            <div class="p-6 bg-white dark:bg-gray-800">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $article->title }}</h2>
                        <div class="mt-2 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span>By {{ $article->user->name }}</span>
                            <span>{{ $article->published_at?->format('F j, Y') }}</span>
                            <x-badge :color="$article->is_published ? 'success' : 'secondary'">
                                {{ $article->is_published ? 'Published' : 'Draft' }}
                            </x-badge>
                        </div>
                    </div>
                    <button @click="$dispatch('close')" class="text-gray-400 hover:text-gray-500">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>
                <div class="mt-6 prose dark:prose-invert max-w-none">
                    {!! $article->content !!}
                </div>
            </div>
        </x-modal>
    @endif

    <x-modal name="delete-article-confirmation" focusable>
        <form wire:submit.prevent="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Apakah Anda yakin ingin menghapus artikel ini?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Aksi ini akan memindahkan artikel ke sampah. Anda dapat menghapusnya secara permanen nanti.
            </p>

            <div class="mt-6 flex justify-end">
                <x-button type="button" size="sm" variant="secondary" @click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-button>

                <x-button class="ms-3" size="sm" type="button" variant="danger" wire:click="forceDelete"
                    wire:loading.attr="disabled" wire:target="forceDelete">
                    <div wire:loading wire:target="forceDelete"
                        class="animate-spin rounded-full h-4 w-4 border-b-2 border-current mr-2"></div>
                    <span wire:loading.remove wire:target="forceDelete">{{ __('Hapus Permanen') }}</span>
                    <span wire:loading wire:target="forceDelete">{{ __('Menghapus...') }}</span>
                </x-button>

                <x-button class="ms-3" size="sm" type="submit" variant="danger"
                    wire:loading.attr="disabled" wire:target="delete">
                    <div wire:loading wire:target="delete"
                        class="animate-spin rounded-full h-4 w-4 border-b-2 border-current mr-2"></div>
                    <span wire:loading.remove wire:target="delete">{{ __('Ya, Hapus') }}</span>
                    <span wire:loading wire:target="delete">{{ __('Menghapus...') }}</span>
                </x-button>
            </div>
        </form>
    </x-modal>
</div>
