@extends('layouts.pages')

@section('title', 'Elites International College')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 sm:px-6 py-12 bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="max-w-4xl w-full text-center">
        <div class="relative">
            <!-- Animated background elements -->
            <div class="absolute -top-24 -left-20 w-64 h-64 bg-blue-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
            <div class="absolute -bottom-24 -right-20 w-64 h-64 bg-indigo-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-purple-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
            
            <div class="relative space-y-8 z-10">
                <!-- Animated 404 number -->
                <div class="text-9xl font-extrabold text-gray-800 mb-4 animate__animated animate__bounceIn">
                    <span class="text-blue-600">4</span>
                    <span class="text-indigo-600">0</span>
                    <span class="text-purple-600">4</span>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6 animate__animated animate__fadeInUp animate__delay-1s">
                    429 - Too Many Requests
                </h1>
                
                <p class="text-xl text-gray-600 max-w-2xl mx-auto mb-10 animate__animated animate__fadeInUp animate__delay-1s">
                    You have sent too many requests in a given amount of time. Please wait a moment and try again.
                </p>
                
                <div class="flex flex-col sm:flex-row justify-center gap-4 animate__animated animate__fadeInUp animate__delay-2s">
                    <a href="{{ url('/') }}" 
                       class="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 text-white font-semibold rounded-full shadow-lg transform transition-all duration-300 hover:scale-105 hover:shadow-xl flex items-center justify-center gap-2">
                        <i class="fas fa-home mr-2"></i>
                        Go to Homepage
                    </a>
                    
                    <a href="{{ url()->previous() }}" 
                       class="px-8 py-4 bg-white text-gray-800 font-semibold rounded-full shadow-lg border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-xl flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Go Back
                    </a>
                </div>
                
                <!-- Search form -->
                <div class="mt-12 max-w-xl mx-auto animate__animated animate__fadeInUp animate__delay-3s">
                    <p class="text-gray-600 mb-4">Or search for what you need:</p>
                    <form action="{{ route('home') }}" method="GET" class="relative">
                        <input type="text" name="search"
                               class="w-full px-6 py-4 rounded-full border-0 shadow-lg focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-300"
                               placeholder="Search our site...">
                        <button type="submit" 
                                class="absolute right-2 top-2 bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-3 rounded-full hover:opacity-90 transition-opacity">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Floating content cards -->
        <div class="mt-20 grid grid-cols-2 sm:grid-cols-4 gap-6 max-w-3xl mx-auto">
            <a href="{{ route('about') }}" class="bg-white/80 backdrop-blur-sm p-4 rounded-2xl shadow-lg border border-gray-100 animate-float transform transition hover:scale-105">
                <i class="fas fa-book text-4xl text-blue-600 mb-3"></i>
                <h3 class="font-semibold text-gray-800">Academics</h3>
            </a>
            <a href="{{ route('gallery') }}" class="bg-white/80 backdrop-blur-sm p-4 rounded-2xl shadow-lg border border-gray-100 animate-float animation-delay-500 transform transition hover:scale-105">
                <i class="fas fa-calendar-alt text-4xl text-indigo-600 mb-3"></i>
                <h3 class="font-semibold text-gray-800">Events</h3>
            </a>
            <a href="{{ route('admission') }}" class="bg-white/80 backdrop-blur-sm p-4 rounded-2xl shadow-lg border border-gray-100 animate-float animation-delay-1000 transform transition hover:scale-105">
                <i class="fas fa-graduation-cap text-4xl text-purple-600 mb-3"></i>
                <h3 class="font-semibold text-gray-800">Admissions</h3>
            </a>
            <a href="{{ route('contact') }}" class="bg-white/80 backdrop-blur-sm p-4 rounded-2xl shadow-lg border border-gray-100 animate-float animation-delay-1500 transform transition hover:scale-105">
                <i class="fas fa-user-friends text-4xl text-blue-500 mb-3"></i>
                <h3 class="font-semibold text-gray-800">Community</h3>
            </a>
        </div>
    </div>
</div>

<style>
    @keyframes blob {
        0% {
            transform: translate(0px, 0px) scale(1);
        }
        33% {
            transform: translate(30px, -50px) scale(1.1);
        }
        66% {
            transform: translate(-20px, 20px) scale(0.9);
        }
        100% {
            transform: translate(0px, 0px) scale(1);
        }
    }
    
    @keyframes float {
        0% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-15px);
        }
        100% {
            transform: translateY(0px);
        }
    }
    
    .animate-blob {
        animation: blob 7s infinite;
    }
    
    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
    
    .animation-delay-500 {
        animation-delay: 0.5s;
    }
    
    .animation-delay-1000 {
        animation-delay: 1s;
    }
    
    .animation-delay-1500 {
        animation-delay: 1.5s;
    }
    
    .animation-delay-2000 {
        animation-delay: 2s;
    }
    
    .animation-delay-4000 {
        animation-delay: 4s;
    }
</style>
@endsection
