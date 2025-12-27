    <title>{{ $seo->title }}</title>
    <meta name="description" content="{{ $seo->description }}">
    <meta name="keywords" content="{{ implode(', ', $seo->keywords) }}">
    <meta name="author" content="{{ $seo->author }}">
    <link rel="canonical" href="{{ $seo->canonical }}" />

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="{{ $seo->ogType }}">
    <meta property="og:url" content="{{ $seo->canonical }}">
    <meta property="og:title" content="{{ $seo->title }}">
    <meta property="og:description" content="{{ $seo->description }}">
    @if ($seo->ogImage)
    <meta property="og:image" content="{{ $seo->ogImage }}">
    @endif

    {{-- Twitter --}}
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $seo->canonical }}">
    <meta property="twitter:title" content="{{ $seo->title }}">
    <meta property="twitter:description" content="{{ $seo->description }}">
    @if ($seo->ogImage)
    <meta property="twitter:image" content="{{ $seo->ogImage }}">
    @endif

    {{-- Breadcrumb Schema --}}
    @if ($seo->breadcrumbs)
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($seo->breadcrumbs)->map(function ($item, $index) {
            return [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ];
        })->toArray()
    ]) !!}
    </script>
    @endif

    {{-- Article/Page Schema --}}
    @if ($seo->schema)
    <script type="application/ld+json">
    {!! json_encode($seo->schema) !!}
    </script>
    @endif

