<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Articles') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between">
            <x-button type="button" variant="primary" href="{{ route('admin.articles.create') }}"
                class="items-center max-w-xs gap-2">
                <x-heroicon-s-plus class="w-5 h-5" />

                <span>{{ __('Create') }}</span>
            </x-button>

            <div class="w-full md:w-1/2">
                <x-search placeholder="Search Articles by title.." />
            </div>
        </div>

        <div class="p-6 text-gray-900 dark:text-gray-100">
            <x-table title="Data Articles" :heads="$table_heads">
                @forelse ($articles as $key => $article)
                    <tr wire:key="user-{{ $article->id }}"
                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $key + $articles->firstItem() }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            <img src="{{ Storage::url($article->featured_image) }}" alt="{{ $article->title }}"
                                class="w-36 h-20 rounded object-cover">
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $article->title }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $article->published_at?->format('d/m/Y') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            @if ($article->is_published)
                                <span class="text-green-500 dark:text-green-400">{{ __('Published') }}</span>
                            @else
                                <span class="text-rose-500 dark:text-rose-400">{{ __('Draft') }}</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $article->category->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $article->user->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            <div class="flex flex-col items-center gap-2">
                                <x-button size="sm" variant="primary" wire:click="show({{ $article }})">
                                    {{ __('Detail') }}
                                </x-button>
                                <x-button size="sm" variant="warning" type="button"
                                    disabled="{{ $article->status === App\Enums\BorrowingStatus::APPROVED }}"
                                    href="{{ route('admin.articles.edit', $article) }}">
                                    {{ __('Edit') }}
                                </x-button>
                                <x-button size="sm" variant="danger" type="button"
                                    wire:click="confirmDelete({{ $article->id }})">
                                    {{ __('Delete') }}
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white dark:bg-gray-800">
                        <td colspan="{{ count($table_heads) }}"
                            class="whitespace-nowrap px-6 py-4 text-rose-700 dark:text-rose-400 text-sm text-center">
                            {{ __('No data available') }}
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>
        <div class="px-6 py-4">
            {{ $articles->links() }}
        </div>
    </div>

    <x-modal name="preview-modal" maxWidth="7xl">
        @if ($this->article)
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
                    @if ($this->article->featured_image)
                        <div
                            class="relative aspect-[16/9] w-full overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700 shadow">
                            <img class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                                src="{{ asset("storage/{$this->article->featured_image}") }}" alt="Thumbnail Preview">
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
                                    @if ($this->article->is_published)
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
                                    {{ $this->article->category->name ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <x-heroicon-s-hashtag class="h-5 w-5 text-gray-400" />
                            <div>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Tags</p>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @forelse($this->article->tags as $tag)
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
                            {{ $this->article->title }}
                        </h1>

                        @if ($this->article->excerpt)
                            <div
                                class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg mb-6 border-l-4 border-primary-500 dark:border-primary-400">
                                <p class="text-gray-600 dark:text-gray-300 italic font-medium">
                                    {{ $this->article->excerpt }}
                                </p>
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
                            {!! $this->article->content !!}
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
        @endif
    </x-modal>
</div>
