<footer class="relative bg-gradient-to-br from-blue-950 via-slate-800 to-blue-900 text-white overflow-hidden">
    <!-- Floating Glow Effects -->
    <div class="absolute inset-0 pointer-events-none -z-10">
        <div class="absolute w-96 h-96 bg-white/10 rounded-full blur-3xl top-32 left-10 animate-pulse"></div>
        <div class="absolute w-72 h-72 bg-white/5 rounded-full blur-2xl bottom-10 right-10 animate-bounce"></div>
    </div>

    <!-- Top Curve -->
    <div class="absolute top-0 left-0 w-full h-24 bg-white rounded-b-[50%] -z-10"></div>

    <!-- Main Footer Content -->
    <div
        class="max-w-7xl mx-auto px-6 lg:px-20 pt-28 pb-20 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-10 text-sm animate__animated animate__fadeInUp">

        <!-- Logo + About -->
        <div class="space-y-4 col-span-1">
            <div class="transform hover:scale-110 transition hover:uppercase flex items-center gap-3">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" class="h-12 w-12 rounded-full shadow-lg">
                <h2 class="text-xl font-extrabold leading-tight">Elites International College</h2>
            </div>
            <p class="transform hover:scale-110 transition text-gray-300 leading-relaxed">A citadel of excellence
                dedicated to academic brilliance, moral
                integrity, and global standards.</p>
            <p class="italic text-xs text-gray-400">"Inspiring minds, shaping futures."</p>
        </div>

        <!-- Quick Links -->
        <div>
            <h3
                class="transform hover:scale-125 transition hover:uppercase font-semibold text-base mb-4 border-b border-blue-500 inline-block pb-1">
                Quick Links</h3>
            <ul class="space-y-2 text-gray-300">
                <li><a href="{{ route('home') }}"
                        class="transform hover:scale-110 transition hover:uppercase hover:border-b border-blue-300 text-white">Home</a>
                </li>
                <li><a href="{{ route('about') }}"
                        class="transform hover:scale-110 transition hover:uppercase hover:border-b border-blue-300 text-white">About</a>
                </li>
                <li><a href="{{ route('contact') }}"
                        class="transform hover:scale-110 transition hover:uppercase hover:border-b border-blue-300 text-white">Contact</a>
                </li>
                <li><a href="{{ route('admission') }}"
                        class="transform hover:scale-110 transition hover:uppercase hover:border-b border-blue-300 text-white">Admission</a>
                </li>
                <li><a href="{{ route('gallery') }}"
                        class="transform hover:scale-110 transition hover:uppercase hover:border-b border-blue-300 text-white">Gallery</a>
                </li>
                <li><a href="#"
                        class="transform hover:scale-110 transition hover:uppercase hover:border-b border-blue-300 text-white">Career</a>
                </li>
            </ul>
        </div>

        <!-- Academics Section -->
        <div>
            <h3
                class="transform hover:scale-125 transition hover:uppercase font-semibold text-base mb-4 border-b border-blue-500 inline-block pb-1">
                Academics</h3>
            <ul class="space-y-2 text-gray-300">
                <li><a href="#"
                        class="transform hover:scale-110 transition hover:uppercase hover:border-b border-blue-300 text-white">Curriculum</a>
                </li>
                <li><a href="#"
                        class="transform hover:scale-110 transition hover:uppercase hover:border-b border-blue-300 text-white">Subjects
                        Offered</a></li>
                <li><a href="#"
                        class="transform hover:scale-110 transition hover:uppercase hover:border-b border-blue-300 text-white">Clubs
                        & Societies</a></li>
                <li><a href="#"
                        class="transform hover:scale-110 transition hover:uppercase hover:border-b border-blue-300 text-white">Boarding
                        Life</a></li>
            </ul>
        </div>

        <!-- Contact Info -->
        <div>
            <h3
                class="transform hover:scale-125 transition hover:uppercase font-semibold text-base mb-4 border-b border-blue-500 inline-block pb-1">
                Contact</h3>
            <address class="not-italic leading-loose text-gray-300">
                13 Chief Mbanefo E. Uduezue Street,
                Umubele, Awka, Anambra State<br>
                📞 <a href="tel:+2348066025508" class="hover:text-white">+234 806 602 5508</a><br>
                📞 <a href="tel:+2348037315741" class="hover:text-white">+234 803 731 5741</a><br>
                📧 <a href="mailto:info@elitesinternationalcollege.com" class="hover:text-white">info@elitesinternationalcollege.com</a>
            </address>
        </div>

        <!-- Subscribe & Socials -->
        <div>
            <h3
                class="transform hover:scale-125 transition hover:uppercase font-semibold text-base mb-4 border-b border-blue-500 inline-block pb-1">
                Stay Updated</h3>
            <form class="flex items-center space-x-2 mb-4">
                <input type="email" placeholder="Your Email"
                    class="flex-1 px-3 py-2 rounded-md bg-blue-800 text-white placeholder-gray-400 focus:ring focus:ring-blue-400 text-sm">
                <button
                    class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md text-sm font-medium">Subscribe</button>
            </form>
            <div class="flex space-x-4 text-white">
                <a href="#" aria-label="WhatsApp"
                    class="hover:text-green-500 transform hover:scale-110 transition"><i
                        class="fab fa-whatsapp"></i></a>
                <a href="#" aria-label="Facebook"
                    class="hover:text-blue-300 transform hover:scale-110 transition"><i
                        class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Twitter"
                    class="hover:text-gray-400 transform hover:scale-110 transition"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="Instagram"
                    class="hover:text-red-300 transform hover:scale-110 transition"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"
                    class="hover:text-blue-300 transform hover:scale-110 transition"><i
                        class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </div>

    <!-- Testimonial -->
    <div class="max-w-5xl mx-auto text-center text-gray-300 text-sm px-6 mb-12 animate__animated animate__fadeIn">
        <blockquote class="italic border-l-4 border-blue-500 pl-4 text-gray-300">
            “Elites International College provided a foundation I still rely on today. The values and vision here are
            truly life-changing.”
        </blockquote>
        <p class="mt-2 text-xs text-gray-400">– Alumni, Class of 2021</p>
    </div>

    <!-- Mission / Vision -->
    <div
        class="bg-blue-950 px-6 lg:px-20 py-6 grid sm:grid-cols-2 gap-8 text-sm text-gray-300 border-t border-blue-800">
        <div>
            <h4 class="text-white font-semibold mb-1">Our Mission</h4>
            <p>To nurture students with a sound mind and strong moral principles through globally competitive education.
            </p>
        </div>
        <div>
            <h4 class="text-white font-semibold mb-1">Our Vision</h4>
            <p>To be Africa’s leading institution for academic and character excellence.</p>
        </div>
    </div>

    <!-- Copyright -->


    <div
        class="text-center py-4 text-xs bg-blue-950 text-gray-400  px-6 lg:px-20  grid sm:grid-cols-2 gap-8 border-t border-blue-800">
        <div>
            <p>&copy; {{ date('Y') }} Elites International College. All rights reserved.</p>
        </div>
        <div
            class="text-center py-4 text-xs bg-blue-950 text-gray-400  px-6 lg:px-20  grid sm:grid-cols-2 gap-8  border-blue-800">
            <p>{{ date('F d Y H:i (T)') }} - Wireless Terminal.</p>
        </div>
    </div>
</footer>
