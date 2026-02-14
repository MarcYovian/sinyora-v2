@props([
    'image' => null,
    'fallback' => 'images/1.webp',
    'alt' => 'Hero Background',
    'class' => 'w-full h-full object-cover object-center',
    'style' => 'filter: brightness(0.4);',
    'fetchpriority' => 'high',
])

@php
    $decoded = is_string($image) ? json_decode($image, true) : null;
    $isResponsive = is_array($decoded);
    
    if ($isResponsive) {
        $desktopSrc = asset($decoded['desktop'] ?? $decoded['tablet'] ?? reset($decoded));
        $srcset = collect($decoded)->map(function ($path, $key) {
            $widths = ['mobile' => '480w', 'tablet' => '768w', 'desktop' => '1200w'];
            return asset($path) . ' ' . ($widths[$key] ?? '1200w');
        })->implode(', ');
    } else {
        $desktopSrc = asset($image ?: $fallback);
        $srcset = null;
    }
@endphp

<img 
    src="{{ $desktopSrc }}"
    @if($srcset)
        srcset="{{ $srcset }}"
        sizes="100vw"
    @endif
    alt="{{ $alt }}"
    class="{{ $class }}"
    style="{{ $style }}"
    fetchpriority="{{ $fetchpriority }}"
>
