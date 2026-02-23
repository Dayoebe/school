@extends('layouts.app', ['mode' => 'public'])

@section('title', 'About Us')

@php
    $settings = $publicSiteSettings ?? [];
    $aboutSettings = data_get($settings, 'about_page', []);
    $contactAddress = data_get($settings, 'contact.address', '');
    $contactPhonePrimary = data_get($settings, 'contact.phone_primary', '');
    $contactEmail = data_get($settings, 'contact.email', '');
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
                            We combine structured teaching, co-curricular exposure, and close mentoring to help students discover their
                            strengths and pursue clear goals.
                        </p>
                        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="text-sm font-semibold text-amber-800">
                                <i class="fas fa-quote-left mr-2"></i>
                                Our focus is not just high scores, but complete growth: mind, character, and leadership.
                            </p>
                        </div>
                    </div>

                    <div class="animate__animated animate__fadeInRight rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-indigo-700">Journey</p>
                        <h3 class="mt-2 text-xl font-black text-slate-900">Milestones</h3>
                        <div class="mt-5 space-y-4">
                            <template x-for="item in milestones" :key="item.title">
                                <div class="flex gap-3">
                                    <div class="mt-1 h-3 w-3 rounded-full" :class="item.dotClass"></div>
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
                        <div class="rounded-xl border p-4" :class="value.cardClass">
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm" :class="value.badgeClass">
                                    <i class="fas" :class="value.icon"></i>
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
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">How we build student success</h2>
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
                            <h3 class="text-xl font-black text-slate-900" x-text="pillars[activePillar].title"></h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600 sm:text-base" x-text="pillars[activePillar].description"></p>
                            <div class="mt-4 space-y-2">
                                <template x-for="point in pillars[activePillar].points" :key="point">
                                    <p class="flex items-start gap-2 text-sm text-slate-700">
                                        <i class="fas fa-check-circle mt-0.5 text-lime-600"></i>
                                        <span x-text="point"></span>
                                    </p>
                                </template>
                            </div>
                        </div>
                        <div class="lg:col-span-2">
                            <div class="rounded-xl border p-4" :class="pillars[activePillar].boxClass">
                                <p class="text-xs font-bold uppercase tracking-wider" :class="pillars[activePillar].labelClass">Outcomes</p>
                                <ul class="mt-3 space-y-2 text-sm">
                                    <template x-for="result in pillars[activePillar].outcomes" :key="result">
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
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Teams that drive quality education</h2>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <template x-for="member in leadership" :key="member.role">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-white" :class="member.iconClass">
                                <i class="fas" :class="member.icon"></i>
                            </div>
                            <p class="mt-3 text-sm font-black text-slate-900" x-text="member.role"></p>
                            <p class="mt-1 text-xs font-semibold" :class="member.roleClass" x-text="member.unit"></p>
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
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Our impact at a glance</h2>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <template x-for="stat in stats" :key="stat.label">
                        <div class="rounded-2xl border p-4 text-center sm:p-5" :class="stat.cardClass">
                            <p class="text-2xl font-black sm:text-3xl" :class="stat.valueClass">
                                <span x-text="stat.current"></span><span x-text="stat.suffix"></span>
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
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-700">Frequently Asked Questions</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">What parents and students ask</h2>
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
                activePillar: 'academics',
                activeFaq: null,
                milestones: [
                    {
                        title: 'Foundation Stage',
                        note: 'Built with a mission to deliver strong academics and character development.',
                        dotClass: 'bg-red-500'
                    },
                    {
                        title: 'Growth Stage',
                        note: 'Expanded student support systems, clubs, and practical learning structure.',
                        dotClass: 'bg-orange-500'
                    },
                    {
                        title: 'Digital Learning Stage',
                        note: 'Integrated technology-driven learning and data-backed performance tracking.',
                        dotClass: 'bg-blue-500'
                    },
                    {
                        title: 'Future Forward',
                        note: 'Strengthening innovation, leadership culture, and global readiness.',
                        dotClass: 'bg-violet-500'
                    }
                ],
                values: [
                    {
                        title: 'Excellence',
                        text: 'We pursue high standards in teaching, learning, and outcomes.',
                        icon: 'fa-medal',
                        cardClass: 'border-red-200 bg-red-50 text-red-900',
                        badgeClass: 'bg-red-600 text-white'
                    },
                    {
                        title: 'Integrity',
                        text: 'We build honesty, accountability, and trust in daily school life.',
                        icon: 'fa-shield-halved',
                        cardClass: 'border-orange-200 bg-orange-50 text-orange-900',
                        badgeClass: 'bg-orange-600 text-white'
                    },
                    {
                        title: 'Leadership',
                        text: 'Students are encouraged to take initiative and lead with confidence.',
                        icon: 'fa-flag',
                        cardClass: 'border-amber-200 bg-amber-50 text-amber-900',
                        badgeClass: 'bg-amber-600 text-white'
                    },
                    {
                        title: 'Innovation',
                        text: 'Creative thinking and practical problem-solving are part of learning.',
                        icon: 'fa-lightbulb',
                        cardClass: 'border-cyan-200 bg-cyan-50 text-cyan-900',
                        badgeClass: 'bg-cyan-600 text-white'
                    },
                    {
                        title: 'Discipline',
                        text: 'Structure and consistency shape student habits and responsibility.',
                        icon: 'fa-list-check',
                        cardClass: 'border-indigo-200 bg-indigo-50 text-indigo-900',
                        badgeClass: 'bg-indigo-600 text-white'
                    },
                    {
                        title: 'Service',
                        text: 'We inspire students to contribute positively to the community.',
                        icon: 'fa-hand-holding-heart',
                        cardClass: 'border-rose-200 bg-rose-50 text-rose-900',
                        badgeClass: 'bg-rose-600 text-white'
                    }
                ],
                pillarTabs: [
                    { key: 'academics', label: 'Academic Rigor' },
                    { key: 'mentorship', label: 'Mentorship' },
                    { key: 'coCurricular', label: 'Co-Curricular' },
                    { key: 'technology', label: 'Technology' }
                ],
                pillars: {
                    academics: {
                        title: 'Academic Rigor and Structured Progress',
                        description: 'A clear curriculum plan with continuous assessments and targeted support for steady improvement.',
                        points: [
                            'Focused teaching plans by level and subject',
                            'Regular performance reviews and interventions',
                            'Exam readiness culture with guided preparation'
                        ],
                        outcomes: ['Stronger foundation', 'Higher consistency', 'Clear progress tracking'],
                        boxClass: 'border-blue-200 bg-blue-50 text-blue-900',
                        labelClass: 'text-blue-700'
                    },
                    mentorship: {
                        title: 'Mentorship and Character Formation',
                        description: 'Every student receives guidance on academics, discipline, values, and personal growth.',
                        points: [
                            'Class-based mentoring and counseling support',
                            'Leadership coaching and confidence-building',
                            'Positive behavior and responsibility culture'
                        ],
                        outcomes: ['Better focus', 'Strong values', 'Confidence growth'],
                        boxClass: 'border-emerald-200 bg-emerald-50 text-emerald-900',
                        labelClass: 'text-emerald-700'
                    },
                    coCurricular: {
                        title: 'Co-Curricular Development',
                        description: 'Balanced education through sports, clubs, competitions, and creative platforms.',
                        points: [
                            'Clubs for debate, science, arts, and writing',
                            'Sports programs and team participation',
                            'Public speaking and collaboration activities'
                        ],
                        outcomes: ['Teamwork', 'Creativity', 'Leadership exposure'],
                        boxClass: 'border-purple-200 bg-purple-50 text-purple-900',
                        labelClass: 'text-purple-700'
                    },
                    technology: {
                        title: 'Technology-Enabled Learning',
                        description: 'Digital tools improve teaching quality, tracking, communication, and learning access.',
                        points: [
                            'Technology-supported classroom delivery',
                            'Data-informed student performance review',
                            'Digital communication with parents and guardians'
                        ],
                        outcomes: ['Faster feedback', 'Better visibility', 'Modern readiness'],
                        boxClass: 'border-cyan-200 bg-cyan-50 text-cyan-900',
                        labelClass: 'text-cyan-700'
                    }
                },
                leadership: [
                    {
                        role: 'Principal',
                        unit: 'School Leadership Office',
                        note: 'Provides direction, quality assurance, and strategic school development.',
                        icon: 'fa-user-tie',
                        iconClass: 'bg-red-600',
                        roleClass: 'text-red-700'
                    },
                    {
                        role: 'Academic Team',
                        unit: 'Teaching & Curriculum',
                        note: 'Drives curriculum delivery, lesson quality, and exam readiness.',
                        icon: 'fa-book-open-reader',
                        iconClass: 'bg-blue-600',
                        roleClass: 'text-blue-700'
                    },
                    {
                        role: 'Student Affairs',
                        unit: 'Welfare & Guidance',
                        note: 'Supports behavior, counseling, and student wellbeing.',
                        icon: 'fa-user-group',
                        iconClass: 'bg-teal-600',
                        roleClass: 'text-teal-700'
                    },
                    {
                        role: 'Operations Team',
                        unit: 'School Administration',
                        note: 'Ensures smooth campus operations and learning support systems.',
                        icon: 'fa-gear',
                        iconClass: 'bg-violet-600',
                        roleClass: 'text-violet-700'
                    }
                ],
                stats: [
                    { label: 'Students Mentored', target: 1200, current: 0, suffix: '+', cardClass: 'border-red-200 bg-red-50', valueClass: 'text-red-700' },
                    { label: 'Certified Teachers', target: 85, current: 0, suffix: '+', cardClass: 'border-orange-200 bg-orange-50', valueClass: 'text-orange-700' },
                    { label: 'Learning Programs', target: 30, current: 0, suffix: '+', cardClass: 'border-blue-200 bg-blue-50', valueClass: 'text-blue-700' },
                    { label: 'Success Rate', target: 98, current: 0, suffix: '%', cardClass: 'border-violet-200 bg-violet-50', valueClass: 'text-violet-700' }
                ],
                faqs: [
                    {
                        q: 'What makes Elites International College different?',
                        a: 'We combine strong academics, disciplined culture, mentoring support, and practical student development.'
                    },
                    {
                        q: 'How does the school support student growth?',
                        a: 'Through structured teaching, regular assessments, counseling support, and co-curricular opportunities.'
                    },
                    {
                        q: 'Can parents stay informed about student progress?',
                        a: 'Yes. The school provides regular communication and progress visibility through reports and portal tools.'
                    },
                    {
                        q: 'How can I learn more about admissions?',
                        a: 'Visit the Admission page or Contact page to speak with the admissions team.'
                    }
                ],
                init() {
                    this.animateStats();
                },
                animateStats() {
                    this.stats.forEach((stat) => {
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
