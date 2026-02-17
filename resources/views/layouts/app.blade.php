<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    {{-- SEO Meta Tags --}}
    <meta name="description" content="Elite International College, Awka – A top-tier secondary school dedicated to academic excellence, character development, and future leadership.">
    <meta name="keywords" content="Elite International College, Awka, Secondary School in Awka, Nigerian Schools, WAEC, NECO, Best School in Anambra, College Education, Private School Awka">
    <meta name="author" content="Elite International College, Awka">
    <meta name="robots" content="index, follow">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset(config('app.favicon', 'logo.png')) }}" type="image/png">

    {{-- Open Graph / Facebook Meta Tags --}}
    <meta property="og:title" content="@yield('title', 'Elite International College, Awka')">
    <meta property="og:description" content="Excellence in education, character, and leadership. Explore admissions, academics, and student life at Elite International College, Awka.">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Elite International College, Awka">

    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Elite International College, Awka')">
    <meta name="twitter:description" content="A leading secondary school in Anambra State focused on academic and moral excellence.">
    <meta name="twitter:image" content="{{ asset('logo.png') }}">

    {{-- Page Title --}}
    <title>@yield('title', config('app.name', 'Elite International College, Awka'))</title>

    {{-- Styles --}}
    @vite('resources/css/app.css')
    <livewire:styles />
</head>


<body class="font-sans">
    <a href="#main" class="sr-only">
        Skip to content
    </a>
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
                            @endif
                        </div>
                    </div>
                    <main class="p-4" id="main">
                        @yield('content')
                    </main>
                </div>
            </div>
        </div>
        @livewire('common.display-status')
    </body>
    <livewire:scripts />
    @stack('scripts')

    </html>
