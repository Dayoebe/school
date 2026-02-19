<nav class="fixed w-0 lg:sticky inset-0 h-screen shadow-lg flex duration-250 text-gray-100 transition-all dark:lg:border-r dark:border-white z-40"
    :class="{'w-0 overflow-hidden lg:flex lg:w-20' : menuOpen == false, 'w-full lg:w-72' : menuOpen == true}">
    <aside
        class="w-4/6 md:w-3/6 lg:w-full bg-gray-800 dark:bg-gray-900 shadow-md shadow-black border-x border-black overflow-scroll beautify-scrollbar">
        <a href="{{ route('home') }}" class="flex lg:hidden items-center justify-center border-b border-gray-200 p-4">
            <img src="{{ asset(auth()->user()->school->logoURL ?? config('app.logo')) }}" alt="School Logo"
                class="rounded-full w-14 h-14 border border-gray-200 shadow-md">
            <h1 class="text-lg font-semibold mx-3 text-center capitalize">{{ config('app.name') }}</h1>
        </a>

        <div class="p-3">
            @php
                $sections = [];
                $currentHeader = null;
                $currentItems = [];

                foreach ($menu ?? [] as $item) {
                    if (isset($item['header'])) {
                        if ($currentItems !== []) {
                            $sections[] = [
                                'header' => $currentHeader,
                                'items' => $currentItems,
                            ];
                            $currentItems = [];
                        }

                        if (!$this->isVisible($item)) {
                            $currentHeader = null;
                            continue;
                        }

                        $currentHeader = $item['header'];
                        continue;
                    }

                    if (!$this->isVisible($item)) {
                        continue;
                    }

                    if (isset($item['submenu']) && is_array($item['submenu'])) {
                        $item['submenu'] = $this->visibleSubmenu($item['submenu']);
                        if ($item['submenu'] === []) {
                            continue;
                        }
                    }

                    $currentItems[] = $item;
                }

                if ($currentItems !== []) {
                    $sections[] = [
                        'header' => $currentHeader,
                        'items' => $currentItems,
                    ];
                }
            @endphp

            @foreach ($sections as $section)
                @if (!empty($section['header']))
                    <p x-show="menuOpen" x-transition class="my-3">{{ $section['header'] }}</p>
                @endif

                @foreach ($section['items'] as $menuItem)
                    @if (!isset($menuItem['submenu']))
                        @php
                            $routeParams = $menuItem['params'] ?? [];
                            $routeUrl = route($menuItem['route'], $routeParams);
                            if (!empty($menuItem['query']) && is_array($menuItem['query'])) {
                                $routeUrl .= '?' . http_build_query($menuItem['query']);
                            }
                            $isActive = Route::currentRouteName() === $menuItem['route'];
                        @endphp

                        <a class="flex items-center gap-2 p-3 px-4 my-2 rounded"
                            href="{{ $routeUrl }}"
                            :class="{{ $isActive ? '1' : '0' }} ? 'bg-blue-600 hover:bg-blue-400 hover:bg-opacity-100' : 'hover:bg-white hover:bg-opacity-5'"
                            aria-label="{{ $menuItem['text'] }}" wire:navigate>
                            <i class="{{ $menuItem['icon'] ?? 'fa fa-circle' }}" aria-hidden="true" x-transition></i>
                            <p x-show="menuOpen">{{ $menuItem['text'] }}</p>
                        </a>
                    @else
                        @php
                            $submenuRoutes = collect($menuItem['submenu'])
                                ->pluck('route')
                                ->filter()
                                ->values()
                                ->all();
                            $isActive = in_array(Route::currentRouteName(), $submenuRoutes, true);
                        @endphp

                        <div @click.outside="submenu = false" x-data="{ submenu: {{ $isActive ? 'true' : 'false' }} }">
                            <div class="flex items-center justify-between gap-2 p-3 my-2 px-4 rounded cursor-pointer"
                                @click="submenu = !submenu"
                                :class="{{ $isActive ? '1' : '0' }} ? 'bg-blue-600 hover:bg-blue-400 hover:bg-opacity-100' : 'hover:bg-white hover:bg-opacity-5'">
                                <div class="flex items-center gap-2">
                                    <i class="{{ $menuItem['icon'] ?? 'fa fa-circle' }}" aria-hidden="true" x-transition></i>
                                    <p x-show="menuOpen" class="cursor-default">{{ $menuItem['text'] }}</p>
                                </div>
                                <i class="transition-all"
                                    :class="{'fas fa-angle-left': submenu == false, 'fas fa-angle-down': submenu == true}"
                                    x-show="menuOpen"></i>
                            </div>

                            @foreach ($menuItem['submenu'] as $submenuItem)
                                @php
                                    $submenuParams = $submenuItem['params'] ?? [];
                                    $submenuUrl = route($submenuItem['route'], $submenuParams);
                                    if (!empty($submenuItem['query']) && is_array($submenuItem['query'])) {
                                        $submenuUrl .= '?' . http_build_query($submenuItem['query']);
                                    }
                                    $submenuIsActive = Route::currentRouteName() === $submenuItem['route'];
                                @endphp

                                <a class="flex items-center gap-2 p-3 px-4 my-2 transition-all rounded whitespace-nowrap {{ $submenuIsActive ? 'bg-white text-black' : 'hover:bg-white hover:bg-opacity-5' }}"
                                    :class="{'h-0 my-auto overflow-hidden py-0 opacity-0': submenu == false}" x-transition
                                    href="{{ $submenuUrl }}" aria-label="{{ $submenuItem['text'] }}" wire:navigate>
                                    <i class="{{ $submenuItem['icon'] ?? 'far fa-fw fa-circle' }}" aria-hidden="true"></i>
                                    <p x-show="menuOpen">{{ $submenuItem['text'] }}</p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            @endforeach
        </div>
    </aside>

    <div class="lg:hidden w-2/6 md:w-4/6 bg-gray-600 opacity-30" @click="menuOpen = false" x-show="menuOpen"
        x-transition:enter="transition-all ease-in duration-200 delay-250" x-transition:enter-start="opacity-0">
    </div>
</nav>
