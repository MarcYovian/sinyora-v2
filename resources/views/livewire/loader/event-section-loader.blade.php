<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
    @for ($i = 0; $i < 3; $i++)
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

    <!-- Add more event cards here following the same pattern -->
</div>
