<div class="font-sans antialiased text-gray-900 bg-gray-50">
    <!-- Hero Section with Blur Background -->
    <div class="relative bg-gray-900">
        <!-- Blur Background Image -->
        <div class="absolute inset-0 overflow-hidden">
            <img src="{{ $article->featured_image ? Storage::url($article->featured_image) : asset('images/article-placeholder.jpg') }}"
                alt="" class="w-full h-full object-cover object-center opacity-30 blur-sm scale-105">
        </div>
        
        <!-- Content Overlay -->
        <div class="relative max-w-4xl mx-auto py-24 px-4 sm:py-28 sm:px-6 lg:px-8">
            <div class="text-center">
                <!-- Category Badge -->
                <span
                    class="inline-block px-4 py-1.5 mb-6 text-sm font-semibold text-yellow-800 bg-yellow-200 rounded-full">
                    {{ $article->category->name }}
                </span>

                <!-- Title -->
                <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl leading-tight">
                    {{ $article->title }}
                </h1>

                <!-- Meta Info -->
                <div class="mt-6 flex items-center justify-center flex-wrap gap-4 text-gray-300 text-sm">
                    <div class="flex items-center">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>{{ $article->user->name }}</span>
                    </div>
                    <span class="text-gray-500">•</span>
                    <div class="flex items-center">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ $article->published_at?->translatedFormat('d F Y') ?? 'Draft' }}</span>
                    </div>
                    <span class="text-gray-500">•</span>
                    <div class="flex items-center">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $article->reading_time }} min read</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Image - Full Display (No Crop) -->
    @if ($article->featured_image)
        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
            <div class="rounded-xl overflow-hidden shadow-2xl ring-1 ring-gray-900/10">
                <img src="{{ Storage::url($article->featured_image) }}" alt="{{ $article->title }}"
                    class="w-full h-auto">
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Article Content -->
        <article class="prose prose-lg max-w-none prose-headings:text-gray-900 prose-a:text-yellow-700 hover:prose-a:text-yellow-800">
            {!! \Stevebauman\Purify\Facades\Purify::config('trix')->clean($article->content) !!}
        </article>

        <!-- Tags -->
        @if ($article->tags->count() > 0)
            <div class="mt-12 pt-8 border-t border-gray-200">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Tags:</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($article->tags as $tag)
                        <a href="{{ route('articles.index', ['tag' => $tag->name]) }}" wire:navigate
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-yellow-100 hover:text-yellow-800 transition-colors">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Share Buttons -->
        <div class="mt-8 pt-8 border-t border-gray-200">
            <h3 class="text-sm font-medium text-gray-500 mb-4">Bagikan artikel ini:</h3>
            <div class="flex space-x-4">
                <a href="{{ 'https://www.facebook.com/sharer/sharer.php?u=' . url('articles/' . $article->slug) }}"
                    target="_blank" 
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                            clip-rule="evenodd" />
                    </svg>
                    Facebook
                </a>
                <a href="{{ 'https://wa.me/?text=' . urlencode($article->title . ' - ' . url('articles/' . $article->slug)) }}" 
                    target="_blank"
                    rel="noopener noreferrer" 
                    class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    WhatsApp
                </a>
            </div>
        </div>
    </div>

    <!-- Related Articles -->
    @if ($relatedArticles->count() > 0)
        <div class="bg-white py-12 border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Artikel Terkait</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($relatedArticles as $related)
                        <article
                            class="flex flex-col overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 bg-white">
                            <div class="flex-shrink-0">
                                <img class="h-48 w-full object-cover"
                                    src="{{ $related->featured_image ? Storage::url($related->featured_image) : asset('images/article-placeholder.jpg') }}"
                                    alt="{{ $related->title }}">
                            </div>
                            <div class="flex-1 p-6 flex flex-col justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-yellow-600">
                                        {{ $related->category->name }}
                                    </p>
                                    <a href="{{ route('articles.show', $related) }}" wire:navigate class="block mt-2 group">
                                        <h3 class="text-lg font-semibold text-gray-900 line-clamp-2 group-hover:text-yellow-700 transition-colors">
                                            {{ $related->title }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-500 line-clamp-2">
                                            {{ $related->excerpt }}
                                        </p>
                                    </a>
                                </div>
                                <div class="mt-4 flex items-center text-xs text-gray-500">
                                    <span>{{ $related->published_at->translatedFormat('d M Y') }}</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ $related->reading_time }} min read</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
