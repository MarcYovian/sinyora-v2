<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
    {{-- Header Skeleton --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 bg-gray-200 rounded animate-pulse"></div>
            <div class="h-10 w-10 bg-gray-200 rounded animate-pulse"></div>
            <div class="h-6 w-32 bg-gray-200 rounded animate-pulse"></div>
        </div>
        <div class="flex gap-2">
            <div class="h-8 w-20 bg-gray-200 rounded animate-pulse"></div>
            <div class="h-8 w-20 bg-gray-200 rounded animate-pulse"></div>
            <div class="h-8 w-20 bg-gray-200 rounded animate-pulse"></div>
        </div>
    </div>

    {{-- Week Days Header --}}
    <div class="grid grid-cols-7 gap-2 mb-4">
        @for ($i = 0; $i < 7; $i++)
            <div class="text-center">
                <div class="h-4 w-10 mx-auto bg-gray-200 rounded animate-pulse"></div>
            </div>
        @endfor
    </div>

    {{-- Calendar Grid Skeleton --}}
    <div class="grid grid-cols-7 gap-2">
        @for ($i = 0; $i < 35; $i++)
            <div class="aspect-square p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="h-4 w-6 bg-gray-200 rounded animate-pulse"></div>
                @if ($i % 5 == 0)
                    <div class="h-2 w-full bg-[#FFD24C]/30 rounded mt-2 animate-pulse"></div>
                @endif
            </div>
        @endfor
    </div>
</div>
