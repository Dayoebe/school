@extends('layouts.pages')

@section('title', 'Elites International College - Page Not Found')

@section('content')
<section 
    x-data="{ show: false }" 
    x-init="setTimeout(() => show = true, 200)" 
    x-show="show"
    x-transition:enter="transition ease-out duration-700"
    x-transition:enter-start="opacity-0 translate-y-10"
    x-transition:enter-end="opacity-100 translate-y-0"
    class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-tr from-blue-900 to-indigo-900 text-white text-center px-6 py-16"
>
    <img 
        src="https://miro.medium.com/v2/resize:fit:640/format:webp/1*YWUpnY_zNbSfK62GSJIBbw.png" 
        alt="Page Not Found" 
        class="w-64 md:w-96 mb-10 animate-pulse"
    />

    <h1 class="text-6xl md:text-7xl font-extrabold tracking-tight mb-4 text-white">404</h1>
    <p class="text-xl md:text-2xl mb-6">Oops! The page you’re looking for doesn’t exist.</p>

    <div class="flex flex-col md:flex-row gap-4">
        <a href="{{ route('dashboard') }}"
           class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition duration-300">
            Go Home
        </a>
        <a href="{{ route('contact') }}"
           class="px-6 py-3 bg-white text-blue-700 hover:bg-gray-200 font-semibold rounded-xl transition duration-300">
            Contact Support
        </a>
    </div>

    <div class="mt-10 text-sm text-grey-600">
        Or try visiting one of our popular pages:
        <ul class="mt-2 space-y-1">
            <li><a href="{{ route('about') }}" class="underline hover:text-white">About Us</a></li>
            <li><a href="{{ route('dashboard') }}" class="underline hover:text-white">Dashboard</a></li>
            <li><a href="{{ route('login') }}" class="underline hover:text-white">Log in</a></li>
        </ul>
    </div>
</section>
@endsection
