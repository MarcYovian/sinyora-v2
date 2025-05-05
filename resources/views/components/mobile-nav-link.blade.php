@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'block px-3 py-2 rounded-md text-base font-medium text-[#FEC006] bg-gray-100'
            : 'block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-[#825700] hover:bg-gray-50';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
