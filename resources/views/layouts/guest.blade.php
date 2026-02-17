<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    
        {{-- SEO Meta Tags --}}
        <meta name="description" content="Welcome to Elite International College, Awka – A center of academic excellence, moral discipline, and leadership training. Access your account, manage results, and explore resources.">
        <meta name="keywords" content="Elite International College, Awka, School Portal, Student Login, Parent Portal, Nigerian Schools, Secondary School Education, Anambra Schools">
        <meta name="author" content="Elite International College, Awka">
        <meta name="robots" content="index, follow">
    
        {{-- Canonical Link --}}
        <link rel="canonical" href="{{ url()->current() }}">
    
        {{-- Favicon --}}
        <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    
        {{-- Open Graph (Facebook, WhatsApp) --}}
        <meta property="og:title" content="@yield('title', 'Elite International College, Awka')">
        <meta property="og:description" content="Elite International College, Awka student and parent portal. Access school services, results, and updates.">
        <meta property="og:image" content="{{ asset('logo.png') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Elite International College, Awka">
    
        {{-- Twitter Card --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="@yield('title', 'Elite International College, Awka')">
        <meta name="twitter:description" content="Secure login to Elite International College's school portal for students, teachers, and parents.">
        <meta name="twitter:image" content="{{ asset('logo.png') }}">
    
        {{-- Title --}}
        <title>@yield('title', config('app.name', 'Elite International College, Awka'))</title>
    
        {{-- Styles --}}
        @vite('resources/css/app.css')
        <livewire:styles />
    </head>
    
    <body class="bg-gray-100 mx-5">
        @yield('body')
        <livewire:common.display-status />
    </body>
    <livewire:scripts />
</html>
