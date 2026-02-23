@extends('layouts.app', ['mode' => 'public'])

@section('title', 'Gallery')

@php
    $settings = $publicSiteSettings ?? [];
    $schoolName = (string) data_get($settings, 'school_name', config('app.name', 'School Portal'));
    $galleryPage = data_get($settings, 'gallery_page', []);

    $palette = [
        'red' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'dot' => 'bg-red-500', 'ring' => 'ring-red-200'],
        'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'dot' => 'bg-orange-500', 'ring' => 'ring-orange-200'],
        'amber' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'dot' => 'bg-amber-500', 'ring' => 'ring-amber-200'],
        'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'dot' => 'bg-yellow-500', 'ring' => 'ring-yellow-200'],
        'lime' => ['bg' => 'bg-lime-100', 'text' => 'text-lime-700', 'dot' => 'bg-lime-500', 'ring' => 'ring-lime-200'],
        'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'dot' => 'bg-green-500', 'ring' => 'ring-green-200'],
        'emerald' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500', 'ring' => 'ring-emerald-200'],
        'teal' => ['bg' => 'bg-teal-100', 'text' => 'text-teal-700', 'dot' => 'bg-teal-500', 'ring' => 'ring-teal-200'],
        'cyan' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-700', 'dot' => 'bg-cyan-500', 'ring' => 'ring-cyan-200'],
        'sky' => ['bg' => 'bg-sky-100', 'text' => 'text-sky-700', 'dot' => 'bg-sky-500', 'ring' => 'ring-sky-200'],
        'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'dot' => 'bg-blue-500', 'ring' => 'ring-blue-200'],
        'indigo' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'dot' => 'bg-indigo-500', 'ring' => 'ring-indigo-200'],
        'violet' => ['bg' => 'bg-violet-100', 'text' => 'text-violet-700', 'dot' => 'bg-violet-500', 'ring' => 'ring-violet-200'],
        'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'dot' => 'bg-purple-500', 'ring' => 'ring-purple-200'],
        'fuchsia' => ['bg' => 'bg-fuchsia-100', 'text' => 'text-fuchsia-700', 'dot' => 'bg-fuchsia-500', 'ring' => 'ring-fuchsia-200'],
        'pink' => ['bg' => 'bg-pink-100', 'text' => 'text-pink-700', 'dot' => 'bg-pink-500', 'ring' => 'ring-pink-200'],
        'rose' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'dot' => 'bg-rose-500', 'ring' => 'ring-rose-200'],
        'slate' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'dot' => 'bg-slate-500', 'ring' => 'ring-slate-200'],
        'gray' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'dot' => 'bg-gray-500', 'ring' => 'ring-gray-200'],
        'zinc' => ['bg' => 'bg-zinc-100', 'text' => 'text-zinc-700', 'dot' => 'bg-zinc-500', 'ring' => 'ring-zinc-200'],
        'neutral' => ['bg' => 'bg-neutral-100', 'text' => 'text-neutral-700', 'dot' => 'bg-neutral-500', 'ring' => 'ring-neutral-200'],
        'stone' => ['bg' => 'bg-stone-100', 'text' => 'text-stone-700', 'dot' => 'bg-stone-500', 'ring' => 'ring-stone-200'],
    ];

    $dbItems = \App\Models\GalleryItem::query()
        ->with([
            'category:id,name,slug,color,is_active',
            'school:id,name',
        ])
        ->where('is_active', true)
        ->whereHas('category', fn ($query) => $query->where('is_active', true))
        ->orderByDesc('is_featured')
        ->orderBy('sort_order')
        ->latest()
        ->limit(120)
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'caption' => $item->caption ?: 'Moments from student life and learning.',
                'url' => \App\Models\GalleryItem::transformUrl($item->media_url, 'c_fill,g_auto,f_auto,q_auto,w_900,h_620'),
                'full_url' => \App\Models\GalleryItem::transformUrl($item->media_url, 'c_limit,f_auto,q_auto,w_1800,h_1200'),
                'category' => $item->category?->name ?? 'General',
                'category_slug' => $item->category?->slug ?? 'general',
                'color' => $item->category?->color ?? 'slate',
                'school' => $item->school?->name,
                'featured' => (bool) $item->is_featured,
                'date' => $item->taken_on?->format('M d, Y') ?: $item->created_at?->format('M d, Y'),
            ];
        })
        ->values();

    $fallbackItems = collect([
        ['id' => 1, 'title' => 'STEM Innovation Fair', 'caption' => 'Students presented robotics and coding projects.', 'url' => 'https://images.pexels.com/photos/1181406/pexels-photo-1181406.jpeg', 'category' => 'STEM', 'category_slug' => 'stem', 'color' => 'indigo', 'school' => $schoolName, 'featured' => true, 'date' => 'May 18, 2026'],
        ['id' => 2, 'title' => 'Inter-House Sports Day', 'caption' => 'Competitive track and field events with full participation.', 'url' => 'https://images.pexels.com/photos/8613089/pexels-photo-8613089.jpeg', 'category' => 'Sports', 'category_slug' => 'sports', 'color' => 'emerald', 'school' => $schoolName, 'featured' => true, 'date' => 'Apr 04, 2026'],
        ['id' => 3, 'title' => 'Creative Arts Showcase', 'caption' => 'Drama, music and spoken-word performances by students.', 'url' => 'https://images.pexels.com/photos/713149/pexels-photo-713149.jpeg', 'category' => 'Events', 'category_slug' => 'events', 'color' => 'purple', 'school' => $schoolName, 'featured' => false, 'date' => 'Jun 02, 2026'],
        ['id' => 4, 'title' => 'Modern Classroom Sessions', 'caption' => 'Interactive teaching in technology-enabled classrooms.', 'url' => 'https://images.pexels.com/photos/3184325/pexels-photo-3184325.jpeg', 'category' => 'Academics', 'category_slug' => 'academics', 'color' => 'blue', 'school' => $schoolName, 'featured' => false, 'date' => 'Mar 12, 2026'],
        ['id' => 5, 'title' => 'Laboratory Practical', 'caption' => 'Hands-on science practicals for real understanding.', 'url' => 'https://images.pexels.com/photos/2280549/pexels-photo-2280549.jpeg', 'category' => 'Academics', 'category_slug' => 'academics', 'color' => 'red', 'school' => $schoolName, 'featured' => false, 'date' => 'Feb 21, 2026'],
        ['id' => 6, 'title' => 'Leadership Club Meeting', 'caption' => 'Students discussing projects and community initiatives.', 'url' => 'https://images.pexels.com/photos/1184572/pexels-photo-1184572.jpeg', 'category' => 'Clubs', 'category_slug' => 'clubs', 'color' => 'teal', 'school' => $schoolName, 'featured' => false, 'date' => 'Jan 25, 2026'],
        ['id' => 7, 'title' => 'Debate Championship', 'caption' => 'Critical thinking and persuasive communication in action.', 'url' => 'https://images.pexels.com/photos/3184292/pexels-photo-3184292.jpeg', 'category' => 'Clubs', 'category_slug' => 'clubs', 'color' => 'orange', 'school' => $schoolName, 'featured' => false, 'date' => 'Nov 19, 2025'],
        ['id' => 8, 'title' => 'Graduation Ceremony', 'caption' => 'Celebrating graduating students and award winners.', 'url' => 'https://images.pexels.com/photos/267885/pexels-photo-267885.jpeg', 'category' => 'Events', 'category_slug' => 'events', 'color' => 'rose', 'school' => $schoolName, 'featured' => true, 'date' => 'Jul 30, 2025'],
    ]);

    $galleryItems = $dbItems->isNotEmpty() ? $dbItems : $fallbackItems;
    $categories = $galleryItems
        ->groupBy('category_slug')
        ->map(function ($group) {
            return [
                'slug' => $group->first()['category_slug'],
                'name' => $group->first()['category'],
                'color' => $group->first()['color'] ?: 'slate',
                'count' => $group->count(),
            ];
        })
        ->values()
        ->sortBy('name')
        ->values();
@endphp

@section('content')
    <div x-data="galleryPage(@js($galleryItems), @js($categories), @js($palette))" x-init="init()"
        class="bg-slate-50 text-slate-900">
        <section id="top" class="bg-slate-900 py-14 text-white sm:py-16">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="inline-flex items-center gap-2 rounded-full border border-red-200/40 bg-red-500/10 px-3 py-1 text-xs font-semibold text-red-200">
                    <i class="fas fa-images"></i>
                    <span>{{ data_get($galleryPage, 'hero_badge') }}</span>
                </div>

                <div class="mt-4 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 class="text-3xl font-black leading-tight sm:text-4xl lg:text-5xl">{{ data_get($galleryPage, 'hero_title') }}</h1>
                        <p class="mt-4 max-w-3xl text-sm leading-relaxed text-slate-200 sm:text-base">
                            {{ data_get($galleryPage, 'hero_description') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:w-[320px]">
                        <div class="rounded-xl border border-white/20 bg-white/10 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-300">Items</p>
                            <p class="mt-1 text-2xl font-black text-white" x-text="items.length"></p>
                        </div>
                        <div class="rounded-xl border border-white/20 bg-white/10 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-300">Categories</p>
                            <p class="mt-1 text-2xl font-black text-white" x-text="categories.length"></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-b border-slate-200 bg-white py-4">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-4">
                    <div class="lg:col-span-3">
                        <div class="flex gap-2 overflow-x-auto pb-1">
                            <button @click="activeCategory = 'all'"
                                class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold transition"
                                :class="activeCategory === 'all' ? 'bg-red-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                                All ( <span x-text="items.length"></span> )
                            </button>

                            <template x-for="category in categories" :key="category.slug">
                                <button @click="activeCategory = category.slug"
                                    class="inline-flex items-center gap-2 whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold transition"
                                    :class="activeCategory === category.slug ? badgeClass(category.color) + ' ring-2 ' + ringClass(category.color) : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                                    <span class="h-1.5 w-1.5 rounded-full" :class="dotClass(category.color)"></span>
                                    <span x-text="category.name"></span>
                                    <span class="opacity-70" x-text="'(' + category.count + ')'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <input x-model="search" type="text" placeholder="Search gallery..."
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                    </div>
                </div>
            </div>
        </section>

        <section class="py-10">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <template x-if="filteredItems.length === 0">
                    <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
                        <i class="fas fa-camera text-3xl text-slate-400"></i>
                        <h2 class="mt-3 text-xl font-black text-slate-900">No gallery item found</h2>
                        <p class="mt-2 text-sm text-slate-600">Try another category or search phrase.</p>
                    </div>
                </template>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="item in filteredItems" :key="item.id">
                        <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-lg">
                            <button type="button" @click="openById(item.id)" class="relative block w-full overflow-hidden">
                                <img :src="item.url" :alt="item.title" class="h-56 w-full object-cover transition duration-300 group-hover:scale-105">
                                <div class="absolute inset-x-0 bottom-0 bg-black/60 p-3 text-left">
                                    <div class="mb-1 flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold"
                                            :class="badgeClass(item.color)">
                                            <span class="h-1.5 w-1.5 rounded-full" :class="dotClass(item.color)"></span>
                                            <span x-text="item.category"></span>
                                        </span>
                                        <span x-show="item.featured"
                                            class="rounded-full bg-yellow-100 px-2 py-1 text-[10px] font-bold text-yellow-800">FEATURED</span>
                                    </div>
                                    <h3 class="line-clamp-1 text-sm font-black text-white" x-text="item.title"></h3>
                                </div>
                            </button>

                            <div class="space-y-2 p-4">
                                <p class="line-clamp-2 text-sm text-slate-600" x-text="item.caption"></p>
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span x-text="item.date"></span>
                                    <span x-text="item.school || 'Campus Gallery'"></span>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>
            </div>
        </section>

        <section class="bg-white py-12">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                        <div class="lg:col-span-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-red-700">Want More?</p>
                            <h2 class="mt-2 text-2xl font-black text-slate-900">Follow our school activities and events</h2>
                            <p class="mt-3 text-sm text-slate-600">
                                The dashboard team updates this gallery with new categories and fresh school moments regularly.
                            </p>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center lg:flex-col lg:justify-center lg:items-end">
                            @auth
                                @if (auth()->user()->hasAnyRole(['super-admin', 'super_admin', 'admin', 'principal', 'teacher']))
                                <a href="{{ route('gallery.manage') }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-700">
                                    <i class="fas fa-images"></i>
                                    <span>Manage Gallery</span>
                                </a>
                                @else
                                <a href="{{ route('dashboard') }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-700">
                                    <i class="fas fa-gauge-high"></i>
                                    <span>Go to Dashboard</span>
                                </a>
                                @endif
                            @else
                                <a href="{{ route('admission') }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-700">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Apply for Admission</span>
                                </a>
                            @endauth
                            <a href="{{ route('contact') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                                <i class="fas fa-envelope"></i>
                                <span>Contact School</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div x-show="selectedIndex !== null" x-transition x-cloak
            class="fixed inset-0 z-[70] flex items-center justify-center bg-black/85 p-4"
            @keydown.escape.window="closeModal()" @click.self="closeModal()">
            <div class="relative w-full max-w-5xl overflow-hidden rounded-2xl bg-white">
                <button type="button" @click="closeModal()"
                    class="absolute right-3 top-3 z-20 inline-flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-700 shadow-sm transition hover:bg-slate-100">
                    <i class="fas fa-times"></i>
                </button>

                <template x-if="selectedItem">
                    <div class="grid grid-cols-1 lg:grid-cols-3">
                        <div class="relative lg:col-span-2">
                            <img :src="selectedItem.full_url || selectedItem.url" :alt="selectedItem.title" class="h-[360px] w-full object-cover sm:h-[500px]">
                            <button type="button" @click="prev()"
                                class="absolute left-3 top-1/2 inline-flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-white transition hover:bg-black/80">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" @click="next()"
                                class="absolute right-3 top-1/2 inline-flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-white transition hover:bg-black/80">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>

                        <div class="space-y-4 p-5">
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold"
                                :class="badgeClass(selectedItem.color)">
                                <span class="h-1.5 w-1.5 rounded-full" :class="dotClass(selectedItem.color)"></span>
                                <span x-text="selectedItem.category"></span>
                            </span>
                            <h3 class="text-xl font-black text-slate-900" x-text="selectedItem.title"></h3>
                            <p class="text-sm leading-relaxed text-slate-600" x-text="selectedItem.caption"></p>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
                                <p><strong>Date:</strong> <span x-text="selectedItem.date"></span></p>
                                <p class="mt-1"><strong>School:</strong> <span x-text="selectedItem.school || 'Campus Gallery'"></span></p>
                                <p class="mt-1"><strong>Item:</strong> <span x-text="(selectedIndex + 1) + ' of ' + filteredItems.length"></span></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function galleryPage(items, categories, palette) {
            return {
                items,
                categories,
                palette,
                activeCategory: 'all',
                search: '',
                selectedIndex: null,
                keyHandler: null,

                get filteredItems() {
                    const text = this.search.toLowerCase().trim();
                    return this.items.filter((item) => {
                        const categoryMatch = this.activeCategory === 'all' || item.category_slug === this.activeCategory;
                        if (!categoryMatch) {
                            return false;
                        }

                        if (!text) {
                            return true;
                        }

                        const haystack = `${item.title} ${item.caption} ${item.category} ${item.school || ''}`.toLowerCase();
                        return haystack.includes(text);
                    });
                },

                get selectedItem() {
                    if (this.selectedIndex === null) {
                        return null;
                    }

                    return this.filteredItems[this.selectedIndex] || null;
                },

                badgeClass(color) {
                    const tone = this.palette[color] || this.palette.slate;
                    return `${tone.bg} ${tone.text}`;
                },

                dotClass(color) {
                    const tone = this.palette[color] || this.palette.slate;
                    return tone.dot;
                },

                ringClass(color) {
                    const tone = this.palette[color] || this.palette.slate;
                    return tone.ring;
                },

                openById(id) {
                    const index = this.filteredItems.findIndex((item) => item.id === id);
                    if (index === -1) {
                        return;
                    }

                    this.selectedIndex = index;
                },

                closeModal() {
                    this.selectedIndex = null;
                },

                next() {
                    if (this.filteredItems.length === 0 || this.selectedIndex === null) {
                        return;
                    }

                    this.selectedIndex = (this.selectedIndex + 1) % this.filteredItems.length;
                },

                prev() {
                    if (this.filteredItems.length === 0 || this.selectedIndex === null) {
                        return;
                    }

                    this.selectedIndex = (this.selectedIndex - 1 + this.filteredItems.length) % this.filteredItems.length;
                },

                init() {
                    this.keyHandler = (event) => {
                        if (this.selectedIndex === null) {
                            return;
                        }

                        if (event.key === 'ArrowRight') {
                            this.next();
                        } else if (event.key === 'ArrowLeft') {
                            this.prev();
                        } else if (event.key === 'Escape') {
                            this.closeModal();
                        }
                    };

                    window.addEventListener('keydown', this.keyHandler);
                }
            };
        }
    </script>
@endpush
