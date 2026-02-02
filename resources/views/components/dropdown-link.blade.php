@php
    $classes =
        'block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out';
    
    // Only consider external if has target="_blank"
    $isExternal = $attributes->has('target');
@endphp

@if ($attributes->has('href'))
    @if($isExternal)
        <a {{ $attributes->merge(['class' => $classes]) }}>
    @else
        <a wire:navigate {{ $attributes->merge(['class' => $classes]) }}>
    @endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif


