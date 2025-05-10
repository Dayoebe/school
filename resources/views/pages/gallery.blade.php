@extends('layouts.pages')

@section('title', 'Home')

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
                cta: 'Visit Campus',
                link: '{{ route('contact') }}'
            }
        ]
    }" x-init="setInterval(() => currentSlide = (currentSlide + 1) % slides.length, 8000)" class="relative overflow-hidden h-[70vh] md:h-[80vh] w-full">
        <!-- Slides -->
        <template x-for="(slide, index) in slides" :key="index">
            <div x-show="currentSlide === index" x-transition:enter="animate__animated animate__fadeIn"
                class="absolute inset-0 bg-cover bg-center flex items-center justify-center"
                :style="`background-image: url(${slide.image});`">
                <div
                    class="bg-blue-900/70 w-full h-full flex flex-col items-center justify-center text-center px-6 py-12 z-10">
                    <h1 class="text-3xl md:text-5xl font-extrabold text-white animate__animated animate__fadeInDown"
                        x-text="slide.title"></h1>
                    <p class="mt-4 text-lg md:text-xl text-gray-200 animate__animated animate__fadeInUp"
                        x-text="slide.subtitle"></p>
                    <a :href="slide.link"
                        class="mt-6 inline-block px-6 py-3 bg-white text-blue-900 font-semibold rounded-md shadow-md hover:bg-blue-100 transition-all animate__animated animate__fadeInUp animate__delay-1s"
                        x-text="slide.cta"></a>
                </div>
            </div>
        </template>

        <!-- Carousel Dots -->
        <div class="absolute bottom-6 w-full flex justify-center space-x-2 z-20">
            <template x-for="(slide, index) in slides" :key="index">
                <button @click="currentSlide = index" :class="currentSlide === index ? 'bg-white' : 'bg-blue-400'"
                    class="w-3 h-3 rounded-full transition-all"></button>
            </template>
        </div>

        <!-- Overlay Gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-900 via-transparent to-blue-800 opacity-70 z-0"></div>
    </section>



    <!-- About Us Preview Section -->
    <section class="py-16 bg-blue-50">
        <div class="container mx-auto text-center">
            <h2 class="text-4xl font-extrabold text-blue-900">Shaping Tomorrow’s Leaders</h2>
            <p class="mt-4 text-lg text-gray-700">
                At Elites International College, we are dedicated to shaping the leaders of tomorrow. Our world-class
                education system, paired with a rich cultural environment, empowers students to achieve academic success,
                cultivate personal growth, and contribute positively to society.
            </p>
            <p class="mt-4 text-lg text-gray-700">
                We believe in a holistic approach to education, nurturing both the mind and character. Explore more about us
                and our programs below.
            </p>

            <a href="{{ route('about') }}"
                class="mt-6 inline-block px-6 py-3 bg-blue-900 text-white font-semibold rounded-md hover:bg-blue-700 transition-all">Learn
                More</a>

            <!-- Photo Grid -->
            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="https://images.pexels.com/photos/3184323/pexels-photo-3184323.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260"
                        alt="Classroom" class="w-full h-48 object-cover">
                </div>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="https://images.pexels.com/photos/8067833/pexels-photo-8067833.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260"
                        alt="Student Graduation" class="w-full h-48 object-cover">
                </div>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="https://images.pexels.com/photos/1184578/pexels-photo-1184578.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260"
                        alt="Students Collaborating" class="w-full h-48 object-cover">
                </div>
            </div>

    


@endsection
