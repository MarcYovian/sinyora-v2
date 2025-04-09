@props(['active' => false, 'href' => '#', 'hasDropdown' => false])

<li>
    <a href="{{ $href }}" @if ($hasDropdown) @click.prevent="open = !open" @endif
        class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
        :class="{ 'bg-gray-100 dark:bg-gray-700': {{ $active ? 'true' : 'false' }} }">
        {{ $slot }}
    </a>

    @if ($hasDropdown)
        <ul x-show="open" class="py-2 space-y-2 pl-7">
            {{ $dropdown }}
        </ul>
    @endif
</li>
