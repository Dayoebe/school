<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="index, follow">
    
    <link rel="shortcut icon" href="{{ asset(config('app.favicon')) }}" type="image/x-icon">
    <title>@yield('title', config('app.name', 'Skuul'))</title>

    @vite('resources/css/app.css')
    <livewire:styles />
    <!-- Animate.css CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

</head>
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

    @livewire('display-status')
    <livewire:scripts />
    @stack('scripts')
</body>
</html>
