<div class="font-sans antialiased text-gray-900 bg-gray-50">
    <!-- Hero Section -->
    <div class="relative bg-gray-900">
        <div class="absolute inset-0 overflow-hidden">
            <img src="{{ $article->featured_image ? Storage::url($article->featured_image) : asset('images/article-placeholder.jpg') }}"
                alt="{{ $article->title }}" class="w-full h-full object-cover opacity-30">
        </div>
        <div class="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8">
            <div class="text-center">
                <span
                    class="inline-block px-3 py-1 mb-4 text-sm font-semibold text-yellow-800 bg-yellow-200 rounded-full">
                    {{ $article->category->name }}
                </span>
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    {{ $article->title }}
                </h1>
                <div class="mt-8 flex items-center justify-center space-x-4 text-gray-300">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>{{ $article->user->name }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ $article->published_at->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $article->reading_time }} min read</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Article Content -->
        <article class="prose prose-lg max-w-none">
            {!! $article->content !!}
        </article>

        <!-- Tags -->
        @if ($article->tags->count() > 0)
            <div class="mt-12">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Tags:</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($article->tags as $tag)
                        <a href="{{ route('articles.index', ['tag' => $tag->name]) }}"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Share Buttons -->
        <div class="mt-12 border-t border-gray-200 pt-8">
            <h3 class="text-sm font-medium text-gray-500 mb-4">Bagikan artikel ini:</h3>
            <div class="flex space-x-4">
                <a href="{{ 'https://www.facebook.com/sharer/sharer.php?u=' . url('articles/' . $article->slug) }}"
                    target="_blank" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Facebook</span>
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                <a href="{{ 'https://wa.me/?text=' . url('articles/' . $article->slug) }}" target="_blank"
                    rel="noopener noreferrer" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">WhatsApp</span>
                    <svg class="h-6 w-6" fill="currentColor" version="1.1" id="Layer_1"
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        viewBox="0 0 308 308" xml:space="preserve">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <g id="XMLID_468_">
                                <path id="XMLID_469_"
                                    d="M227.904,176.981c-0.6-0.288-23.054-11.345-27.044-12.781c-1.629-0.585-3.374-1.156-5.23-1.156 c-3.032,0-5.579,1.511-7.563,4.479c-2.243,3.334-9.033,11.271-11.131,13.642c-0.274,0.313-0.648,0.687-0.872,0.687 c-0.201,0-3.676-1.431-4.728-1.888c-24.087-10.463-42.37-35.624-44.877-39.867c-0.358-0.61-0.373-0.887-0.376-0.887 c0.088-0.323,0.898-1.135,1.316-1.554c1.223-1.21,2.548-2.805,3.83-4.348c0.607-0.731,1.215-1.463,1.812-2.153 c1.86-2.164,2.688-3.844,3.648-5.79l0.503-1.011c2.344-4.657,0.342-8.587-0.305-9.856c-0.531-1.062-10.012-23.944-11.02-26.348 c-2.424-5.801-5.627-8.502-10.078-8.502c-0.413,0,0,0-1.732,0.073c-2.109,0.089-13.594,1.601-18.672,4.802 c-5.385,3.395-14.495,14.217-14.495,33.249c0,17.129,10.87,33.302,15.537,39.453c0.116,0.155,0.329,0.47,0.638,0.922 c17.873,26.102,40.154,45.446,62.741,54.469c21.745,8.686,32.042,9.69,37.896,9.69c0.001,0,0.001,0,0.001,0 c2.46,0,4.429-0.193,6.166-0.364l1.102-0.105c7.512-0.666,24.02-9.22,27.775-19.655c2.958-8.219,3.738-17.199,1.77-20.458 C233.168,179.508,230.845,178.393,227.904,176.981z">
                                </path>
                                <path id="XMLID_470_"
                                    d="M156.734,0C73.318,0,5.454,67.354,5.454,150.143c0,26.777,7.166,52.988,20.741,75.928L0.212,302.716 c-0.484,1.429-0.124,3.009,0.933,4.085C1.908,307.58,2.943,308,4,308c0.405,0,0.813-0.061,1.211-0.188l79.92-25.396 c21.87,11.685,46.588,17.853,71.604,17.853C240.143,300.27,308,232.923,308,150.143C308,67.354,240.143,0,156.734,0z M156.734,268.994c-23.539,0-46.338-6.797-65.936-19.657c-0.659-0.433-1.424-0.655-2.194-0.655c-0.407,0-0.815,0.062-1.212,0.188 l-40.035,12.726l12.924-38.129c0.418-1.234,0.209-2.595-0.561-3.647c-14.924-20.392-22.813-44.485-22.813-69.677 c0-65.543,53.754-118.867,119.826-118.867c66.064,0,119.812,53.324,119.812,118.867 C276.546,215.678,222.799,268.994,156.734,268.994z">
                                </path>
                            </g>
                        </g>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Related Articles -->
    @if ($relatedArticles->count() > 0)
        <div class="bg-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Artikel Terkait</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($relatedArticles as $related)
                        <article
                            class="flex flex-col overflow-hidden rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
                            <div class="flex-shrink-0">
                                <img class="h-48 w-full object-cover"
                                    src="{{ $related->featured_image ? Storage::url($related->featured_image) : asset('images/article-placeholder.jpg') }}"
                                    alt="{{ $related->title }}">
                            </div>
                            <div class="flex-1 bg-white p-6 flex flex-col justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-yellow-600">
                                        {{ $related->category->name }}
                                    </p>
                                    <a href="{{ route('articles.show', $related) }}" class="block mt-2">
                                        <h3 class="text-xl font-semibold text-gray-900 line-clamp-2">
                                            {{ $related->title }}
                                        </h3>
                                        <p class="mt-3 text-base text-gray-500 line-clamp-3">
                                            {{ $related->excerpt }}
                                        </p>
                                    </a>
                                </div>
                                <div class="mt-6 flex items-center">
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full"
                                            src="{{ $related->user->profile_photo_url }}"
                                            alt="{{ $related->user->name }}">
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $related->user->name }}
                                        </p>
                                        <div class="flex space-x-1 text-sm text-gray-500">
                                            <time datetime="{{ $related->published_at->format('Y-m-d') }}">
                                                {{ $related->published_at->translatedFormat('d M Y') }}
                                            </time>
                                            <span aria-hidden="true">&middot;</span>
                                            <span>{{ $related->reading_time }} min read</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Newsletter Subscription -->
    <div class="bg-gray-900 py-16 sm:py-24">
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative py-16 px-8 bg-yellow-500 rounded-xl overflow-hidden">
                <div class="absolute inset-0 opacity-10 mix-blend-multiply">
                    <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <path fill="#000"
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
                    </svg>
                </div>
                <div class="relative max-w-3xl mx-auto text-center">
                    <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Tetap terhubung dengan kami
                    </h2>
                    <p class="mt-3 text-xl text-gray-800">
                        Dapatkan artikel terbaru langsung ke inbox Anda
                    </p>
                    <form class="mt-8 sm:flex sm:justify-center" action="#" method="POST">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <label for="email-address" class="sr-only">Alamat email</label>
                            <input id="email-address" name="email" type="email" required
                                class="w-full px-5 py-3 placeholder-gray-500 focus:ring-2 focus:ring-offset-2 focus:ring-yellow-700 focus:outline-none rounded-md"
                                placeholder="Masukkan email Anda">
                            <div class="rounded-md shadow sm:flex-shrink-0">
                                <button type="submit"
                                    class="w-full flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-gray-900 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800">
                                    Berlangganan
                                </button>
                            </div>
                        </div>
                    </form>
                    <p class="mt-3 text-sm text-gray-700">
                        Kami menghormati privasi Anda. Unsubscribe kapan saja.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
