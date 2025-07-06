@extends('layouts.pages')

@section('title', 'Server Error')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 sm:px-6 py-12 bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="max-w-4xl w-full text-center">
        <div class="relative">
            <!-- Animated background elements (same as 404) -->
            <div class="absolute -top-24 -left-20 w-64 h-64 bg-blue-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
            <div class="absolute -bottom-24 -right-20 w-64 h-64 bg-indigo-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-purple-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
            
            <div class="relative space-y-8 z-10">
                <!-- Animated 500 number -->
                <div class="text-9xl font-extrabold text-gray-800 mb-4 animate__animated animate__bounceIn">
                    <span class="text-red-600">5</span>
                    <span class="text-orange-600">0</span>
                    <span class="text-red-600">0</span>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6 animate__animated animate__fadeInUp animate__delay-1s">
                    500 - Internal Server Error
                </h1>
                
                <p class="text-xl text-gray-600 max-w-2xl mx-auto mb-10 animate__animated animate__fadeInUp animate__delay-1s">
                    Sorry, something went wrong on our end. We're working to fix it. Please try again later.
                </p>
                
                <div class="flex flex-col sm:flex-row justify-center gap-4 animate__animated animate__fadeInUp animate__delay-2s">
                    <a href="{{ url('/') }}" 
                       class="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 text-white font-semibold rounded-full shadow-lg transform transition-all duration-300 hover:scale-105 hover:shadow-xl flex items-center justify-center gap-2">
                        <i class="fas fa-home mr-2"></i>
                        Go to Homepage
                    </a>
                    
                    <a href="mailto:support@example.com" 
                       class="px-8 py-4 bg-white text-gray-800 font-semibold rounded-full shadow-lg border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-xl flex items-center justify-center gap-2">
                        <i class="fas fa-envelope mr-2"></i>
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection