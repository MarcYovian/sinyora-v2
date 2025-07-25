@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'inline-flex items-center p-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm leading-5 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center p-1 border-b-2 border-transparent text-sm leading-5 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
