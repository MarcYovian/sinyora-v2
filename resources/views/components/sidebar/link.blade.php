@props(['active' => false, 'href' => '#'])

<li>
    <a href="{{ $href }}"
        class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
        :class="{ 'bg-gray-100 dark:bg-gray-700': {{ $active ? 'true' : 'false' }} }">
        {{ $slot }}
    </a>
</li>
