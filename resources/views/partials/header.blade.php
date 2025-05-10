@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
@endpush

<header x-data="{ mobileMenuOpen: false, dropdownOpen: false }"
    class="bg-gradient-to-br from-blue-400 via-gray-700 to-blue-600 text-white shadow-md sticky top-0 z-50 animate__animated animate__fadeInDown">
    <div class="max-w-1xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">

            <!-- Logo & Name -->
            <div class="flex items-center space-x-3 transition transform hover:scale-105">
                <img src="{{ asset('img/logo.png') }}" alt="Elites Logo"
                    class="w-10 h-10 rounded-full animate__animated animate__zoomIn">
                <a href="{{ route('home') }}"
                    class="hover:uppercase text-3xl font-bold tracking-wide hover:text-blue-300 transition duration-300 animate__animated animate__fadeInLeft">
                    Elites International College
                </a>
            </div>


            <div class="flex items-center space-x-6">

            
            <!-- Desktop Nav -->
            <nav class="hidden lg:flex items-center space-x-6 font-medium animate__animated animate__fadeIn">
                <a href="{{ route('home') }}" class=" hover:uppercase hover:text-blue-300 text-grey-600 px-4 py-2 rounded-lg shadow transition transform hover:scale-105">Home</a>
                <a href="{{ route('about') }}" class="hover:uppercase hover:text-blue-300 text-grey-600 px-4 py-2 rounded-lg shadow transition transform hover:scale-105">About</a>
                <a href="{{ route('admission') }}" class="hover:uppercase hover:text-blue-300 text-grey-600 px-4 py-2 rounded-lg shadow transition transform hover:scale-105">Admission</a>
                <a href="{{ route('gallery') }}" class="hover:uppercase hover:text-blue-300 text-grey-600 px-4 py-2 rounded-lg shadow transition transform hover:scale-105">Gallery</a>
                <a href="{{ route('contact') }}" class="hover:uppercase hover:text-blue-300 text-grey-600 px-4 py-2 rounded-lg shadow transition transform hover:scale-105">Contact</a>

                <!-- Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="hover:uppercase hover:text-blue-300 text-grey-600 px-4 py-2 rounded-lg shadow transition transform hover:scale-105 flex items-center gap-1">
                        More
                        <svg class="w-4 h-4 transition-transform transform" :class="{ 'rotate-180': open }"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-40 bg-white text-black rounded shadow-lg z-50 py-2">
                        <a href="#" class="hover:uppercase transition transform hover:scale-105 block px-4 py-2 hover:bg-blue-100">Academics</a>
                        <a href="#" class="hover:uppercase transition transform hover:scale-105 block px-4 py-2 hover:bg-blue-100">News</a>
                        <a href="#" class="hover:uppercase transition transform hover:scale-105 block px-4 py-2 hover:bg-blue-100">Careers</a>
                    </div>
                </div>

                <!-- CTA -->
                <a href="{{ route('admission') }}"
                    class="hover:uppercase hover:bg-white hover:text-blue-600 text-grey-600 px-4 py-2 rounded-lg shadow transition transform hover:scale-105">
                    Apply Now
                </a>
            </nav>

            <!-- Login/Register or User Info -->
            <div class="hidden lg:flex space-x-4 items-center animate__animated animate__fadeInRight">
                @auth
                    <a href="{{ route('dashboard') }}" class="hover:uppercase flex items-center space-x-2 hover:text-blue-300">
                        <img src="{{ Auth::user()->profile_photo_url ?? asset('images/default-avatar.png') }}"
                            alt="Avatar" class="w-8 h-8 rounded-full object-cover border border-white">
                        <span>{{ Auth::user()->name }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hover:uppercase bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow transition transform hover:scale-105">Login</a>
                @endauth
            </div>

        </div>
        
            <!-- Mobile Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden focus:outline-none text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round"
                        d="M4 6h16M4 12h16M4 18h16" />
                    <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" x-transition
        class="lg:hidden bg-[#0a1a3c] px-4 pb-4 space-y-2 animate__animated animate__fadeIn">
        <a href="{{ route('home') }}" class="hover:uppercase block py-2 text-white hover:text-blue-300">Home</a>
        <a href="{{ route('about') }}" class="hover:uppercase block py-2 text-white hover:text-blue-300">About</a>
        <a href="{{ route('admission') }}" class="hover:uppercase block py-2 text-white hover:text-blue-300">Admission</a>
        <a href="{{ route('gallery') }}" class="hover:uppercase block py-2 text-white hover:text-blue-300">Gallery</a>
        <a href="{{ route('contact') }}" class=" block py-2 text-white hover:text-blue-300">Contact</a>
        <a href="#" class="hover:uppercase block py-2 text-white hover:text-blue-300">Academics</a>
        <a href="#" class="hover:uppercase block py-2 text-white hover:text-blue-300">News</a>
        <a href="#" class="hover:uppercase block py-2 text-white hover:text-bhover:uppercaselue-300">Careers</a>
        <a href="{{ route('admission') }}"
            class="hover:uppercase block py-2 text-white font-semibold bg-blue-500 rounded text-center hover:bg-blue-600">Apply Now</a>


        <!-- Login/Register -->
        <div class="border-t border-blue-900 mt-2 pt-2 space-y-1">
            @auth
                <a href="{{ route('dashboard') }}" class="hover:uppercase flex items-center space-x-2 text-white hover:text-blue-300">
                    <img src="{{ Auth::user()->profile_photo_url ?? asset('images/default-avatar.png') }}" alt="Avatar"
                        class="w-6 h-6 rounded-full object-cover border border-white">
                    <span>{{ Auth::user()->name }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="hover:uppercase block text-white hover:text-blue-300">Login</a>
            @endauth
        </div>

    </div>
</header>
