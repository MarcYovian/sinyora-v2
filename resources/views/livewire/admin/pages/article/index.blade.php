<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen Artikel') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Kelola semua artikel yang ada di dalam sistem.
        </p>
    </header>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="p-4 sm:p-6 space-y-4">
            {{-- Header Kontrol: Tombol, Filter, dan Pencarian --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                @can('access', 'admin.articles.create')
                    <x-button tag="a" href="{{ route('admin.articles.create') }}" variant="primary" class="items-center max-w-xs gap-2">
                        <x-heroicon-s-plus class="w-5 h-5" />
                        <span>{{ __('Buat Artikel') }}</span>
                    </x-button>
                @endcan

                <div class="flex-grow flex flex-col sm:flex-row items-center gap-3">
                    <div class="w-full sm:w-auto sm:flex-grow">
                        <x-text-input wire:model.live.debounce.300ms="search" type="text" class="w-full"
                            placeholder="{{ __('Cari judul artikel...') }}" />
                    </div>
                    <div class="w-full sm:w-48">
                        <x-select wire:model.live="filterStatus" class="w-full">
                            <option value="">{{ __('Semua Status') }}</option>
                            <option value="1">{{ __('Published') }}</option>
                            <option value="0">{{ __('Draft') }}</option>
                        </x-select>
                    </div>
                    @if ($search || $filterStatus !== '')
                        <x-button type="button" wire:click="resetFilters" variant="secondary" class="w-full sm:w-auto">
                            {{ __('Reset') }}
                        </x-button>
                    @endif
                </div>
            </div>

            {{-- Indikator Loading --}}
            <div wire:loading.flex wire:target="search, filterStatus" class="items-center justify-center w-full py-4">
                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                    <x-heroicon-s-arrow-path class="h-5 w-5 animate-spin" />
                    <span>Memuat data...</span>
                </div>
            </div>

            <div wire:loading.remove wire:target="search, filterStatus">
                {{-- Tampilan Mobile (Card) --}}
                <div class="grid grid-cols-1 gap-4 md:hidden">
                    @forelse ($articles as $article)
                        <div wire:key="article-card-{{ $article->id }}"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden ring-1 ring-black ring-opacity-5">
                            <div class="relative h-40">
                                <img src="{{ $article->featured_image_url }}"
                                    alt="{{ $article->title }}" class="w-full h-full object-cover">
                                <div class="absolute top-2 right-2">
                                     @if ($article->is_published)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/80 dark:text-green-100 backdrop-blur-sm">
                                            Published
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-gray-100 text-gray-800 dark:bg-gray-900/80 dark:text-gray-100 backdrop-blur-sm">
                                            Draft
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="p-4 border-b dark:border-gray-700">
                                <div class="mb-2">
                                    <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                                        {{ $article->category->name ?? 'Uncategorized' }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200 line-clamp-2">
                                    {{ $article->title }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-2">
                                    <span>{{ $article->user->name ?? 'Unknown' }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $article->published_at ? $article->published_at->format('d M Y') : 'Not Published' }}</span>
                                </p>
                            </div>
                            
                            {{-- Mobile Card Actions --}}
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/30 flex flex-wrap gap-4 items-center justify-end">
                                <a href="{{ route('articles.show', $article) }}" target="_blank" 
                                   class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline flex items-center gap-1.5 transition-colors text-sm">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                    <span>{{ __('View') }}</span>
                                </a>
                                @can('access', 'admin.articles.edit')
                                    <a href="{{ route('admin.articles.edit', $article->id) }}" 
                                       class="text-amber-500 dark:text-amber-400 font-medium hover:underline flex items-center gap-1.5 transition-colors text-sm">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        <span>{{ __('Edit') }}</span>
                                    </a>
                                @endcan
                                @can('access', 'admin.articles.destroy')
                                    <button wire:click="confirmDelete({{ $article->id }})" 
                                            class="text-red-600 dark:text-red-400 font-medium hover:underline flex items-center gap-1.5 transition-colors text-sm">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                        <span>{{ __('Delete') }}</span>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    @empty
                         <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada data artikel') }}
                        </div>
                    @endforelse
                </div>

                {{-- Desktop Table --}}
                <div class="hidden md:block">
                    <x-table title="Data Artikel" :heads="$table_heads">
                        @forelse ($articles as $index => $article)
                            <tr wire:key="article-row-{{ $article->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $articles->firstItem() + $index }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <img src="{{ $article->featured_image_url }}"
                                         alt="Thumbnail" class="w-16 h-10 object-cover rounded-md border dark:border-gray-700 bg-gray-100 dark:bg-gray-700">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-gray-200 line-clamp-1" title="{{ $article->title }}">
                                        {{ Str::limit($article->title, 50) }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $article->slug }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $article->published_at ? $article->published_at->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($article->is_published)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-green-500"></span>
                                            Published
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-gray-500"></span>
                                            Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $article->category->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $article->user->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end space-x-1">
                                        <x-button tag="a" href="{{ route('articles.show', $article) }}" target="_blank"
                                            variant="secondary" size="sm" class="!p-2" title="View">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                        </x-button>
                                        @can('access', 'admin.articles.edit')
                                            <x-button tag="a" href="{{ route('admin.articles.edit', $article->id) }}"
                                                variant="warning" size="sm" class="!p-2" title="Edit">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                            </x-button>
                                        @endcan
                                        @can('access', 'admin.articles.destroy')
                                            <x-button type="button" variant="danger" size="sm" class="!p-2"
                                                wire:click="confirmDelete({{ $article->id }})" title="Delete">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                            </x-button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                             <tr>
                                <td colspan="{{ count($table_heads) }}"
                                    class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-document-magnifying-glass class="mx-auto h-12 w-12" />
                                    <h4 class="mt-2 text-sm font-semibold">{{ __('Tidak ada data artikel') }}</h4>
                                    <p class="mt-1 text-sm">{{ __('Coba ubah filter Anda atau buat artikel baru.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>
            </div>
        </div>

        <div class="px-4 md:px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $articles->links() }}
        </div>
    </div>

    {{-- Delete Modal --}}
    <x-modal name="delete-article-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit.prevent="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Hapus Artikel?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Apakah Anda yakin ingin menghapus artikel ini? Aksi ini akan memindahkan artikel ke sampah.') }}
            </p>

             @if ($article)
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $article->title }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                         Status: {{ $article->is_published ? 'Published' : 'Draft' }}
                    </div>
                </div>
            @endif

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-danger-button type="submit">
                    <span wire:loading.remove wire:target="delete">{{ __('Hapus') }}</span>
                    <span wire:loading wire:target="delete" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                        {{ __('Menghapus...') }}
                    </span>
                </x-danger-button>
            </div>
        </form>
    </x-modal>
     
    {{-- Preview Modal --}}
     @if ($article)
        <x-modal name="preview-modal" max-width="4xl">
             <div class="p-6 bg-white dark:bg-gray-800">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $article->title }}</h2>
                        <div class="mt-2 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span>By {{ $article->user->name ?? 'Unknown' }}</span>
                            <span>{{ $article->published_at?->format('F j, Y') }}</span>
                             <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $article->is_published ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ $article->is_published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                    </div>
                    <button @click="$dispatch('close')" class="text-gray-400 hover:text-gray-500">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>
                <div class="mt-6 prose dark:prose-invert max-w-none">
                    {!! \Stevebauman\Purify\Facades\Purify::config('trix')->clean($article->content) !!}
                </div>
            </div>
        </x-modal>
     @endif
</div>
