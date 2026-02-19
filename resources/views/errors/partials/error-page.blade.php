<div class="min-h-screen flex items-center justify-center px-4 sm:px-6 py-12 bg-gray-100 dark:bg-gray-900">
    <div class="max-w-3xl w-full">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-8 sm:p-10 text-center">
            <p class="text-xs font-semibold tracking-widest uppercase text-gray-500 dark:text-gray-400">Error</p>
            <h1 class="text-6xl sm:text-7xl font-extrabold mt-2 text-blue-600">{{ $code }}</h1>
            <h2 class="mt-4 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">{{ $heading }}</h2>
            <p class="mt-4 text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">{{ $message }}</p>

            <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-white font-semibold hover:bg-blue-700 transition-colors">
                    <i class="fas fa-home mr-2"></i>
                    Go to Homepage
                </a>

                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 px-6 py-3 font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Go Back
                </a>
            </div>

            <div class="mt-10 grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                <a href="{{ route('about') }}" class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700">About</a>
                <a href="{{ route('admission') }}" class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700">Admission</a>
                <a href="{{ route('gallery') }}" class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700">Gallery</a>
                <a href="{{ route('contact') }}" class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700">Contact</a>
            </div>
        </div>
    </div>
</div>
