@php
    $layoutMode = $mode ?? null;
    $hasBodySection = trim($__env->yieldContent('body')) !== '';

    $isPrintMode = $layoutMode === 'print';
    $isGuestMode = $layoutMode === 'guest' || (!$isPrintMode && $hasBodySection);
    $isPublicMode = $layoutMode === 'public';
    $isDashboardMode = !$isPrintMode && !$isGuestMode && !$isPublicMode && auth()->check();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="Elite International College, Awka portal and services.">
    <meta name="keywords" content="Elite International College, Awka, School Portal, Results, Exams, Admissions">
    <meta name="author" content="Elite International College, Awka">
    <meta name="robots" content="index, follow">

    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset(config('app.favicon', 'logo.png')) }}" type="image/png">

    <meta property="og:title" content="@yield('title', config('app.name', 'Elite International College, Awka'))">
    <meta property="og:description" content="Elite International College portal for learning, exams, and result management.">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Elite International College, Awka">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', config('app.name', 'Elite International College, Awka'))">
    <meta name="twitter:description" content="Elite International College portal for learning, exams, and result management.">
    <meta name="twitter:image" content="{{ asset('logo.png') }}">

    <title>@yield('title', config('app.name', 'Elite International College, Awka'))</title>

    @if ($isPrintMode)
        <style>
            * { font-family: Helvetica, sans-serif; }
            body { background-color: white; }
            header { display: table; width: 100%; margin-bottom: 1rem; }
            main { width: 100%; }
            .logo-wrapper { display: table-cell; vertical-align: middle; width: 5%; }
            .site-identity { display: table-cell; width: 95%; }
            .site-identity * { text-align: center; }
            .logo { width: 100px; height: 100px; border-radius: 50px; }
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
        @vite('resources/css/app.css')
        <livewire:styles />

        @if ($isPublicMode)
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
        @endif
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
        </div>
        @livewire('common.display-status')
        <livewire:scripts />
        @stack('scripts')
    </body>
@endif
</html>
