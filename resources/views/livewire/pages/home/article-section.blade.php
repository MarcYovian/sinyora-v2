<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Featured Articles -->
    <div class="lg:col-span-2">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if ($popularArticles->count() > 0)
                @foreach ($popularArticles as $article)
                    <div class=" bg-white rounded-xl overflow-hidden shadow-lg group">
                        <div class="relative">
                            <img class="w-full h-64 object-cover" src="{{ asset("storage/{$article->featured_image}") }}"
                                alt="Card 1">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent">
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span
                                        class="text-xs font-medium text-[#FFD24C]">{{ $article->category->name }}</span>
                                    <span class="text-xs text-white/80">• {{ $article->reading_time }} min read</span>
                                </div>
                                <h3 class="text-xl font-bold line-clamp-2">
                                    {{ $article->title }}
                                </h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600 mb-4 line-clamp-3">
                                {{ $article->excerpt }}
                            </p>
                            <a href="#"
                                class="inline-flex items-center text-[#825700] font-medium hover:text-[#6b4900] transition-colors">
                                Baca selengkapnya
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-8 text-center">
                    <div class="mx-auto w-24 h-24 text-[#FFD24C] mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">Belum ada artikel populer</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
                        Kami belum mempublikasikan artikel terbaru. Silakan cek kembali nanti!
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Sidebar Articles -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl p-6 shadow-md border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Artikel Terbaru</h3>
            <div class="space-y-4">
                @if ($latestArticles->count() > 0)
                    @foreach ($latestArticles as $item)
                        <a href="#" class="group block">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0 bg-[#FFD24C]/10 p-2 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#825700]"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-800 group-hover:text-[#825700] transition-colors">
                                        {{ $item->title }}</h4>
                                    </h4>
                                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">
                                        {{ $item->excerpt }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @else
                    <div class="text-center py-8">
                        <div class="mx-auto w-20 h-20 text-gray-300 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-medium text-gray-700 mb-1">Belum ada artikel terbaru</h4>
                        <p class="text-gray-500 text-sm">Silakan kembali lagi nanti</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Newsletter Subscription -->
        {{-- <div class="bg-[#282834] rounded-xl p-6 text-white">
            <h3 class="text-lg font-bold mb-2">Tetap Terhubung</h3>
            <p class="text-sm text-[#FFD24C] mb-4">
                Dapatkan update terbaru langsung ke email Anda
            </p>
            <form class="space-y-3">
                <input type="email" placeholder="Alamat Email Anda"
                    class="w-full px-4 py-2 rounded-md bg-white/10 border border-white/20 focus:ring-2 focus:ring-[#FFD24C] focus:border-transparent text-white placeholder-white/50">
                <button type="submit"
                    class="w-full bg-[#FFD24C] hover:bg-[#FEC006] text-[#825700] font-medium py-2 rounded-md transition-colors">
                    Berlangganan
                </button>
            </form>
            <p class="text-xs text-white/60 mt-3">
                Kami menghargai privasi Anda. Tidak ada spam.
            </p>
        </div> --}}
    </div>
</div>
