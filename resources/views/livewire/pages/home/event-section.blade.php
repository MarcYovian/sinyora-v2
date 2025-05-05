<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
    @forelse ($eventArticles as $item)
        <div class="bg-white rounded-xl overflow-hidden shadow-lg transition-all duration-300 hover:shadow-xl">
            <div class="relative">
                <img class="w-full h-56 object-cover" src="{{ asset('images/about.jpg') }}" alt="Perayaan Paskah 2023">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                    <span
                        class="inline-block px-3 py-1 bg-[#FFD24C] text-[#825700] text-xs font-semibold rounded-full mb-2">
                        Paskah
                    </span>
                    <h3 class="text-xl font-bold">Perayaan Paskah 2023</h3>
                </div>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">
                    Perayaan Paskah di Kapela Sinyora Yang Sangat Khidmat dan Tenang
                </p>
                <a href="#"
                    class="inline-flex items-center text-[#825700] font-medium hover:text-[#6b4900] transition-colors">
                    Lihat detail
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12">
            <div class="mx-auto w-24 h-24 text-[#FFD24C]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Belum ada artikel tersedia</h3>
            <p class="mt-2 text-gray-500 max-w-md mx-auto">
                Kami belum mempublikasikan artikel terbaru. Silakan cek kembali nanti!
            </p>
            <div class="mt-6">
                <button
                    class="inline-flex items-center px-4 py-2 bg-[#FFD24C] text-[#825700] rounded-md hover:bg-[#FEC006] transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                            clip-rule="evenodd" />
                    </svg>
                    Muat Ulang
                </button>
            </div>
        </div>
    @endforelse

    <!-- Add more event cards here following the same pattern -->
</div>
