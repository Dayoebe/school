@extends('layouts.app', ['mode' => 'public'])

@section('title', 'Home')

@php
    $settings = $publicSiteSettings ?? [];
    $homeSettings = data_get($settings, 'home', []);
    $contactAddress = data_get($settings, 'contact.address', '');
    $contactPhonePrimary = data_get($settings, 'contact.phone_primary', '');
    $contactEmail = data_get($settings, 'contact.email', '');
@endphp

@section('content')
    <div x-data="homePage()" x-init="init()" class="bg-slate-50 text-slate-900">
        <section id="top" class="relative overflow-hidden bg-slate-900">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -left-24 h-64 w-64 rounded-full bg-red-500/20 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-orange-500/20 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-6xl px-4 pb-10 pt-12 sm:px-6 sm:pt-16 lg:px-8">
                <div class="animate__animated animate__fadeInDown inline-flex items-center gap-2 rounded-full border border-red-200/30 bg-red-500/10 px-3 py-1 text-xs font-semibold text-orange-200">
                    <i class="fas fa-school"></i>
                    <span>{{ data_get($homeSettings, 'hero_badge') }}</span>
                </div>

                <h1 class="animate__animated animate__fadeInUp mt-5 text-3xl font-black leading-tight text-white sm:text-4xl lg:text-5xl">
                    {{ data_get($homeSettings, 'hero_title') }}
                    <span class="block text-red-300">{{ data_get($homeSettings, 'hero_highlight') }}</span>
                </h1>

                <p class="animate__animated animate__fadeInUp animate__delay-1s mt-4 max-w-2xl text-sm leading-relaxed text-slate-200 sm:text-base">
                    {{ data_get($homeSettings, 'hero_description') }}
                </p>

                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:flex lg:flex-wrap lg:items-center">
                    <a href="{{ route('admission') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                        <i class="fas fa-user-plus"></i>
                        <span>Start Admission</span>
                    </a>
                    <a href="{{ route('about') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-orange-200/40 bg-orange-500/10 px-5 py-3 text-sm font-bold text-orange-100 transition hover:bg-orange-500/20">
                        <i class="fas fa-compass"></i>
                        <span>Explore School Life</span>
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-sky-200/40 bg-sky-500/10 px-5 py-3 text-sm font-bold text-sky-100 transition hover:bg-sky-500/20">
                            <i class="fas fa-chart-line"></i>
                            <span>Go to Dashboard</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-indigo-200/40 bg-indigo-500/10 px-5 py-3 text-sm font-bold text-indigo-100 transition hover:bg-indigo-500/20">
                            <i class="fas fa-right-to-bracket"></i>
                            <span>Portal Login</span>
                        </a>
                    @endauth
                </div>

                <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <template x-for="stat in stats" :key="stat.label">
                        <div class="animate__animated animate__fadeInUp rounded-xl border border-white/20 p-3 text-white backdrop-blur-sm" :class="stat.cardClass">
                            <p class="text-xl font-black sm:text-2xl" :class="stat.valueClass">
                                <span x-text="stat.current"></span><span x-text="stat.suffix"></span>
                            </p>
                            <p class="mt-1 text-xs text-slate-200" x-text="stat.label"></p>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <section class="border-b border-slate-200 bg-white/95 py-3">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="flex gap-2 overflow-x-auto pb-1 text-xs font-semibold text-slate-700">
                    <a href="#programs" class="whitespace-nowrap rounded-full bg-red-100 px-4 py-2 text-red-700 transition hover:bg-red-200">Programs</a>
                    <a href="#experience" class="whitespace-nowrap rounded-full bg-orange-100 px-4 py-2 text-orange-700 transition hover:bg-orange-200">Experience</a>
                    <a href="#results" class="whitespace-nowrap rounded-full bg-sky-100 px-4 py-2 text-sky-700 transition hover:bg-sky-200">Academic Results</a>
                    <a href="#events" class="whitespace-nowrap rounded-full bg-purple-100 px-4 py-2 text-purple-700 transition hover:bg-purple-200">Events</a>
                    <a href="#faq" class="whitespace-nowrap rounded-full bg-teal-100 px-4 py-2 text-teal-700 transition hover:bg-teal-200">FAQ</a>
                    <a href="#contact-quick" class="whitespace-nowrap rounded-full bg-rose-100 px-4 py-2 text-rose-700 transition hover:bg-rose-200">Contact</a>
                </div>
            </div>
        </section>

    

        <section id="programs" class="py-14">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInUp mb-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-red-700">Academic Pathways</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Choose the right track for every learner</h2>
                    <p class="mt-3 text-sm text-slate-600 sm:text-base">
                        Structured curriculum from foundational learning to advanced exam preparation.
                    </p>
                </div>

                <div class="mb-4 flex flex-wrap gap-2">
                    <template x-for="tab in programTabs" :key="tab.key">
                        <button @click="activeProgram = tab.key"
                            class="rounded-xl px-4 py-2 text-sm font-bold transition"
                            :class="activeProgram === tab.key ? 'bg-red-600 text-white' : 'bg-stone-200 text-stone-700 hover:bg-stone-300'">
                            <span x-text="tab.label"></span>
                        </button>
                    </template>
                </div>

                <div class="animate__animated animate__fadeIn rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h3 class="text-xl font-black text-slate-900" x-text="programs[activeProgram].title"></h3>
                    <p class="mt-2 text-sm text-slate-600 sm:text-base" x-text="programs[activeProgram].description"></p>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <template x-for="item in programs[activeProgram].highlights" :key="item">
                            <div class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm font-medium text-stone-700">
                                <i class="fas fa-check-circle mr-2 text-lime-600"></i>
                                <span x-text="item"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <section id="experience" class="bg-white py-14">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInUp mb-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-orange-700">School Experience</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Learning beyond the classroom</h2>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="animate__animated animate__fadeInUp rounded-2xl border border-cyan-200 bg-cyan-50 p-5">
                        <i class="fas fa-microscope text-2xl text-cyan-700"></i>
                        <h3 class="mt-3 text-lg font-bold text-slate-900">Modern Labs</h3>
                        <p class="mt-2 text-sm text-slate-600">Hands-on science and ICT lessons to build confidence and skill.</p>
                    </div>
                    <div class="animate__animated animate__fadeInUp rounded-2xl border border-amber-200 bg-amber-50 p-5">
                        <i class="fas fa-basketball-ball text-2xl text-amber-700"></i>
                        <h3 class="mt-3 text-lg font-bold text-slate-900">Sports & Fitness</h3>
                        <p class="mt-2 text-sm text-slate-600">Football, basketball, athletics, and inter-house competitions.</p>
                    </div>
                    <div class="animate__animated animate__fadeInUp rounded-2xl border border-green-200 bg-green-50 p-5">
                        <i class="fas fa-users text-2xl text-green-700"></i>
                        <h3 class="mt-3 text-lg font-bold text-slate-900">Clubs & Leadership</h3>
                        <p class="mt-2 text-sm text-slate-600">Debate, coding, literary, press, and social impact clubs.</p>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-900 p-5 text-white sm:p-6">
                    <p class="text-xs uppercase tracking-wider text-violet-300">Student Voice</p>
                    <div class="mt-3" x-transition>
                        <p class="text-lg font-bold sm:text-xl" x-text="testimonials[testimonialIndex].quote"></p>
                        <p class="mt-2 text-sm text-slate-300" x-text="testimonials[testimonialIndex].name"></p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <template x-for="(item, index) in testimonials" :key="index">
                            <button @click="testimonialIndex = index"
                                class="h-2.5 w-8 rounded-full transition"
                                :class="testimonialIndex === index ? 'bg-violet-400' : 'bg-slate-600'"></button>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <section id="results" class="py-14">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInUp mb-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Performance</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Strong academic outcomes every year</h2>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm">
                        <p class="text-3xl font-black text-teal-700">98%</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Pass Rate</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm">
                        <p class="text-3xl font-black text-cyan-700">75+</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Subject Distinctions</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm">
                        <p class="text-3xl font-black text-sky-700">40+</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Scholarship Entries</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm">
                        <p class="text-3xl font-black text-indigo-700">24/7</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Parent Portal Access</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="events" class="bg-white py-14">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInUp mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-purple-700">Updates</p>
                        <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Upcoming events and school calendar</h2>
                    </div>
                    <a href="{{ route('gallery') }}" class="inline-flex items-center gap-2 text-sm font-bold text-purple-700 hover:text-purple-800">
                        <span>View Gallery</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="space-y-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-fuchsia-700">March 12</p>
                            <h3 class="text-base font-bold text-slate-900">Open Day and Parent Briefing</h3>
                            <p class="text-sm text-slate-600">Classroom tours, staff interaction, and annual goals update.</p>
                        </div>
                        <span class="mt-3 inline-flex rounded-full bg-fuchsia-100 px-3 py-1 text-xs font-bold text-fuchsia-700 sm:mt-0">Campus Event</span>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-pink-700">April 04</p>
                            <h3 class="text-base font-bold text-slate-900">Inter-House Sports Festival</h3>
                            <p class="text-sm text-slate-600">Track events, team games, and student awards.</p>
                        </div>
                        <span class="mt-3 inline-flex rounded-full bg-pink-100 px-3 py-1 text-xs font-bold text-pink-700 sm:mt-0">Sports</span>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-rose-700">May 18</p>
                            <h3 class="text-base font-bold text-slate-900">STEM and Innovation Fair</h3>
                            <p class="text-sm text-slate-600">Student project showcase across science, ICT, and robotics.</p>
                        </div>
                        <span class="mt-3 inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700 sm:mt-0">Innovation</span>
                    </div>
                </div>
            </div>
        </section>

        <section id="faq" class="py-14">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInUp mb-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-teal-700">Need Help?</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Frequently asked questions</h2>
                </div>

                <div class="space-y-3">
                    <template x-for="(faq, index) in faqs" :key="index">
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                            <button @click="activeFaq = activeFaq === index ? null : index"
                                class="flex w-full items-center justify-between px-4 py-4 text-left text-sm font-bold text-slate-900 sm:text-base">
                                <span x-text="faq.q"></span>
                                <i class="fas" :class="activeFaq === index ? 'fa-minus text-teal-600' : 'fa-plus text-zinc-500'"></i>
                            </button>
                            <div x-show="activeFaq === index" x-transition class="border-t border-slate-200 px-4 py-3 text-sm text-slate-600">
                                <p x-text="faq.a"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <section id="contact-quick" class="bg-slate-900 py-14 text-white">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-fuchsia-300">Ready to Join?</p>
                        <h2 class="mt-2 text-2xl font-black sm:text-3xl">{{ data_get($homeSettings, 'contact_title') }}</h2>
                        <p class="mt-3 max-w-2xl text-sm text-slate-300 sm:text-base">
                            {{ data_get($homeSettings, 'contact_description') }}
                        </p>
                        <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('admission') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white hover:bg-red-700">
                                <i class="fas fa-file-signature"></i>
                                <span>Apply for Admission</span>
                            </a>
                            <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-violet-200/40 bg-violet-500/10 px-5 py-3 text-sm font-bold text-violet-100 hover:bg-violet-500/20">
                                <i class="fas fa-phone-alt"></i>
                                <span>Contact School</span>
                            </a>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                        <p class="text-sm font-bold text-pink-200">Quick Contact</p>
                        <p class="mt-3 text-sm text-slate-200"><i class="fas fa-location-dot mr-2"></i>{{ $contactAddress }}</p>
                        <p class="mt-2 text-sm text-slate-200"><i class="fas fa-phone mr-2"></i>{{ $contactPhonePrimary }}</p>
                        <p class="mt-2 text-sm text-slate-200"><i class="fas fa-envelope mr-2"></i>{{ $contactEmail }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="fixed bottom-3 left-1/2 z-40 w-[95%] -translate-x-1/2 rounded-2xl border border-slate-200 bg-white/95 p-2 shadow-xl backdrop-blur md:hidden">
            <div class="grid grid-cols-4 gap-1 text-center text-[11px] font-semibold text-slate-700">
                <a href="#top" class="rounded-lg px-2 py-2 hover:bg-slate-100"><i class="fas fa-house block"></i><span>Home</span></a>
                <a href="#programs" class="rounded-lg px-2 py-2 hover:bg-slate-100"><i class="fas fa-book-open block"></i><span>Programs</span></a>
                <a href="#events" class="rounded-lg px-2 py-2 hover:bg-slate-100"><i class="fas fa-calendar block"></i><span>Events</span></a>
                <a href="#contact-quick" class="rounded-lg px-2 py-2 hover:bg-slate-100"><i class="fas fa-paper-plane block"></i><span>Contact</span></a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function homePage() {
            return {
                activeProgram: 'junior',
                activeFaq: null,
                testimonialIndex: 0,
                testimonialTimer: null,
                stats: [
                    { label: 'Students', target: 1200, current: 0, suffix: '+', cardClass: 'bg-red-500/15', valueClass: 'text-red-200' },
                    { label: 'Certified Teachers', target: 85, current: 0, suffix: '+', cardClass: 'bg-orange-500/15', valueClass: 'text-orange-200' },
                    { label: 'Clubs & Activities', target: 24, current: 0, suffix: '', cardClass: 'bg-amber-500/15', valueClass: 'text-amber-200' },
                    { label: 'Exam Success Rate', target: 98, current: 0, suffix: '%', cardClass: 'bg-yellow-500/15', valueClass: 'text-yellow-200' }
                ],
                colorPalette: [
                    { name: 'red', class: 'bg-red-100 text-red-700' },
                    { name: 'orange', class: 'bg-orange-100 text-orange-700' },
                    { name: 'amber', class: 'bg-amber-100 text-amber-700' },
                    { name: 'yellow', class: 'bg-yellow-100 text-yellow-700' },
                    { name: 'lime', class: 'bg-lime-100 text-lime-700' },
                    { name: 'green', class: 'bg-green-100 text-green-700' },
                    { name: 'emerald', class: 'bg-emerald-100 text-emerald-700' },
                    { name: 'teal', class: 'bg-teal-100 text-teal-700' },
                    { name: 'cyan', class: 'bg-cyan-100 text-cyan-700' },
                    { name: 'sky', class: 'bg-sky-100 text-sky-700' },
                    { name: 'blue', class: 'bg-blue-100 text-blue-700' },
                    { name: 'indigo', class: 'bg-indigo-100 text-indigo-700' },
                    { name: 'violet', class: 'bg-violet-100 text-violet-700' },
                    { name: 'purple', class: 'bg-purple-100 text-purple-700' },
                    { name: 'fuchsia', class: 'bg-fuchsia-100 text-fuchsia-700' },
                    { name: 'pink', class: 'bg-pink-100 text-pink-700' },
                    { name: 'rose', class: 'bg-rose-100 text-rose-700' },
                    { name: 'slate', class: 'bg-slate-100 text-slate-700' },
                    { name: 'gray', class: 'bg-gray-100 text-gray-700' },
                    { name: 'zinc', class: 'bg-zinc-100 text-zinc-700' },
                    { name: 'neutral', class: 'bg-neutral-100 text-neutral-700' },
                    { name: 'stone', class: 'bg-stone-100 text-stone-700' }
                ],
                programTabs: [
                    { key: 'junior', label: 'Junior School' },
                    { key: 'senior', label: 'Senior School' },
                    { key: 'stem', label: 'STEM Path' },
                    { key: 'arts', label: 'Humanities & Arts' }
                ],
                programs: {
                    junior: {
                        title: 'Junior Secondary (JSS 1-3)',
                        description: 'A strong foundation in literacy, numeracy, digital skills, and character formation.',
                        highlights: [
                            'Continuous assessment with mentoring support',
                            'Creative arts and practical science',
                            'Structured reading and communication labs',
                            'Early leadership and team-building programs'
                        ]
                    },
                    senior: {
                        title: 'Senior Secondary (SS 1-3)',
                        description: 'Exam-focused, career-oriented preparation for WAEC, NECO, and university readiness.',
                        highlights: [
                            'Focused subject pathways by career interest',
                            'Targeted revision and mock examination drills',
                            'One-on-one academic advisory system',
                            'Performance analytics for parents and students'
                        ]
                    },
                    stem: {
                        title: 'STEM and Innovation',
                        description: 'A practical environment for coding, robotics, engineering concepts, and applied science.',
                        highlights: [
                            'Weekly coding and problem-solving sessions',
                            'Project-based experiments and showcases',
                            'Mathematics enhancement clinics',
                            'Technology-integrated learning tools'
                        ]
                    },
                    arts: {
                        title: 'Humanities and Creative Arts',
                        description: 'Balanced learning in communication, social sciences, literature, and creative expression.',
                        highlights: [
                            'Debate, writing, and public speaking workshops',
                            'Cultural and civic education programs',
                            'Music, drama, and visual arts opportunities',
                            'Research and presentation skill development'
                        ]
                    }
                },
                testimonials: [
                    { quote: 'The teachers are supportive and the school keeps us focused with clear learning goals.', name: 'Parent, SS2 Student' },
                    { quote: 'I improved my confidence in Math and English because every term has a clear improvement plan.', name: 'Student, JSS3' },
                    { quote: 'The portal makes it easy to track results and stay connected with school progress.', name: 'Guardian, Senior School' }
                ],
                faqs: [
                    { q: 'When is admission open?', a: 'Admission is open throughout the session, with major intakes at the beginning of each term.' },
                    { q: 'Do you provide boarding facilities?', a: 'Yes. We provide safe and supervised boarding with academic and welfare support.' },
                    { q: 'Can parents track student performance online?', a: 'Yes. Parents can view records, assessments, and updates through the school portal.' },
                    { q: 'How do I schedule a school visit?', a: 'Use the Contact or Admission page, and our admissions unit will confirm your visit time.' }
                ],
                init() {
                    this.animateStats();
                    this.startTestimonialRotation();
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
                },
                startTestimonialRotation() {
                    this.testimonialTimer = setInterval(() => {
                        this.testimonialIndex = (this.testimonialIndex + 1) % this.testimonials.length;
                    }, 5000);
                }
            }
        }
    </script>
@endpush
