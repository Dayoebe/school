@php
    $layoutMode = $mode ?? null;
    $hasBodySection = trim($__env->yieldContent('body')) !== '';

    $isPrintMode = $layoutMode === 'print';
    $isGuestMode = $layoutMode === 'guest' || (!$isPrintMode && $hasBodySection);
    $isPublicMode = $layoutMode === 'public';
    $isDashboardMode = !$isPrintMode && !$isGuestMode && !$isPublicMode && auth()->check();

    $publicSettings = $publicSiteSettings ?? [];
    $currentRouteName = request()->route()?->getName();
    $metaLocale = str_replace('_', '-', app()->getLocale());
    $publicSeoPages = \App\Support\PublicSeo::pages($publicSettings);
    $isSeoPublicPage = $isPublicMode && array_key_exists((string) $currentRouteName, $publicSeoPages);
    $seoPageMeta = $isSeoPublicPage ? $publicSeoPages[(string) $currentRouteName] : null;

    $metaSiteName = \App\Support\PublicSeo::siteName($publicSettings);
    $metaDescription = $seoPageMeta['description'] ?? \App\Support\PublicSeo::limit((string) data_get($publicSettings, 'meta.description', 'School portal and services.'), 160);
    $metaKeywords = \App\Support\PublicSeo::plain((string) data_get($publicSettings, 'meta.keywords', 'School Portal, Results, Exams, Admissions'));
    $metaAuthor = \App\Support\PublicSeo::plain((string) data_get($publicSettings, 'meta.author', $metaSiteName));
    $metaOgDescription = $metaDescription;
    $metaRobots = $isSeoPublicPage
        ? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'
        : 'noindex, nofollow';
    $canonicalUrl = $seoPageMeta['canonical'] ?? url()->current();

    $themePrimaryColor = (string) data_get($publicSettings, 'theme.primary_color', '#dc2626');
    if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $themePrimaryColor)) {
        $themePrimaryColor = '#dc2626';
    }

    $hex = ltrim($themePrimaryColor, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    $themePrimaryRgb = sprintf(
        '%d, %d, %d',
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    );

    $themeLogoMeta = \App\Support\PublicSeo::defaultImage($publicSettings);

    // Always use school logo as favicon.
    $themeFavicon = trim((string) data_get($publicSettings, 'theme.favicon_url', '')) ?: $themeLogoMeta;
    if ($themeFavicon === '') {
        $themeFavicon = asset(config('app.logo', 'logo.png'));
    }
    $themeFavicon = \App\Support\PublicSeo::absoluteUrl($themeFavicon);

    $metaOgImage = $seoPageMeta['image'] ?? $themeLogoMeta;
    $metaTitle = $seoPageMeta['title'] ?? \App\Support\PublicSeo::limit(trim($__env->yieldContent('title', $metaSiteName)) . ' | ' . $metaSiteName, 80);
    $metaOgType = 'website';
    $twitterCard = 'summary_large_image';

    $jsonLdSchemas = $isSeoPublicPage
        ? [\App\Support\PublicSeo::schema((string) $currentRouteName, $publicSettings, $publicSiteSchool ?? null)]
        : [];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="author" content="{{ $metaAuthor }}">
    <meta name="robots" content="{{ $metaRobots }}">
    <meta name="googlebot" content="{{ $metaRobots }}">
    <meta name="application-name" content="{{ $metaSiteName }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $metaSiteName }}">
    <meta name="theme-color" content="{{ $themePrimaryColor }}">

    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="sitemap" type="application/xml" href="{{ route('seo.sitemap') }}">
    <link rel="alternate" type="text/plain" title="LLMS" href="{{ route('seo.llms') }}">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <link rel="icon" href="{{ $themeFavicon }}" type="image/png">
    <link rel="shortcut icon" href="{{ $themeFavicon }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ $themeLogoMeta }}">

    @if ($isSeoPublicPage)
        <link rel="alternate" hreflang="{{ $metaLocale }}" href="{{ $canonicalUrl }}">
        <link rel="alternate" hreflang="x-default" href="{{ $canonicalUrl }}">
    @endif

    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaOgDescription }}">
    <meta property="og:image" content="{{ $metaOgImage }}">
    <meta property="og:image:alt" content="{{ $metaSiteName }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:type" content="{{ $metaOgType }}">
    <meta property="og:site_name" content="{{ $metaSiteName }}">
    <meta property="og:locale" content="{{ str_replace('-', '_', $metaLocale) }}">

    <meta name="twitter:card" content="{{ $twitterCard }}">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaOgDescription }}">
    <meta name="twitter:image" content="{{ $metaOgImage }}">
    <meta name="twitter:image:alt" content="{{ $metaSiteName }}">

    @foreach ($jsonLdSchemas as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endforeach

    <title>{{ $metaTitle }}</title>

    @if ($isPrintMode)
        <style>
            * { font-family: Helvetica, sans-serif; }
            body { background-color: white; }
            header { display: table; width: 100%; margin-bottom: 1rem; }
            main { width: 100%; }
            .logo-wrapper { display: table-cell; vertical-align: middle; width: 5%; }
            .site-identity { display: table-cell; width: 95%; }
            .site-identity * { text-align: center; }
            .logo { width: 100px; height: 100px; border-radius: 50px; background: white; object-fit: contain; padding: 6px; }
            p { padding: 0.45rem; }
            h1, h2, h3, h4, h5, h6 { text-align: center; }
            h1 { font-size: 2rem; }
            h2 { font-size: 1.2rem; }
            table, th, td { border: 1px solid rgba(46, 45, 45, 0.854); border-collapse: collapse; }
            table { width: 100%; vertical-align: middle; text-align: center; }
            th { font-weight: 700; }
            td, th { padding: 0.75rem; }
        </style>
        @yield('style')
    @else
        <style>
            :root {
                --site-primary: {{ $themePrimaryColor }};
                --site-primary-rgb: {{ $themePrimaryRgb }};
            }

            .site-primary-bg { background-color: var(--site-primary) !important; }
            .site-primary-text { color: var(--site-primary) !important; }
            .site-primary-border { border-color: var(--site-primary) !important; }
            .site-primary-soft { background-color: rgba(var(--site-primary-rgb), 0.12) !important; }
            .site-primary-soft-border { border-color: rgba(var(--site-primary-rgb), 0.3) !important; }
        </style>

        @vite('resources/css/app.css')
        <livewire:styles />

        @stack('head')
    @endif

    @if (!$isPrintMode)
        @include('partials.pwa-register', ['pwaThemeColor' => $themePrimaryColor])
    @endif
</head>

@if ($isPrintMode)
    <body>
        <header>
            <div class="logo-wrapper">
                <img src="{{ auth()->user()->school->logoURL ?? public_path() . '/' . config('app.logo') }}" alt="" class="logo">
            </div>
            <div class="site-identity">
                <h1>{{ auth()->user()->school->name }}</h1>
                <h2>{{ auth()->user()->school->address }}</h2>
            </div>
        </header>
        <main>@yield('content')</main>
    </body>
@elseif ($isGuestMode)
    <body class="bg-gray-100 mx-5">
        @yield('body')
        <livewire:common.display-status />
        <livewire:scripts />
        @stack('scripts')
    </body>
@elseif ($isPublicMode || !auth()->check())
    <body class="font-sans text-gray-900 bg-white dark:bg-gray-900 dark:text-gray-100">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 p-2 bg-blue-600 text-white z-50">
            Skip to content
        </a>

        <div x-data="{ menuOpen: false }">
            @include('partials.header')
            <main id="main" class="min-h-screen py-10">
                @yield('content')
            </main>
            @include('partials.footer')
        </div>

        @livewire('common.display-status')
        <livewire:scripts />
        @stack('scripts')
    </body>
@else
    <body class="font-sans">
        <a href="#main" class="sr-only">Skip to content</a>
        <div x-data="{ menuOpen: window.innerWidth >= 1024 ? $persist(false) : false }">
            <livewire:layouts.header />
            <div class="lg:flex lg:flex-cols text-gray-900 bg-gray-100 dark:bg-gray-700 dark:text-gray-50 min-h-screen">
                <livewire:layouts.menu />
                <div class="w-full max-w-full overflow-scroll beautify-scrollbar">
                    <div class="bg-white dark:bg-gray-800 p-4 w-full border-b-2">
                        <h1 class="text-3xl my-2 capitalize font-semibold">@yield('page_heading')</h1>
                        <div class="w-full">
                            <x-show-set-school />
                        </div>
                        <div class="w-full">
                            @isset($breadcrumbs)
                                <x-breadcrumbs :paths="$breadcrumbs" />
                            @endisset
                        </div>
                    </div>
                    <main class="p-4" id="main">
                        @yield('content')
                    </main>
                </div>
            </div>

            @include('partials.dashboard-floating-countdown')
        </div>
        @livewire('common.display-status')
        <livewire:scripts />
        @stack('scripts')
    </body>
@endif
</html>
