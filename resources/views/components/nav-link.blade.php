@props(['active'])

@php
    $baseClasses =
        'relative text-sm font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FEC006] rounded-md px-1';
    $activeClasses = 'text-[#FEC006]';
    $inactiveClasses = 'hover:text-[#FEC006]';

    $classes = $active ?? false ? $baseClasses . ' ' . $activeClasses : $baseClasses . ' ' . $inactiveClasses;
    
    // Only consider external if has target="_blank" (external links always have this)
    $isExternal = $attributes->has('target');
@endphp

@if($isExternal)
    <a {{ $attributes->merge(['class' => $classes]) }}>
@else
    <a wire:navigate {{ $attributes->merge(['class' => $classes]) }}>
@endif
    {{ $slot }}
    @if ($active ?? false)
        <span class="absolute bottom-[-8px] left-1/2 -translate-x-1/2 h-0.5 w-6 bg-[#FEC006] rounded-full"></span>
    @endif
</a>

