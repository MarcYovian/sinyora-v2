<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Featured Articles -->
    <div class="lg:col-span-2">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @for ($i = 0; $i < 2; $i++)
                <div class="bg-white rounded-xl overflow-hidden shadow-lg">
                    <!-- Image placeholder -->
                    <div class="relative w-full h-64 bg-gray-200 animate-pulse">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                    </div>

                    <!-- Content placeholder -->
                    <div class="p-6">
                        <!-- Category and read time -->
                        <div class="flex items-center space-x-2 mb-4">
                            <span class="h-4 w-16 bg-[#FFD24C]/30 rounded animate-pulse"></span>
                            <span class="h-3 w-12 bg-gray-200 rounded animate-pulse"></span>
                        </div>

                        <!-- Title -->
                        <div class="space-y-2 mb-4">
                            <div class="h-5 w-full bg-gray-200 rounded animate-pulse"></div>
                            <div class="h-5 w-3/4 bg-gray-200 rounded animate-pulse"></div>
                        </div>

                        <!-- Excerpt -->
                        <div class="space-y-2 mb-6">
                            <div class="h-4 w-full bg-gray-200 rounded animate-pulse"></div>
                            <div class="h-4 w-5/6 bg-gray-200 rounded animate-pulse"></div>
                            <div class="h-4 w-2/3 bg-gray-200 rounded animate-pulse"></div>
                        </div>

                        <!-- Read more link -->
                        <div class="h-4 w-32 bg-gray-200 rounded animate-pulse"></div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl p-6 shadow-md border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Artikel Terbaru</h3>
            <div class="space-y-4">
                @for ($i = 0; $i < 2; $i++)
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 bg-gray-200 p-2 rounded-lg h-12 w-12"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-4 w-3/4 bg-gray-200 rounded"></div>
                            <div class="space-y-1">
                                <div class="h-3 w-full bg-gray-200 rounded"></div>
                                <div class="h-3 w-5/6 bg-gray-200 rounded"></div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <div class="bg-[#282834] rounded-xl p-6 text-white">
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
        </div>
    </div>
</div>
