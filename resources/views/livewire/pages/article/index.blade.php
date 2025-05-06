@push('styles')
    <style>
        /* Custom styles that complement Tailwind */
        .article-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .article-card:hover {
            transform: translateY(-5px);
        }

        .article-image {
            transition: transform 0.5s ease;
        }

        .article-card:hover .article-image {
            transform: scale(1.05);
        }

        .read-more svg {
            transition: transform 0.2s ease;
        }

        .read-more:hover svg {
            transform: translateX(3px);
        }

        .category-chip {
            transition: all 0.2s ease;
        }

        .category-chip:hover {
            transform: translateY(-2px);
        }
    </style>
@endpush

<div class="font-sans antialiased text-gray-900">
    <!-- Hero Section -->
    <section class="relative bg-cover bg-center bg-no-repeat py-32 px-6"
        style="background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('{{ asset('images/1.jpg') }}');">
        <div class="container mx-auto text-center text-white max-w-4xl">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-6 leading-tight">
                Artikel Terbaru
            </h1>
            <p class="text-lg sm:text-xl md:text-2xl mb-8 opacity-90 max-w-3xl mx-auto">
                Temukan artikel inspiratif, berita terbaru, dan wawasan rohani dari Kapel St. Yohanes Rasul
            </p>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12 max-w-7xl">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Main Content -->
            <div class="lg:w-2/3">
                <!-- Search and Filter Section -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                    <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
                        <!-- Search Box -->
                        <div class="relative w-full md:w-auto flex-grow">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input wire:model.live.debounce.500ms="search" type="text"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-full bg-gray-50 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 sm:text-sm"
                                placeholder="Cari artikel...">
                        </div>

                        <!-- Category Filter -->
                        <div class="w-full md:w-auto">
                            <select wire:model.live="selectedCategory"
                                class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 sm:text-sm rounded-full">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Articles Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($articles as $article)
                        <article class="article-card bg-white rounded-xl shadow-md overflow-hidden flex flex-col">
                            <!-- Article Image -->
                            <div class="relative h-48 overflow-hidden">
                                <img src="{{ $article->featured_image ? Storage::url($article->featured_image) : asset('images/article-placeholder.jpg') }}"
                                    alt="{{ $article->title }}" class="article-image w-full h-full object-cover">
                                <span
                                    class="absolute top-4 left-4 bg-yellow-400 text-yellow-800 text-xs font-semibold px-3 py-1 rounded-full shadow-sm">
                                    {{ $article->category->name }}
                                </span>
                            </div>

                            <!-- Article Content -->
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="flex items-center text-sm text-gray-500 mb-2">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $article->published_at->translatedFormat('d F Y') }}
                                </div>

                                <h3 class="text-xl font-bold text-gray-800 mb-3 leading-snug">
                                    {{ $article->title }}
                                </h3>

                                <p class="text-gray-600 mb-4 flex-grow line-clamp-3">
                                    {{ $article->excerpt }}
                                </p>

                                <div class="flex justify-between items-center">
                                    <a href="{{ route('articles.show', $article) }}"
                                        class="read-more inline-flex items-center text-yellow-700 font-medium text-sm hover:text-yellow-800">
                                        Baca selengkapnya
                                        <svg class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>

                                    <div class="flex items-center text-gray-400 text-xs">
                                        <span class="flex items-center mr-3">
                                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            {{ $article->views }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-2 bg-white rounded-xl shadow-sm p-12 text-center">
                            <div
                                class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mb-4">
                                <svg class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">
                                @if ($selectedCategory || $search)
                                    Artikel tidak ditemukan
                                @else
                                    Belum ada artikel
                                @endif
                            </h3>
                            <p class="text-gray-500">
                                @if ($selectedCategory || $search)
                                    Coba dengan kata kunci atau kategori yang berbeda
                                @else
                                    Silakan periksa kembali nanti untuk update terbaru
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if ($articles->hasPages())
                    <div class="mt-8">
                        {{ $articles->links(data: ['scrollTo' => false]) }}
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:w-1/3 space-y-6">
                <!-- Popular Categories -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Kategori Populer</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach ($popularCategories as $category)
                                <a href="{{ route('articles.index', ['category' => $category->name]) }}" wire:navigate
                                    class="category-chip block px-4 py-3 bg-gray-50 hover:bg-yellow-50 rounded-lg transition">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-900">{{ $category->name }}</span>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{ $category->articles_count }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Recent Articles -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Artikel Terbaru</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach ($recentArticles as $article)
                                <a href="{{ route('articles.show', $article) }}" class="flex group">
                                    <div class="flex-shrink-0">
                                        <img class="h-16 w-16 rounded-lg object-cover"
                                            src="{{ $article->featured_image ? Storage::url($article->featured_image) : asset('images/article-placeholder.jpg') }}"
                                            alt="{{ $article->title }}">
                                    </div>
                                    <div class="ml-4">
                                        <h4
                                            class="text-sm font-medium text-gray-900 group-hover:text-yellow-700 line-clamp-2">
                                            {{ $article->title }}
                                        </h4>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $article->published_at->translatedFormat('d M Y') }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Newsletter Subscription -->
                {{-- <div
                    class="bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-xl shadow-sm overflow-hidden p-6 text-center">
                    <h3 class="text-lg font-semibold text-white mb-2">Berlangganan Artikel</h3>
                    <p class="text-yellow-100 mb-4">Dapatkan artikel terbaru langsung ke email Anda</p>
                    <form class="mt-4">
                        <div class="flex rounded-md shadow-sm">
                            <input type="email"
                                class="flex-1 min-w-0 block w-full px-3 py-2 rounded-l-md border-0 text-sm focus:ring-2 focus:ring-yellow-700"
                                placeholder="Alamat email">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-r-md text-yellow-800 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-700">
                                Subscribe
                            </button>
                        </div>
                    </form>
                </div> --}}
            </div>
        </div>
    </div>
</div>
