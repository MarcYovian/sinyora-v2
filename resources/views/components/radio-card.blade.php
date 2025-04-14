@props(['value', 'selected' => false])

<label class="relative cursor-pointer">
    <input type="radio" {{ $attributes->merge(['class' => 'sr-only peer']) }} value="{{ $value }}"
        @checked($selected)>
    <div
        {{ $attributes->merge(['class' => 'p-4 border border-gray-300 dark:border-gray-600 rounded-lg transition-all peer-checked:border-indigo-500 peer-checked:ring-2 peer-checked:ring-indigo-500 peer-checked:bg-indigo-50/50 dark:peer-checked:bg-indigo-900/20 hover:border-gray-400 dark:hover:border-gray-500']) }}>
        {{ $slot }}
    </div>
</label>
