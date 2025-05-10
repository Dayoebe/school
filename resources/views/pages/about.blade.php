@extends('layouts.pages')

@section('title', 'Home')

@section('content')
   
   
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

            <!-- Animated Stats Section -->
            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

                <!-- Years of Excellence -->
                <div
                    class="transition-all duration-300 transform hover:scale-105 bg-white p-8 rounded-lg shadow-lg text-center">
                    <h3 class="text-3xl font-bold text-blue-900">
                        <span x-data="{ count: 0 }" x-init="let interval = setInterval(() => {
                            if (count < 25) count++;
                            else clearInterval(interval);
                        }, 150)" x-text="count"></span>+
                    </h3>
                    <p class="text-lg text-gray-600">Years of Excellence</p>
                </div>

                <!-- Students Enrolled -->
                <div
                    class="transition-all duration-300 transform hover:scale-105 bg-white p-8 rounded-lg shadow-lg text-center">
                    <h3 class="text-3xl font-bold text-blue-900">
                        <span x-data="{ count: 0 }" x-init="let interval = setInterval(() => {
                            if (count < 1000) count += 5;
                            else clearInterval(interval);
                        }, 30)" x-text="count"></span>+
                    </h3>
                    <p class="text-lg text-gray-600">Students Enrolled</p>
                </div>

                <!-- Programs Offered -->
                <div
                    class="transition-all duration-300 transform hover:scale-105 bg-white p-8 rounded-lg shadow-lg text-center">
                    <h3 class="text-3xl font-bold text-blue-900">
                        <span x-data="{ count: 0 }" x-init="let interval = setInterval(() => {
                            if (count < 30) count++;
                            else clearInterval(interval);
                        }, 120)" x-text="count"></span>+
                    </h3>
                    <p class="text-lg text-gray-600">Programs Offered</p>
                </div>

                <!-- Satisfaction Rate -->
                <div
                    class="transition-all duration-300 transform hover:scale-105 bg-white p-8 rounded-lg shadow-lg text-center">
                    <h3 class="text-3xl font-bold text-blue-900">
                        <span x-data="{ count: 0 }" x-init="let interval = setInterval(() => {
                            if (count < 100) count++;
                            else clearInterval(interval);
                        }, 40)" x-text="count + '%'"></span>
                    </h3>
                    <p class="text-lg text-gray-600">Student Satisfaction Rate</p>
                </div>

            </div>


        </div>
    </section>

     <!-- Why Choose Us Section -->
     <section class="py-16 bg-blue-50">
        <div class="container mx-auto text-center">
            <h2 class="hover:uppercase text-4xl font-extrabold text-blue-900">Why Choose Us?</h2>
            <p class="mt-4 text-lg text-gray-700">Experience a world-class education that shapes you into a future leader.
            </p>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12">

                <!-- Feature 1: Academic Excellence -->
                <div
                    class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-125">
                    <div class="text-blue-600 text-5xl mb-4">
                        <i class="fas fa-graduation-cap"></i> <!-- Icon for Academic Excellence -->
                    </div>
                    <h3 class="text-xl font-semibold text-blue-900 mb-2">Academic Excellence</h3>
                    <p class="text-gray-600">We prepare students for success with a rigorous academic curriculum designed to
                        challenge and inspire, ensuring they are ready for the world.</p>
                </div>

                <!-- Feature 2: Qualified Teachers -->
                <div
                    class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-125">
                    <div class="text-blue-600 text-5xl mb-4">
                        <i class="fas fa-chalkboard-teacher"></i> <!-- Icon for Qualified Teachers -->
                    </div>
                    <h3 class="text-xl font-semibold text-blue-900 mb-2">Qualified Teachers</h3>
                    <p class="text-gray-600">Our teachers are passionate mentors with years of experience, dedicated to
                        providing a personalized and dynamic learning experience for every student.</p>
                </div>

                <!-- Feature 3: Moral Standards -->
                <div
                    class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-125">
                    <div class="text-blue-600 text-5xl mb-4">
                        <i class="fas fa-heart"></i> <!-- Icon for Moral Standards -->
                    </div>
                    <h3 class="text-xl font-semibold text-blue-900 mb-2">Moral Standards</h3>
                    <p class="text-gray-600">At Elites, we instill values of integrity, respect, and accountability,
                        ensuring our students not only excel academically but lead with character and responsibility.</p>
                </div>

                <!-- Feature 4: Global Competitiveness -->
                <div
                    class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-125">
                    <div class="text-blue-600 text-5xl mb-4">
                        <i class="fas fa-globe-americas"></i> <!-- Icon for Global Competitiveness -->
                    </div>
                    <h3 class="text-xl font-semibold text-blue-900 mb-2">Global Competitiveness</h3>
                    <p class="text-gray-600">We prepare our students to excel on a global stage by offering international
                        perspectives, leadership opportunities, and cutting-edge technology skills.</p>
                </div>

            </div>
        </div>
    </section>



    <section class="py-20 bg-gray-100">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-extrabold text-blue-900">Our Academics</h2>
                <p class="mt-4 text-gray-600 text-lg max-w-2xl mx-auto">Explore the unique learning experiences tailored for
                    each stage of growth and development.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Card Template -->
                <template
                    x-for="card in [
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
        ]"
                    :key="card.title">
                    <div x-data x-intersect="$el.classList.add('animate-fade-in-up')"
                        class="bg-white p-8 rounded-2xl border border-blue-100 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-500 ease-in-out">
                        <div
                            class="w-16 h-16 bg-blue-50 text-blue-700 rounded-full flex items-center justify-center mb-5 mx-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" x-html="card.icon"></svg>
                        </div>
                        <h3 class="text-xl font-semibold text-blue-900 text-center" x-text="card.title"></h3>
                        <p class="text-gray-600 mt-3 text-center" x-text="card.desc"></p>
                        <div class="mt-5 text-center">
                            <a :href="card.href" class="text-blue-700 hover:underline font-medium">Learn More →</a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- Testimony -->
    <section class="py-16 bg-white">
        <div class="max-w-5xl mx-auto px-6 text-center" x-data="{
            active: 0,
            testimonials: [{
                    quote: 'Elites International College transformed my son’s academic journey. The teachers are truly invested.',
                    name: 'Mrs. Adebayo',
                    role: 'Parent'
                },
                {
                    quote: 'The hands-on learning and moral training made me confident and capable. I’m proud to be an alum.',
                    name: 'Chinedu Okoro',
                    role: 'Alumnus'
                },
                {
                    quote: 'From day one, I felt welcomed and inspired. The environment is perfect for any student.',
                    name: 'Sarah Bello',
                    role: 'Current Student'
                }
            ]
        }" x-init="setInterval(() => active = (active + 1) % testimonials.length, 8000)">
            <h2 class="text-3xl md:text-4xl font-bold text-blue-900 mb-6">What People Say</h2>
            <template x-for="(testimonial, index) in testimonials" :key="index">
                <div x-show="active === index" x-transition class="text-gray-700">
                    <p class="text-xl italic max-w-3xl mx-auto">“<span x-text="testimonial.quote"></span>”</p>
                    <div class="mt-4 font-semibold text-blue-800" x-text="testimonial.name"></div>
                    <div class="text-sm text-gray-500" x-text="testimonial.role"></div>
                </div>
            </template>
            <div class="mt-6 flex justify-center space-x-2">
                <template x-for="(dot, i) in testimonials.length">
                    <button class="w-3 h-3 rounded-full" :class="active === i ? 'bg-blue-800' : 'bg-gray-300'"
                        @click="active = i"></button>
                </template>
            </div>
        </div>
    </section>

    <!------- CTA -->
    <section class="bg-blue-900 py-12">
        <div class="max-w-4xl mx-auto text-center px-6 text-white">
            <h2 class="text-3xl md:text-4xl font-bold">Join the Elites Family Today!</h2>
            <p class="mt-4 text-lg">Ready to give your child the best start in life? Admissions are open for all levels.
            </p>
            <a href="{{ route('admission') }}"
                class="mt-6 inline-block bg-white text-blue-900 font-semibold px-8 py-3 rounded-full shadow hover:bg-blue-100 transition-all">
                Apply Now
            </a>
        </div>
    </section>




@endsection
