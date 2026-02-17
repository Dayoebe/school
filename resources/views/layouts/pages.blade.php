<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        {{-- SEO Meta Tags --}}
        <meta name="description" content="Elite International College, Awka is a reputable secondary school in Nigeria, committed to excellence in academics, moral values, and leadership training.">
        <meta name="keywords" content="Elite International College, Awka, Secondary School, Anambra Education, Private School Nigeria, WAEC School, NECO School, College Admission, Best School Awka">
        <meta name="author" content="Elite International College, Awka">
        <meta name="robots" content="index, follow">
    
        {{-- Canonical URL --}}
        <link rel="canonical" href="{{ url()->current() }}">
    
        {{-- Favicon --}}
        <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    
        {{-- Open Graph / Facebook --}}
        <meta property="og:title" content="@yield('title', 'Elite International College, Awka')">
        <meta property="og:description" content="Explore Elite International College, Awka – fostering excellence in academics, leadership, and character. Learn about our curriculum, activities, and admission process.">
        <meta property="og:image" content="{{ asset('logo.png') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Elite International College, Awka">
    
        {{-- Twitter Card --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="@yield('title', 'Elite International College, Awka')">
        <meta name="twitter:description" content="Leading secondary school in Awka, Anambra State. Excellence in learning, leadership, and life skills.">
        <meta name="twitter:image" content="{{ asset('logo.png') }}">
    
        {{-- Title --}}
        <title>@yield('title', config('app.name', 'Elite International College, Awka'))</title>
    
        {{-- Styles --}}
        @vite('resources/css/app.css')
        <livewire:styles />
    

        
        {{-- Alpine.js --}}
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
        
        {{-- Animate.css --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" integrity="sha512-Bas9uDUpx+1EtuAr+HwW7Bz7EAlEoLEvksByx1WZbW0UozUAVsTIiBSVmPbZ7w8eGDFwQmAq+KpquzyTrKkZ9Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
        {{-- Font Awesome --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-vEcZ4KblMQUgFnzZFMbmqTqHZx+4Xx+BXvPtAbv+eZ2+6R+RyFV1Rmopm8eoRmM5Wr7MoQGcDHh9f7Q3PvA0jw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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

    @livewire('common.display-status')
    <livewire:scripts />
    @stack('scripts')
</body>
</html>
