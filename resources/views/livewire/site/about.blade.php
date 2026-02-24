@extends('layouts.app', ['mode' => 'public'])

@section('title', 'About Us')

@php
    $settings = $publicSiteSettings ?? [];
    $aboutSettings = data_get($settings, 'about_page', []);
    $contactAddress = data_get($settings, 'contact.address', '');
    $contactPhonePrimary = data_get($settings, 'contact.phone_primary', '');
    $contactEmail = data_get($settings, 'contact.email', '');

    $aboutMilestones = data_get($aboutSettings, 'milestones', []);
    $aboutValues = data_get($aboutSettings, 'values', []);
    $aboutPillarTabs = data_get($aboutSettings, 'pillar_tabs', []);
    $aboutPillars = data_get($aboutSettings, 'pillars', []);
    $aboutLeadership = data_get($aboutSettings, 'leadership', []);
    $aboutStats = data_get($aboutSettings, 'stats', []);
    $aboutFaqs = data_get($aboutSettings, 'faqs', []);
    $aboutUpdates = data_get($aboutSettings, 'updates', []);
    $aboutCalendar = data_get($aboutSettings, 'calendar', []);
    $aboutStudentVoice = data_get($aboutSettings, 'student_voice', []);

    foreach (['aboutMilestones', 'aboutValues', 'aboutPillarTabs', 'aboutPillars', 'aboutLeadership', 'aboutStats', 'aboutFaqs', 'aboutUpdates', 'aboutCalendar', 'aboutStudentVoice'] as $varName) {
        if (!is_array($$varName)) {
            $$varName = [];
        }
    }

    if ($aboutPillarTabs === [] && $aboutPillars !== []) {
        foreach ($aboutPillars as $key => $pillar) {
            if (!is_array($pillar)) {
                continue;
            }
            $aboutPillarTabs[] = [
                'key' => (string) $key,
                'label' => (string) data_get($pillar, 'title', ucfirst((string) $key)),
            ];
        }
    }

    $defaultPillarKey = null;
    if (!empty($aboutPillarTabs[0]['key'])) {
        $defaultPillarKey = (string) $aboutPillarTabs[0]['key'];
    } elseif ($aboutPillars !== []) {
        $firstPillarKey = array_key_first($aboutPillars);
        if ($firstPillarKey !== null) {
            $defaultPillarKey = (string) $firstPillarKey;
        }
    }
@endphp

@section('content')
    <div x-data="aboutPage()" x-init="init()" class="bg-slate-50 text-slate-900">
        <section id="top" class="relative overflow-hidden bg-slate-900 py-14 sm:py-16">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -left-20 -top-20 h-56 w-56 rounded-full bg-red-500/20 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInDown inline-flex items-center gap-2 rounded-full border border-orange-200/40 bg-orange-500/10 px-3 py-1 text-xs font-semibold text-orange-200">
                    <i class="fas fa-circle-info"></i>
                    <span>{{ data_get($aboutSettings, 'hero_badge') }}</span>
                </div>

                <h1 class="animate__animated animate__fadeInUp mt-4 text-3xl font-black leading-tight text-white sm:text-4xl lg:text-5xl">
                    {{ data_get($aboutSettings, 'hero_title') }}
                    <span class="mt-1 block text-red-300">{{ data_get($aboutSettings, 'hero_highlight') }}</span>
                </h1>

                <p class="animate__animated animate__fadeInUp animate__delay-1s mt-4 max-w-3xl text-sm leading-relaxed text-slate-200 sm:text-base">
                    {{ data_get($aboutSettings, 'hero_description') }}
                </p>

                <div class="mt-6 flex flex-wrap gap-2 text-xs font-bold">
                    <a href="#story" class="rounded-full bg-red-100 px-4 py-2 text-red-700 transition hover:bg-red-200">Our Story</a>
                    <a href="#identity" class="rounded-full bg-orange-100 px-4 py-2 text-orange-700 transition hover:bg-orange-200">Identity</a>
                    <a href="#pillars" class="rounded-full bg-blue-100 px-4 py-2 text-blue-700 transition hover:bg-blue-200">Academic Pillars</a>
                    <a href="#leadership" class="rounded-full bg-violet-100 px-4 py-2 text-violet-700 transition hover:bg-violet-200">Leadership</a>
                    <a href="#faq" class="rounded-full bg-teal-100 px-4 py-2 text-teal-700 transition hover:bg-teal-200">FAQ</a>
                    <a href="#updates" class="rounded-full bg-amber-100 px-4 py-2 text-amber-700 transition hover:bg-amber-200">Updates</a>
                    <a href="#voices" class="rounded-full bg-fuchsia-100 px-4 py-2 text-fuchsia-700 transition hover:bg-fuchsia-200">Student Voice</a>
                </div>
            </div>
        </section>

        <section id="story" class="py-14">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="animate__animated animate__fadeInLeft rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-red-700">Our Story</p>
                        <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">{{ data_get($aboutSettings, 'story_title') }}</h2>
                        <p class="mt-4 text-sm leading-relaxed text-slate-600 sm:text-base">
                            {{ data_get($aboutSettings, 'story_description') }}
                        </p>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600 sm:text-base">
                            {{ data_get($aboutSettings, 'story_extra') }}
                        </p>
                        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="text-sm font-semibold text-amber-800">
                                <i class="fas fa-quote-left mr-2"></i>
                                {{ data_get($aboutSettings, 'story_quote') }}
                            </p>
                        </div>
                    </div>

                    <div class="animate__animated animate__fadeInRight rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-indigo-700">Journey</p>
                        <h3 class="mt-2 text-xl font-black text-slate-900">Milestones</h3>
                        <div class="mt-5 space-y-4">
                            <template x-for="item in milestones" :key="item.title">
                                <div class="flex gap-3">
                                    <div class="mt-1 h-3 w-3 rounded-full" :class="item.dotClass || 'bg-blue-500'"></div>
                                    <div class="flex-1 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <p class="text-sm font-bold text-slate-900" x-text="item.title"></p>
                                        <p class="mt-1 text-xs leading-relaxed text-slate-600" x-text="item.note"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="identity" class="bg-white py-14">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInUp mb-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-orange-700">Identity</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">{{ data_get($aboutSettings, 'identity_title') }}</h2>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
                        <p class="text-sm font-black uppercase tracking-wide text-red-700">Mission</p>
                        <p class="mt-3 text-sm leading-relaxed text-red-900/90">
                            {{ data_get($settings, 'mission') }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                        <p class="text-sm font-black uppercase tracking-wide text-blue-700">Vision</p>
                        <p class="mt-3 text-sm leading-relaxed text-blue-900/90">
                            {{ data_get($settings, 'vision') }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5 md:col-span-2 xl:col-span-1">
                        <p class="text-sm font-black uppercase tracking-wide text-violet-700">School Promise</p>
                        <p class="mt-3 text-sm leading-relaxed text-violet-900/90">
                            {{ data_get($settings, 'school_promise') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="value in values" :key="value.title">
                        <div class="rounded-xl border p-4" :class="value.cardClass || 'border-slate-200 bg-slate-50 text-slate-900'">
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm" :class="value.badgeClass || 'bg-slate-700 text-white'">
                                    <i class="fas" :class="value.icon || 'fa-star'"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-black" x-text="value.title"></p>
                                    <p class="mt-1 text-xs leading-relaxed" x-text="value.text"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <section id="pillars" class="py-14">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInUp mb-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Academic Pillars</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">{{ data_get($aboutSettings, 'pillars_title') }}</h2>
                </div>

                <div class="mb-4 flex flex-wrap gap-2">
                    <template x-for="tab in pillarTabs" :key="tab.key">
                        <button @click="activePillar = tab.key" class="rounded-xl px-4 py-2 text-sm font-bold transition"
                            :class="activePillar === tab.key ? 'bg-blue-600 text-white' : 'bg-stone-200 text-stone-700 hover:bg-stone-300'">
                            <span x-text="tab.label"></span>
                        </button>
                    </template>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
                        <div class="lg:col-span-3">
                            <h3 class="text-xl font-black text-slate-900" x-text="currentPillar().title"></h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600 sm:text-base" x-text="currentPillar().description"></p>
                            <div class="mt-4 space-y-2">
                                <template x-for="point in currentPillar().points" :key="point">
                                    <p class="flex items-start gap-2 text-sm text-slate-700">
                                        <i class="fas fa-check-circle mt-0.5 text-lime-600"></i>
                                        <span x-text="point"></span>
                                    </p>
                                </template>
                            </div>
                        </div>
                        <div class="lg:col-span-2">
                            <div class="rounded-xl border p-4" :class="currentPillar().boxClass">
                                <p class="text-xs font-bold uppercase tracking-wider" :class="currentPillar().labelClass">Outcomes</p>
                                <ul class="mt-3 space-y-2 text-sm">
                                    <template x-for="result in currentPillar().outcomes" :key="result">
                                        <li class="rounded-lg border border-white/40 px-3 py-2" x-text="result"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="leadership" class="bg-white py-14">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInUp mb-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-violet-700">Leadership</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">{{ data_get($aboutSettings, 'leadership_title') }}</h2>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <template x-for="member in leadership" :key="member.role">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-white" :class="member.iconClass || 'bg-blue-600'">
                                <i class="fas" :class="member.icon || 'fa-user'"></i>
                            </div>
                            <p class="mt-3 text-sm font-black text-slate-900" x-text="member.role"></p>
                            <p class="mt-1 text-xs font-semibold" :class="member.roleClass || 'text-slate-700'" x-text="member.unit"></p>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600" x-text="member.note"></p>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <section id="achievements" class="py-14">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInUp mb-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-teal-700">Snapshot</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">{{ data_get($aboutSettings, 'stats_title') }}</h2>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <template x-for="stat in stats" :key="stat.label">
                        <div class="rounded-2xl border p-4 text-center sm:p-5" :class="stat.cardClass || 'border-slate-200 bg-slate-50'">
                            <p class="text-2xl font-black sm:text-3xl" :class="stat.valueClass || 'text-slate-900'">
                                <span x-text="stat.current"></span><span x-text="stat.suffix || ''"></span>
                            </p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-600" x-text="stat.label"></p>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <section id="faq" class="bg-white py-14">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInUp mb-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-700">{{ data_get($aboutSettings, 'faq_subtitle') }}</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">{{ data_get($aboutSettings, 'faq_title') }}</h2>
                </div>

                <div class="space-y-3">
                    <template x-for="(faq, index) in faqs" :key="index">
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                            <button @click="activeFaq = activeFaq === index ? null : index"
                                class="flex w-full items-center justify-between px-4 py-4 text-left text-sm font-bold text-slate-900 sm:text-base">
                                <span x-text="faq.q"></span>
                                <i class="fas transition" :class="activeFaq === index ? 'fa-minus text-indigo-600' : 'fa-plus text-zinc-500'"></i>
                            </button>
                            <div x-show="activeFaq === index" x-transition class="border-t border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                                <p x-text="faq.a"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <section id="updates" class="py-14">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInUp mb-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-700">{{ data_get($aboutSettings, 'updates_subtitle') }}</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">{{ data_get($aboutSettings, 'updates_title') }}</h2>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                        <h3 class="text-sm font-black uppercase tracking-wide text-amber-700">Upcoming Events</h3>
                        <div class="mt-4 space-y-3">
                            <template x-for="event in updates" :key="event.title + event.date">
                                <div class="rounded-xl border border-amber-200/80 bg-white p-3">
                                    <p class="text-sm font-bold text-slate-900" x-text="event.title"></p>
                                    <p class="mt-1 text-xs font-semibold text-amber-700" x-text="event.date"></p>
                                    <p class="mt-1 text-xs leading-relaxed text-slate-600" x-text="event.note"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-5">
                        <h3 class="text-sm font-black uppercase tracking-wide text-cyan-700">School Calendar</h3>
                        <div class="mt-4 space-y-3">
                            <template x-for="entry in calendarEntries" :key="entry.period + entry.activity">
                                <div class="rounded-xl border border-cyan-200/80 bg-white p-3">
                                    <p class="text-xs font-bold uppercase tracking-wide text-cyan-700" x-text="entry.period"></p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900" x-text="entry.activity"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="voices" class="bg-white py-14">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInUp mb-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-fuchsia-700">{{ data_get($aboutSettings, 'student_voice_subtitle') }}</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">{{ data_get($aboutSettings, 'student_voice_title') }}</h2>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <template x-for="voice in studentVoices" :key="voice.name + voice.role">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <i class="fas fa-quote-left text-fuchsia-500"></i>
                            <p class="mt-3 text-sm leading-relaxed text-slate-700" x-text="voice.quote"></p>
                            <div class="mt-4 border-t border-slate-200 pt-3">
                                <p class="text-sm font-black text-slate-900" x-text="voice.name"></p>
                                <p class="text-xs font-semibold text-fuchsia-700" x-text="voice.role"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <section id="visit" class="bg-slate-900 py-14 text-white">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-rose-300">Visit Us</p>
                        <h2 class="mt-2 text-2xl font-black sm:text-3xl">{{ data_get($aboutSettings, 'visit_title') }}</h2>
                        <p class="mt-3 max-w-2xl text-sm text-slate-300 sm:text-base">
                            {{ data_get($aboutSettings, 'visit_description') }}
                        </p>
                        <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('admission') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white hover:bg-red-700">
                                <i class="fas fa-file-signature"></i>
                                <span>Admission Information</span>
                            </a>
                            <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-violet-200/40 bg-violet-500/10 px-5 py-3 text-sm font-bold text-violet-100 hover:bg-violet-500/20">
                                <i class="fas fa-phone-alt"></i>
                                <span>Talk to Us</span>
                            </a>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                        <p class="text-sm font-bold text-pink-200">Campus Contact</p>
                        <p class="mt-3 text-sm text-slate-200"><i class="fas fa-location-dot mr-2"></i>{{ $contactAddress }}</p>
                        <p class="mt-2 text-sm text-slate-200"><i class="fas fa-phone mr-2"></i>{{ $contactPhonePrimary }}</p>
                        <p class="mt-2 text-sm text-slate-200"><i class="fas fa-envelope mr-2"></i>{{ $contactEmail }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        function aboutPage() {
            return {
                activePillar: @js($defaultPillarKey),
                activeFaq: null,
                milestones: @js($aboutMilestones),
                values: @js($aboutValues),
                pillarTabs: @js($aboutPillarTabs),
                pillars: @js($aboutPillars),
                leadership: @js($aboutLeadership),
                stats: @js($aboutStats),
                faqs: @js($aboutFaqs),
                updates: @js($aboutUpdates),
                calendarEntries: @js($aboutCalendar),
                studentVoices: @js($aboutStudentVoice),
                init() {
                    this.normalizeCollections();
                    this.ensureActivePillar();
                    this.animateStats();
                },
                normalizeCollections() {
                    if (!Array.isArray(this.milestones)) this.milestones = [];
                    if (!Array.isArray(this.values)) this.values = [];
                    if (!Array.isArray(this.pillarTabs)) this.pillarTabs = [];
                    if (typeof this.pillars !== 'object' || this.pillars === null || Array.isArray(this.pillars)) this.pillars = {};
                    if (!Array.isArray(this.leadership)) this.leadership = [];
                    if (!Array.isArray(this.faqs)) this.faqs = [];
                    if (!Array.isArray(this.updates)) this.updates = [];
                    if (!Array.isArray(this.calendarEntries)) this.calendarEntries = [];
                    if (!Array.isArray(this.studentVoices)) this.studentVoices = [];
                    if (!Array.isArray(this.stats)) {
                        this.stats = [];
                    }

                    this.stats = this.stats.map((stat) => {
                        const target = Number(stat.target ?? 0);
                        return {
                            ...stat,
                            target: Number.isFinite(target) ? target : 0,
                            current: 0,
                            suffix: stat.suffix ?? '',
                        };
                    });
                },
                ensureActivePillar() {
                    const hasActive = this.activePillar && this.pillars[this.activePillar];
                    if (hasActive) {
                        return;
                    }

                    if (this.pillarTabs.length > 0 && this.pillarTabs[0].key && this.pillars[this.pillarTabs[0].key]) {
                        this.activePillar = this.pillarTabs[0].key;
                        return;
                    }

                    const keys = Object.keys(this.pillars);
                    this.activePillar = keys.length > 0 ? keys[0] : null;
                },
                currentPillar() {
                    if (!this.activePillar || !this.pillars[this.activePillar]) {
                        this.ensureActivePillar();
                    }

                    return this.pillars[this.activePillar] || {
                        title: 'Pillar content is not configured yet.',
                        description: '',
                        points: [],
                        outcomes: [],
                        boxClass: 'border-slate-200 bg-slate-50 text-slate-900',
                        labelClass: 'text-slate-700',
                    };
                },
                animateStats() {
                    this.stats.forEach((stat) => {
                        if (!stat.target || stat.target <= 0) {
                            stat.current = 0;
                            return;
                        }

                        const step = Math.max(1, Math.ceil(stat.target / 60));
                        const timer = setInterval(() => {
                            if (stat.current >= stat.target) {
                                stat.current = stat.target;
                                clearInterval(timer);
                            } else {
                                stat.current = Math.min(stat.current + step, stat.target);
                            }
                        }, 24);
                    });
                }
            };
        }
    </script>
@endpush
