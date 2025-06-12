@extends('layouts.pages')

@section('title', 'Elites International College')

@section('content')


<section x-data="{
        currentSlide: 0,
        slides: [{
                image: 'https://images.pexels.com/photos/3184323/pexels-photo-3184323.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260',
                title: 'Shaping Future Leaders',
                subtitle: 'Empowering Minds, Transforming Futures.',
                cta: 'Apply Now',
                link: '{{ route('admission') }}'
            },
            {
                image: 'https://images.pexels.com/photos/8067833/pexels-photo-8067833.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260',
                title: 'Unleash Your Potential',
                subtitle: 'Where Excellence Meets Opportunity.',
                cta: 'Explore Programs',
                link: '{{ route('about') }}'
            },
            {
                image: 'https://images.pexels.com/photos/1184578/pexels-photo-1184578.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260',
                title: 'Join the Elites Family',
                subtitle: 'A Tradition of Excellence, A Future of Impact.',
                cta: 'Visit School',
                link: '{{ route('contact') }}'
            }
        ],
        direction: 'right'
    }" 
    x-init="setInterval(() => {
        direction = 'right';
        currentSlide = (currentSlide + 1) % slides.length;
    }, 8000)" 
    class="relative overflow-hidden h-[80vh] w-full group">
    
    <!-- Slides -->
    <template x-for="(slide, index) in slides" :key="index">
        <div 
            x-show="currentSlide === index" 
            x-transition:enter="transition ease-out duration-1000 transform"
            x-transition:enter-start="opacity-0 translate-x-full"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-1000 transform"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 -translate-x-full"
            class="absolute inset-0 bg-cover bg-center flex items-center justify-center"
            :style="`background-image: url(${slide.image});`">
            
            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-gray-900/80 to-blue-900/60 z-0"></div>
            
            <!-- Content Container -->
            <div class="relative z-10 w-full max-w-7xl px-6 mx-auto text-center lg:text-left">
                <div class="lg:w-1/2 space-y-6 animate__animated" 
                    :class="{
                        'animate__fadeInLeft': currentSlide === index,
                        'animate__delay-300ms': currentSlide === index
                    }">
                    
                    <!-- Title with animated underline -->
                    <h1 class="text-4xl md:text-6xl font-bold text-white leading-tight">
                        <span class="relative inline-block">
                            <span x-text="slide.title" class="relative z-10"></span>
                            <span class="absolute bottom-0 left-0 w-full h-1 bg-emerald-400 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></span>
                        </span>
                    </h1>
                    
                    <!-- Subtitle with fade-in -->
                    <p class="text-xl md:text-2xl text-gray-200 font-light animate__animated animate__fadeIn animate__delay-500ms" 
                       x-text="slide.subtitle"></p>
                    
                    <!-- CTA Button with floating animation -->
                    <div class="animate__animated animate__fadeInUp animate__delay-700ms">
                        <a :href="slide.link" 
                           class="inline-flex items-center px-8 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-lg shadow-lg transform transition-all duration-300 hover:scale-105 hover:shadow-xl group">
                            <span x-text="slide.cta" class="mr-2"></span>
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Navigation Arrows -->
    <button @click="direction = 'left'; currentSlide = (currentSlide - 1 + slides.length) % slides.length" 
            class="absolute left-4 top-1/2 -translate-y-1/2 z-20 p-3 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-full transition-all duration-300 group">
        <svg class="w-6 h-6 text-white group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </button>
    
    <button @click="direction = 'right'; currentSlide = (currentSlide + 1) % slides.length" 
            class="absolute right-4 top-1/2 -translate-y-1/2 z-20 p-3 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-full transition-all duration-300 group">
        <svg class="w-6 h-6 text-white group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </button>

    <!-- Progress Indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex space-x-2">
        <template x-for="(slide, index) in slides" :key="index">
            <button @click="currentSlide = index" class="relative">
                <div class="w-16 h-1 bg-white/30 rounded-full overflow-hidden">
                    <div x-show="currentSlide === index" 
                         x-transition:enter="transition-all ease-linear duration-8000" 
                         x-transition:enter-start="w-0" 
                         x-transition:enter-end="w-full"
                         class="h-full bg-white origin-left"
                         style="display: none;"></div>
                </div>
                <span class="sr-only" x-text="`Go to slide ${index + 1}`"></span>
            </button>
        </template>
    </div>

    <!-- Scrolling Indicator -->
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 animate-bounce">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
        </svg>
    </div>
</section>



<!-- Why Choose Us Section -->
<section class="py-20 bg-gradient-to-b from-blue-50 to-white relative overflow-hidden">
    <!-- Decorative elements -->
    <div class="absolute -top-20 -right-20 w-64 h-64 bg-blue-100 rounded-full opacity-20"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-100 rounded-full opacity-20"></div>
    
    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center mb-16">
            <span class="text-blue-600 font-semibold tracking-wider">WHY ELITES?</span>
            <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mt-4">
                <span class="relative inline-block">
                    <span class="relative z-10">Why Choose Us?</span>
                    <span class="absolute bottom-0 left-0 w-full h-3 bg-emerald-300 opacity-30 -z-0"></span>
                </span>
            </h2>
            <p class="mt-6 text-lg text-gray-600 max-w-2xl mx-auto">
                Experience a world-class education that shapes you into a future leader.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Feature 1: Academic Excellence -->
            <div class="group relative bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-all duration-500 border border-gray-100 hover:border-emerald-100">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-white opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-xl"></div>
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6 group-hover:bg-emerald-100 transition-colors duration-300">
                        <i class="fas fa-graduation-cap text-3xl text-blue-600 group-hover:text-emerald-600 transition-colors duration-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Academic Excellence</h3>
                    <p class="text-gray-600">We prepare students for success with a rigorous academic curriculum designed to challenge and inspire.</p>
                    <div class="mt-6">
                        <div class="w-8 h-1 bg-blue-200 group-hover:bg-emerald-400 transition-colors duration-300"></div>
                    </div>
                </div>
            </div>

            <!-- Feature 2: Qualified Teachers -->
            <div class="group relative bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-all duration-500 border border-gray-100 hover:border-emerald-100">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-white opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-xl"></div>
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6 group-hover:bg-emerald-100 transition-colors duration-300">
                        <i class="fas fa-chalkboard-teacher text-3xl text-blue-600 group-hover:text-emerald-600 transition-colors duration-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Qualified Teachers</h3>
                    <p class="text-gray-600">Our passionate mentors provide personalized learning experiences to help each student thrive.</p>
                    <div class="mt-6">
                        <div class="w-8 h-1 bg-blue-200 group-hover:bg-emerald-400 transition-colors duration-300"></div>
                    </div>
                </div>
            </div>

            <!-- Feature 3: Moral Standards -->
            <div class="group relative bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-all duration-500 border border-gray-100 hover:border-emerald-100">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-white opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-xl"></div>
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6 group-hover:bg-emerald-100 transition-colors duration-300">
                        <i class="fas fa-heart text-3xl text-blue-600 group-hover:text-emerald-600 transition-colors duration-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Moral Standards</h3>
                    <p class="text-gray-600">We instill values of integrity and respect, ensuring students lead with character and responsibility.</p>
                    <div class="mt-6">
                        <div class="w-8 h-1 bg-blue-200 group-hover:bg-emerald-400 transition-colors duration-300"></div>
                    </div>
                </div>
            </div>

            <!-- Feature 4: Global Competitiveness -->
            <div class="group relative bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-all duration-500 border border-gray-100 hover:border-emerald-100">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-white opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-xl"></div>
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6 group-hover:bg-emerald-100 transition-colors duration-300">
                        <i class="fas fa-globe-americas text-3xl text-blue-600 group-hover:text-emerald-600 transition-colors duration-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Global Competitiveness</h3>
                    <p class="text-gray-600">We prepare students to excel globally with international perspectives and leadership opportunities.</p>
                    <div class="mt-6">
                        <div class="w-8 h-1 bg-blue-200 group-hover:bg-emerald-400 transition-colors duration-300"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Us Preview Section -->
<section class="py-20 bg-gradient-to-b from-white to-blue-50 relative overflow-hidden">
    <!-- Decorative elements -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-blue-100 rounded-full opacity-20 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-blue-100 rounded-full opacity-20 translate-x-1/2 translate-y-1/2"></div>
    
    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center mb-16">
            <span class="text-blue-600 font-semibold tracking-wider">OUR MISSION</span>
            <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mt-4">
                <span class="relative inline-block">
                    <span class="relative z-10">Shaping Tomorrow's Leaders</span>
                    <span class="absolute bottom-0 left-0 w-full h-3 bg-emerald-300 opacity-30 -z-0"></span>
                </span>
            </h2>
            <p class="mt-6 text-lg text-gray-600 max-w-3xl mx-auto">
                At Elites International College, we are dedicated to shaping the leaders of tomorrow through world-class education and rich cultural experiences.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
            <div class="space-y-6">
                <p class="text-lg text-gray-600">
                    We believe in a holistic approach to education, nurturing both the mind and character. Our programs are designed to empower students to achieve academic success while cultivating personal growth and social responsibility.
                </p>
                <p class="text-lg text-gray-600">
                    With state-of-the-art facilities and innovative teaching methods, we create an environment where students can discover their passions and unlock their full potential.
                </p>
                <div class="mt-8">
                    <a href="{{ route('about') }}" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-emerald-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform transition-all duration-300 hover:scale-105 group">
                        <span>Learn More About Us</span>
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Photo Gallery -->
            <div class="grid grid-cols-2 gap-4">
                <div class="relative rounded-xl overflow-hidden shadow-lg group h-48">
                    <img src="https://images.pexels.com/photos/3184323/pexels-photo-3184323.jpeg" alt="Classroom" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-blue-900/20 group-hover:bg-blue-900/10 transition-colors duration-500"></div>
                </div>
                <div class="relative rounded-xl overflow-hidden shadow-lg group h-48">
                    <img src="https://images.pexels.com/photos/8067833/pexels-photo-8067833.jpeg" alt="Student Graduation" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-blue-900/20 group-hover:bg-blue-900/10 transition-colors duration-500"></div>
                </div>
                <div class="relative rounded-xl overflow-hidden shadow-lg group h-48">
                    <img src="https://images.pexels.com/photos/1184578/pexels-photo-1184578.jpeg" alt="Students Collaborating" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-blue-900/20 group-hover:bg-blue-900/10 transition-colors duration-500"></div>
                </div>
                <div class="relative rounded-xl overflow-hidden shadow-lg group h-48">
                    <img src="https://images.pexels.com/photos/5212345/pexels-photo-5212345.jpeg" alt="Science Lab" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-blue-900/20 group-hover:bg-blue-900/10 transition-colors duration-500"></div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="bg-white rounded-2xl shadow-lg p-8 max-w-6xl mx-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Years of Excellence -->
                <div class="text-center p-6 group">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-emerald-50 transition-colors duration-500">
                        <i class="fas fa-calendar-alt text-3xl text-blue-600 group-hover:text-emerald-600 transition-colors duration-500"></i>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-900 mb-2">
                        <span x-data="{ count: 0 }" x-init="let interval = setInterval(() => {
                            if (count < 25) count++;
                            else clearInterval(interval);
                        }, 60)" x-text="count"></span>+
                    </h3>
                    <p class="text-lg text-gray-600">Years of Excellence</p>
                </div>

                <!-- Students Enrolled -->
                <div class="text-center p-6 group">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-emerald-50 transition-colors duration-500">
                        <i class="fas fa-users text-3xl text-blue-600 group-hover:text-emerald-600 transition-colors duration-500"></i>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-900 mb-2">
                        <span x-data="{ count: 0 }" x-init="let interval = setInterval(() => {
                            if (count < 1000) count += 10;
                            else clearInterval(interval);
                        }, 15)" x-text="count.toLocaleString()"></span>+
                    </h3>
                    <p class="text-lg text-gray-600">Students Enrolled</p>
                </div>

                <!-- Programs Offered -->
                <div class="text-center p-6 group">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-emerald-50 transition-colors duration-500">
                        <i class="fas fa-book-open text-3xl text-blue-600 group-hover:text-emerald-600 transition-colors duration-500"></i>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-900 mb-2">
                        <span x-data="{ count: 0 }" x-init="let interval = setInterval(() => {
                            if (count < 30) count++;
                            else clearInterval(interval);
                        }, 80)" x-text="count"></span>+
                    </h3>
                    <p class="text-lg text-gray-600">Programs Offered</p>
                </div>

                <!-- Satisfaction Rate -->
                <div class="text-center p-6 group">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-emerald-50 transition-colors duration-500">
                        <i class="fas fa-heart text-3xl text-blue-600 group-hover:text-emerald-600 transition-colors duration-500"></i>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-900 mb-2">
                        <span x-data="{ count: 0 }" x-init="let interval = setInterval(() => {
                            if (count < 98) count++;
                            else clearInterval(interval);
                        }, 30)" x-text="count + '%'"></span>
                    </h3>
                    <p class="text-lg text-gray-600">Satisfaction Rate</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Academics Section -->
<section class="py-20 bg-gradient-to-b from-gray-50 to-white relative overflow-hidden">
    <!-- Decorative elements -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-blue-100 rounded-full opacity-10 -translate-y-1/2 translate-x-1/2"></div>
    
    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="text-center mb-16" x-data="{ visible: false }" x-intersect="visible = true">
            <span class="text-blue-600 font-semibold tracking-wider" x-show="visible" 
                  x-transition:enter="transition ease-out duration-500"
                  x-transition:enter-start="opacity-0 translate-y-4"
                  x-transition:enter-end="opacity-100 translate-y-0">OUR PROGRAMS</span>
            <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mt-4" x-show="visible"
                x-transition:enter="transition ease-out duration-500 delay-100"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0">
                <span class="relative inline-block">
                    <span class="relative z-10">Our Academics</span>
                    <span class="absolute bottom-0 left-0 w-full h-3 bg-emerald-300 opacity-30 -z-0"></span>
                </span>
            </h2>
            <p class="mt-6 text-lg text-gray-600 max-w-2xl mx-auto" x-show="visible"
               x-transition:enter="transition ease-out duration-500 delay-200"
               x-transition:enter-start="opacity-0 translate-y-4"
               x-transition:enter-end="opacity-100 translate-y-0">
                Explore the unique learning experiences tailored for each stage of growth and development.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Card Template -->
            <template x-for="(card, index) in [
                {
                    title: 'Nursery School',
                    desc: 'A warm and playful environment to build curiosity, motor skills, and early literacy.',
                    icon: `<path d='M12 2L2 7l10 5 10-5-10-5zm0 6v14' />`,
                    href: '#'
                },
                {
                    title: 'Primary School',
                    desc: 'Building strong foundations in literacy, numeracy, and creative thinking through fun learning.',
                    icon: `<path d='M5 3h14a2 2 0 012 2v14l-8-4-8 4V5a2 2 0 012-2z' />`,
                    href: '#'
                },
                {
                    title: 'Secondary School',
                    desc: 'Advanced academics, personal development, and leadership skills for future readiness.',
                    icon: `<path d='M4 6h16M4 10h16M4 14h10M4 18h6' />`,
                    href: '#'
                }
            ]" :key="card.title">
                <div x-data x-intersect="$el.classList.add('animate-fade-in-up')" 
                     :class="`delay-${index * 100}`"
                     class="bg-white p-8 rounded-2xl border border-blue-50 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-500 ease-in-out group overflow-hidden relative">
                    <!-- Animated background element -->
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-emerald-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-0"></div>
                    
                    <div class="relative z-10">
                        <div class="w-20 h-20 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mb-6 mx-auto group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-all duration-500 rotate-0 group-hover:rotate-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.5" x-html="card.icon"></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 text-center group-hover:text-blue-800 transition-colors duration-300" x-text="card.title"></h3>
                        <p class="text-gray-600 mt-4 text-center" x-text="card.desc"></p>
                        <div class="mt-6 text-center">
                            <a :href="card.href" class="inline-flex items-center text-blue-600 hover:text-emerald-600 font-medium group-hover:underline transition-all duration-300">
                                Learn More
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 bg-gradient-to-b from-white to-gray-50 relative overflow-hidden">
    <!-- Decorative elements -->
    <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-blue-100 rounded-full opacity-10"></div>
    
    <div class="max-w-5xl mx-auto px-6 relative z-10">
        <div class="text-center mb-12" x-data="{ visible: false }" x-intersect="visible = true">
            <span class="text-blue-600 font-semibold tracking-wider" x-show="visible" 
                  x-transition:enter="transition ease-out duration-500"
                  x-transition:enter-start="opacity-0 translate-y-4"
                  x-transition:enter-end="opacity-100 translate-y-0">VOICES OF ELITES</span>
            <h2 class="text-4xl font-bold text-gray-900 mt-4" x-show="visible"
                x-transition:enter="transition ease-out duration-500 delay-100"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0">
                What People Say
            </h2>
        </div>

        <div class="relative" x-data="{
            active: 0,
            testimonials: [
                {
                    quote: 'Elites International College transformed my son’s academic journey. The teachers are truly invested in each student\'s success.',
                    name: 'Mrs. Adebayo',
                    role: 'Parent',
                    avatar: 'https://randomuser.me/api/portraits/women/43.jpg'
                },
                {
                    quote: 'The hands-on learning and moral training made me confident and capable. I\'m proud to be an alumnus of this great institution.',
                    name: 'Chinedu Okoro',
                    role: 'Alumnus',
                    avatar: 'https://randomuser.me/api/portraits/men/32.jpg'
                },
                {
                    quote: 'From day one, I felt welcomed and inspired. The environment is perfect for any student who wants to excel academically and personally.',
                    name: 'Sarah Bello',
                    role: 'Current Student',
                    avatar: 'https://randomuser.me/api/portraits/women/65.jpg'
                }
            ]
        }" x-init="setInterval(() => active = (active + 1) % testimonials.length, 8000)">
            
            <!-- Testimonial Cards -->
            <div class="relative h-96">
                <template x-for="(testimonial, index) in testimonials" :key="index">
                    <div x-show="active === index" x-transition:enter="transition ease-out duration-500" 
                         x-transition:enter-start="opacity-0 scale-95" 
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-300 absolute top-0 left-0 w-full"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="bg-white p-8 rounded-2xl shadow-lg max-w-2xl mx-auto">
                        <div class="flex flex-col items-center text-center">
                            <img :src="testimonial.avatar" :alt="testimonial.name" class="w-16 h-16 rounded-full object-cover border-4 border-blue-100 mb-4">
                            <p class="text-xl text-gray-700 italic mb-6">"<span x-text="testimonial.quote"></span>"</p>
                            <div>
                                <div class="font-semibold text-blue-800" x-text="testimonial.name"></div>
                                <div class="text-sm text-gray-500" x-text="testimonial.role"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Navigation Dots -->
            <div class="mt-8 flex justify-center space-x-3">
                <template x-for="(dot, i) in testimonials.length" :key="i">
                    <button @click="active = i" class="w-3 h-3 rounded-full relative" :class="active === i ? 'bg-blue-800' : 'bg-gray-300'">
                        <span class="sr-only" x-text="`Go to testimonial ${i + 1}`"></span>
                        <span x-show="active === i" class="absolute inset-0 rounded-full bg-blue-800 animate-ping opacity-30"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</section>

<!-- News & Events Section -->
<section class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-12" x-data="{ visible: false }" x-intersect="visible = true">
            <span class="text-blue-600 font-semibold tracking-wider" x-show="visible" 
                  x-transition:enter="transition ease-out duration-500"
                  x-transition:enter-start="opacity-0 translate-y-4"
                  x-transition:enter-end="opacity-100 translate-y-0">STAY UPDATED</span>
            <h2 class="text-4xl font-bold text-gray-900 mt-4" x-show="visible"
                x-transition:enter="transition ease-out duration-500 delay-100"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0">
                Latest News & Events
            </h2>
        </div>

        <div class="grid gap-8 md:grid-cols-3">
            <!-- Event 1 -->
            <div x-data x-intersect="$el.classList.add('animate-fade-in-up')" 
                 class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-500 group overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-emerald-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-0"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-colors duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-blue-800 mb-3">PTA Meeting – May 10</h3>
                    <p class="text-gray-600 mb-4">Join us to discuss student progress, new policies, and parent feedback.</p>
                    <a href="#" class="inline-flex items-center text-blue-600 hover:text-emerald-600 font-medium transition-colors duration-300">
                        View Details
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Event 2 -->
            <div x-data x-intersect="$el.classList.add('animate-fade-in-up')" 
                 class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-500 group overflow-hidden relative delay-100">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-emerald-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-0"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-colors duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-blue-800 mb-3">Midterm Exams – June 3–7</h3>
                    <p class="text-gray-600 mb-4">Ensure your wards are well prepared. Check the schedule and syllabus online.</p>
                    <a href="#" class="inline-flex items-center text-blue-600 hover:text-emerald-600 font-medium transition-colors duration-300">
                        Learn More
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Event 3 -->
            <div x-data x-intersect="$el.classList.add('animate-fade-in-up')" 
                 class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-500 group overflow-hidden relative delay-200">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-emerald-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-0"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-colors duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 010 18z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-blue-800 mb-3">Graduation – July 25</h3>
                    <p class="text-gray-600 mb-4">Celebrate academic excellence as our senior students graduate with pride.</p>
                    <a href="#" class="inline-flex items-center text-blue-600 hover:text-emerald-600 font-medium transition-colors duration-300">
                        Event Details
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-gradient-to-r from-blue-800 to-blue-600 relative overflow-hidden">
    <!-- Decorative elements -->
    <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/10 rounded-full"></div>
    <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/10 rounded-full"></div>
    
    <div class="max-w-4xl mx-auto text-center px-6 relative z-10">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4" x-data="{ visible: false }" 
            x-intersect="visible = true" x-show="visible"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0">
            Join the Elites Family Today!
        </h2>
        <p class="mt-4 text-lg text-blue-100 max-w-2xl mx-auto" x-data="{ visible: false }" 
           x-intersect="visible = true" x-show="visible"
           x-transition:enter="transition ease-out duration-500 delay-100"
           x-transition:enter-start="opacity-0 translate-y-4"
           x-transition:enter-end="opacity-100 translate-y-0">
            Ready to give your child the best start in life? Admissions are open for all levels.
        </p>
        <div class="mt-8" x-data="{ visible: false }" 
             x-intersect="visible = true" x-show="visible"
             x-transition:enter="transition ease-out duration-500 delay-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">
            <a href="{{ route('admission') }}" 
               class="inline-flex items-center px-8 py-3 bg-white text-blue-900 font-bold rounded-full shadow-lg hover:bg-blue-100 hover:shadow-xl transform transition-all duration-300 hover:scale-105 group">
                Apply Now
                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </a>
        </div>
    </div>
</section>

    <section class="py-16 bg-gray-50">
        <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-10 items-center">
            <!-- Map -->
            <iframe class="w-full h-80 rounded-lg shadow" loading="lazy" style="border:0;" allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3930.220031261229!2d7.070343014768556!3d6.219634895504751!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x10438d77b1d8123d%3A0xc3892dc166d86778!2sUmubele%2C%20Awka%2C%20Anambra!5e0!3m2!1sen!2sng!4v1713892145672!5m2!1sen!2sng">
            </iframe>

            <!-- Contact Info -->
            <div>
                <h3 class="hover:uppercase text-2xl font-bold text-blue-900 mb-3">Visit or Contact Us</h3>
                <div class="mb-3">
                    <p class="text-gray-700 hover:uppercase "><strong>Address:</strong></p>
                    <p class="text-gray-600">
                        13 Chief Mbanefo E. Uduezue Street,<br>
                        Umubele, Awka, Anambra State, Nigeria
                    </p>
                </div>
                <div class="mb-3">
                    <p class="text-gray-700 hover:uppercase"><strong>Phone:</strong></p>
                    📞 <a href="tel:+2348066025508" class="text-gray-600 mb-1"> +234 806 602 5508</a><br>
                    📞 <a href="tel:+2348037315741" class="text-gray-600 pb-4">+234 803 731 5741</a>
                </div>
                <div class="mb-3">
                    <p class="text-gray-700 hover:uppercase"><strong>Email:</strong></p>
                    <a href="mailto:info@elitesinternationalcollege.com" class="text-gray-600 mb-6">📧 info@elitesinternationalcollege.com</a> <br>
                </div>

                <a href="{{ route('contact') }}"
                    class="inline-block bg-blue-900 text-white font-medium px-6 py-3 rounded-md hover:bg-blue-800 transition hover:uppercase">
                    Contact Us
                </a>
            </div>
        </div>
    </section>

@endsection
