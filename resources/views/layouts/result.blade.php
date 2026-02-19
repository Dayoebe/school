<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <meta name="description"
        content="Elite International College, Awka - Results management portal for secure, accurate academic records.">
    <meta name="keywords"
        content="Elite International College, Results, Academic Records, School Report, Result Management">
    <meta name="author" content="Elite International College, Awka">
    <meta name="robots" content="index, follow">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset(config('app.favicon', 'logo.png')) }}" type="image/png">

    <meta property="og:title" content="{{ $title ?? config('app.name', 'Elite International College, Awka') }}">
    <meta property="og:description" content="Results dashboard for uploads, analytics, and class/student performance tracking.">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Elite International College, Awka">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? config('app.name', 'Elite International College, Awka') }}">
    <meta name="twitter:description" content="Results dashboard for uploads, analytics, and class/student performance tracking.">
    <meta name="twitter:image" content="{{ asset('logo.png') }}">

    <title>{{ $title ?? config('app.name', 'Elite International College, Awka') }}</title>

    @vite('resources/css/app.css')
    <livewire:styles />
</head>

<body class="font-sans">
    <a href="#main" class="sr-only">Skip to content</a>

    <div x-data="{ menuOpen: window.innerWidth >= 1024 ? $persist(false) : false }">
        <livewire:layouts.header />

        <div class="lg:flex lg:flex-cols text-gray-900 bg-gray-100 dark:bg-gray-700 dark:text-gray-50 min-h-screen">
            <livewire:layouts.menu />

            <div class="w-full max-w-full overflow-scroll beautify-scrollbar">
                <div class="bg-white dark:bg-gray-800 p-4 w-full border-b-2">
                    <h1 class="text-3xl my-2 capitalize font-semibold flex items-center gap-2">
                        @if (!empty($icon))
                            <i class="{{ $icon }}"></i>
                        @else
                            <i class="fas fa-chart-bar text-emerald-600 dark:text-emerald-400"></i>
                        @endif
                        <span>{{ $title ?? 'Results' }}</span>
                    </h1>

                    @if (!empty($description))
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ $description }}</p>
                    @endif

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
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewire('common.display-status')
    </div>

    <livewire:scripts />
    @stack('scripts')
</body>

</html>
