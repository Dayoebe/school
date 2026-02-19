<footer class="relative mt-16 overflow-hidden border-t border-slate-200 bg-slate-950 text-slate-100">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-20 -left-10 h-56 w-56 rounded-full bg-red-500/15 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-64 w-64 rounded-full bg-orange-500/15 blur-3xl"></div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-10 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-amber-300">Admissions Open</p>
                    <h2 class="mt-1 text-xl font-black text-white sm:text-2xl">Ready to enroll your child?</h2>
                    <p class="mt-2 text-sm text-slate-300">Start your admission process today or contact us for guidance.</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <a href="{{ route('admission') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                        <i class="fas fa-user-plus text-xs"></i>
                        <span>Apply Now</span>
                    </a>
                    <a href="{{ route('contact') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-orange-300/40 bg-orange-500/15 px-5 py-3 text-sm font-bold text-orange-100 transition hover:bg-orange-500/25">
                        <i class="fas fa-phone text-xs"></i>
                        <span>Contact School</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/logo.png') }}" alt="Elites International College Logo"
                        class="h-11 w-11 rounded-full border border-white/20 object-cover">
                    <div>
                        <p class="text-sm font-black text-white">Elites International College</p>
                        <p class="text-xs text-rose-300">Awka, Anambra</p>
                    </div>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-slate-300">
                    A modern learning community focused on academic excellence, leadership development, and moral values.
                </p>
                <div class="mt-4 flex items-center gap-3 text-base">
                    <a href="#" aria-label="Facebook" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-slate-200 transition hover:bg-white/20 hover:text-white">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" aria-label="Instagram" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-slate-200 transition hover:bg-white/20 hover:text-white">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" aria-label="X" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-slate-200 transition hover:bg-white/20 hover:text-white">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" aria-label="WhatsApp" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-slate-200 transition hover:bg-white/20 hover:text-white">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-orange-300">Quick Links</h3>
                <ul class="mt-4 space-y-2 text-sm text-slate-300">
                    <li><a href="{{ route('home') }}" class="transition hover:text-white">Home</a></li>
                    <li><a href="{{ route('about') }}" class="transition hover:text-white">About Us</a></li>
                    <li><a href="{{ route('admission') }}" class="transition hover:text-white">Admission</a></li>
                    <li><a href="{{ route('gallery') }}" class="transition hover:text-white">Gallery</a></li>
                    <li><a href="{{ route('contact') }}" class="transition hover:text-white">Contact</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-yellow-300">Academics</h3>
                <ul class="mt-4 space-y-2 text-sm text-slate-300">
                    <li>Junior Secondary School</li>
                    <li>Senior Secondary School</li>
                    <li>STEM and Innovation</li>
                    <li>Clubs and Leadership</li>
                    <li>Exam and Result Portal</li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-cyan-300">Contact Info</h3>
                <ul class="mt-4 space-y-3 text-sm text-slate-300">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-location-dot mt-1 text-lime-300"></i>
                        <span>13 Chief Mbanefo E. Uduezue Street, Umubele, Awka, Anambra State</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-phone text-green-300"></i>
                        <a href="tel:+2348066025508" class="transition hover:text-white">+234 806 602 5508</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-phone text-teal-300"></i>
                        <a href="tel:+2348037315741" class="transition hover:text-white">+234 803 731 5741</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-envelope text-sky-300"></i>
                        <a href="mailto:info@elitesinternationalcollege.com" class="transition hover:text-white">info@elitesinternationalcollege.com</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-3 border-t border-white/10 pt-4 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} Elites International College. All rights reserved.</p>
            <a href="#top" class="inline-flex items-center gap-2 font-semibold text-fuchsia-300 transition hover:text-fuchsia-200">
                <span>Back to Top</span>
                <i class="fas fa-arrow-up text-[10px]"></i>
            </a>
        </div>
    </div>
</footer>
