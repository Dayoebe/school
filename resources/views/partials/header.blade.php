@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
@endpush

<header x-data="{ mobileMenuOpen: false, dropdownOpen: false, scrolled: false }"
    @scroll.window="scrolled = window.scrollY > 10"
    class="bg-white text-gray-800 shadow-sm sticky top-0 z-50 transition-all duration-300"
    :class="{ 'shadow-md': scrolled }">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">

            <!-- Logo & Name -->
            <div class="flex items-center space-x-3 transition transform hover:scale-[1.02]">
                <img src="{{ asset('img/logo.png') }}" alt="Elites Logo"
                    class="w-10 h-10 rounded-full border-2 border-blue-500 shadow-sm animate__animated animate__zoomIn">
                <a href="{{ route('home') }}"
                    class="text-xl font-bold tracking-tight text-gray-900 hover:text-emerald-600 transition duration-300 animate__animated animate__fadeInLeft">
                    Elites International College
                </a>
            </div>

            <div class="flex items-center space-x-6">
                <!-- Desktop Nav -->
                <nav class="hidden lg:flex items-center space-x-1 font-medium">
                    <a href="{{ route('home') }}"
                        class="px-4 py-2 rounded-md hover:bg-gray-50 text-gray-700 hover:text-emerald-600 transition-all duration-200 flex items-center group">
                        <span class="relative after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-emerald-500 after:transition-all after:duration-300 group-hover:after:w-full">Home</span>
                    </a>
                    <a href="{{ route('about') }}"
                        class="px-4 py-2 rounded-md hover:bg-gray-50 text-gray-700 hover:text-emerald-600 transition-all duration-200 flex items-center group">
                        <span class="relative after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-emerald-500 after:transition-all after:duration-300 group-hover:after:w-full">About</span>
                    </a>
                    <a href="{{ route('admission') }}"
                        class="px-4 py-2 rounded-md hover:bg-gray-50 text-gray-700 hover:text-emerald-600 transition-all duration-200 flex items-center group">
                        <span class="relative after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-emerald-500 after:transition-all after:duration-300 group-hover:after:w-full">Admission</span>
                    </a>
                    <a href="{{ route('gallery') }}"
                        class="px-4 py-2 rounded-md hover:bg-gray-50 text-gray-700 hover:text-emerald-600 transition-all duration-200 flex items-center group">
                        <span class="relative after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-emerald-500 after:transition-all after:duration-300 group-hover:after:w-full">Gallery</span>
                    </a>
                    <a href="{{ route('contact') }}"
                        class="px-4 py-2 rounded-md hover:bg-gray-50 text-gray-700 hover:text-emerald-600 transition-all duration-200 flex items-center group">
                        <span class="relative after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-emerald-500 after:transition-all after:duration-300 group-hover:after:w-full">Contact</span>
                    </a>

                    <!-- Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="px-4 py-2 rounded-md hover:bg-gray-50 text-gray-700 hover:text-emerald-600 transition-all duration-200 flex items-center group">
                            <span class="relative after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-emerald-500 after:transition-all after:duration-300 group-hover:after:w-full">More</span>
                            <svg class="w-4 h-4 ml-1 transition-transform transform" :class="{ 'rotate-180': open }"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-lg shadow-lg z-50 py-1">
                            <a href="#"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-emerald-600 transition-colors duration-200">Academics</a>
                            <a href="#"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-emerald-600 transition-colors duration-200">News</a>
                            <a href="#"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-emerald-600 transition-colors duration-200">Careers</a>
                        </div>
                    </div>
                </nav>

                <!-- CTA Button -->
                <a href="{{ route('admission') }}"
                    class="hidden lg:inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 transition-colors duration-200 ml-2">
                    Apply Now
                </a>

                <!-- Login/Register or User Info -->
                <div class="hidden lg:flex items-center space-x-4 ml-2">
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center space-x-2 hover:text-emerald-600 transition-colors duration-200">
                            <img src="{{ Auth::user()->profile_photo_url ?? asset('images/default-avatar.png') }}"
                                alt="Avatar" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                            <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium text-gray-700 hover:text-emerald-600 transition-colors duration-200">
                            Login
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Mobile Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden focus:outline-none">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" x-transition
        class="lg:hidden bg-white border-t border-gray-200 px-4 pt-2 pb-4 space-y-1">
        <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-emerald-600">Home</a>
        <a href="{{ route('about') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-emerald-600">About</a>
        <a href="{{ route('admission') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-emerald-600">Admission</a>
        <a href="{{ route('gallery') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-emerald-600">Gallery</a>
        <a href="{{ route('contact') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-emerald-600">Contact</a>
        
        <div class="pt-2 border-t border-gray-200">
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex justify-between items-center px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-emerald-600">
                    <span>More</span>
                    <svg class="w-4 h-4 transform transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" class="pl-4 space-y-1 mt-1">
                    <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-emerald-600">Academics</a>
                    <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-emerald-600">News</a>
                    <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-emerald-600">Careers</a>
                </div>
            </div>
        </div>
        
        <div class="pt-2">
            <a href="{{ route('admission') }}" class="block w-full px-4 py-2 text-center rounded-md border border-transparent font-medium text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm">
                Apply Now
            </a>
        </div>
        
        <div class="pt-2 border-t border-gray-200">
            @auth
                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-emerald-600">
                    <img src="{{ Auth::user()->profile_photo_url ?? asset('images/default-avatar.png') }}" alt="Avatar" class="w-6 h-6 rounded-full mr-2 object-cover border border-gray-200">
                    My Account
                </a>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-emerald-600">Login</a>
            @endauth
        </div>
    </div>
</header>