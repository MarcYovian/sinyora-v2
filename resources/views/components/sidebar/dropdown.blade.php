@props(['title', 'icon' => null, 'active' => false])

<li x-data="{ open: {{ $active ? 'true' : 'false' }} }">
    <button @click="open = !open"
        class="flex items-center w-full p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
        :class="{ 'bg-gray-100 dark:bg-gray-700': {{ $active ? 'true' : 'false' }} }">
        @if ($icon)
            @svg('heroicon-' . $icon, 'w-5 h-5')
        @endif
        <span class="flex-1 ms-3 text-left whitespace-nowrap">{{ $title }}</span>
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="m1 1 4 4 4-4" />
        </svg>
    </button>

    <ul x-show="open" class="py-2 space-y-2 pl-7">
        {{ $slot }}
    </ul>
</li>
