@php
    $publicNavItems = [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'About', 'route' => 'about'],
        ['label' => 'Admission', 'route' => 'admission'],
        ['label' => 'Gallery', 'route' => 'gallery'],
        ['label' => 'Contact', 'route' => 'contact'],
    ];

    $settings = $publicSiteSettings ?? [];
    $schoolName = (string) data_get($settings, 'school_name', config('app.name', 'School Portal'));
    $schoolLocation = (string) data_get($settings, 'school_location', 'Awka, Anambra');

    $contactAddress = (string) data_get($settings, 'contact.address', '');
    $contactPhonePrimary = (string) data_get($settings, 'contact.phone_primary', '');
    $contactEmail = (string) data_get($settings, 'contact.email', '');

    $phoneHref = preg_replace('/[^0-9+]/', '', $contactPhonePrimary);
    $emailHref = trim($contactEmail);

    $themeLogoUrl = trim((string) data_get($settings, 'theme.logo_url', ''));
    $logoUrl = $themeLogoUrl !== '' ? $themeLogoUrl : ($publicSiteSchool?->logo_url ?? asset(config('app.logo', 'img/logo.png')));
@endphp

<header
    x-data="{ mobileOpen: false, accountOpen: false, scrolled: false }"
    @scroll.window="scrolled = window.scrollY > 8"
    @keydown.escape.window="mobileOpen = false; accountOpen = false"
    class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/95 backdrop-blur transition-all duration-300"
    :class="scrolled ? 'shadow-lg shadow-slate-900/5' : 'shadow-none'">

    <div class="hidden border-b border-slate-200 bg-slate-50 lg:block">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-2 text-xs text-slate-600">
            <p class="flex items-center gap-2">
                <i class="fas fa-location-dot text-red-600"></i>
                <span>{{ $contactAddress }}</span>
            </p>
            <div class="flex items-center gap-5">
                @if ($contactPhonePrimary !== '')
                    <a href="tel:{{ $phoneHref }}" class="transition hover:text-orange-700">
                        <i class="fas fa-phone mr-1"></i>{{ $contactPhonePrimary }}
                    </a>
                @endif
                @if ($contactEmail !== '')
                    <a href="mailto:{{ $emailHref }}" class="transition hover:text-amber-700">
                        <i class="fas fa-envelope mr-1"></i>{{ $contactEmail }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-3">
            <a href="{{ route('home') }}" class="group flex items-center gap-3">
                <img src="{{ $logoUrl }}" alt="{{ $schoolName }} Logo"
                    class="h-10 w-10 rounded-full border border-amber-300 object-cover shadow-sm transition group-hover:scale-105 sm:h-11 sm:w-11">
                <div class="min-w-0">
                    <p class="truncate text-sm font-black leading-tight text-slate-900 sm:text-base">
                        {{ $schoolName }}
                    </p>
                    <p class="text-xs font-semibold text-orange-700">{{ $schoolLocation }}</p>
                </div>
            </a>

            <nav class="hidden items-center gap-1 lg:flex">
                @foreach ($publicNavItems as $item)
                    <a href="{{ route($item['route']) }}"
                        @class([
                            'rounded-xl px-4 py-2 text-sm font-semibold transition',
                            'bg-red-100 text-red-700' => request()->routeIs($item['route']),
                            'text-slate-700 hover:bg-slate-100 hover:text-slate-900' => !request()->routeIs($item['route']),
                        ])>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-2 lg:flex">
                <a href="{{ route('admission') }}"
                    class="site-primary-bg inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold text-white transition hover:opacity-90">
                    <i class="fas fa-user-plus text-xs"></i>
                    <span>Apply Now</span>
                </a>

                @auth
                    <div class="relative">
                        <button type="button" @click="accountOpen = !accountOpen"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                            <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}"
                                class="h-7 w-7 rounded-full border border-slate-200 object-cover">
                            <span class="max-w-[110px] truncate">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down text-xs text-slate-500 transition"
                                :class="{ 'rotate-180': accountOpen }"></i>
                        </button>

                        <div x-show="accountOpen" x-cloak @click.away="accountOpen = false" x-transition
                            class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
                            <a href="{{ route('dashboard') }}"
                                class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                <i class="fas fa-chart-line text-blue-600"></i>Dashboard
                            </a>
                            @if (Route::has('profile.edit'))
                                <a href="{{ route('profile.edit') }}"
                                    class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    <i class="fas fa-user text-indigo-600"></i>Profile
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center gap-2 px-4 py-3 text-left text-sm font-medium text-rose-700 transition hover:bg-rose-50">
                                    <i class="fas fa-right-from-bracket"></i>Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                        <i class="fas fa-right-to-bracket text-xs"></i>
                        <span>Login</span>
                    </a>
                @endauth
            </div>

            <button type="button" @click="mobileOpen = !mobileOpen"
                class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-300 text-slate-700 transition hover:bg-slate-100 lg:hidden"
                aria-label="Toggle menu">
                <i class="fas text-lg" :class="mobileOpen ? 'fa-times' : 'fa-bars'"></i>
            </button>
        </div>
    </div>

    <div x-show="mobileOpen" x-cloak x-transition class="border-t border-slate-200 bg-white lg:hidden">
        <div class="mx-auto max-w-7xl space-y-2 px-4 py-3 sm:px-6">
            @foreach ($publicNavItems as $item)
                <a href="{{ route($item['route']) }}"
                    @class([
                        'block rounded-xl px-4 py-3 text-sm font-semibold transition',
                        'bg-red-100 text-red-700' => request()->routeIs($item['route']),
                        'text-slate-700 hover:bg-slate-100' => !request()->routeIs($item['route']),
                    ])>
                    {{ $item['label'] }}
                </a>
            @endforeach

            <div class="grid grid-cols-1 gap-2 pt-2">
                <a href="{{ route('admission') }}"
                    class="site-primary-bg inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold text-white transition hover:opacity-90">
                    <i class="fas fa-user-plus text-xs"></i>
                    <span>Apply Now</span>
                </a>

                @auth
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        <i class="fas fa-chart-line text-xs"></i>
                        <span>Dashboard</span>
                    </a>

                    @if (Route::has('profile.edit'))
                        <a href="{{ route('profile.edit') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                            <i class="fas fa-user text-xs"></i>
                            <span>Profile</span>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                            <i class="fas fa-right-from-bracket text-xs"></i>
                            <span>Log Out</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        <i class="fas fa-right-to-bracket text-xs"></i>
                        <span>Login</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>
