@extends('layouts.pages')

@section('title', 'Home')

@section('content')

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


    <section class="py-16 bg-gray-50">
        <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-10 items-center">
            <!-- Map -->
            <iframe class="w-full h-80 rounded-lg shadow" loading="lazy" style="border:0;" allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3930.220031261229!2d7.070343014768556!3d6.219634895504751!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x10438d77b1d8123d%3A0xc3892dc166d86778!2sUmubele%2C%20Awka%2C%20Anambra!5e0!3m2!1sen!2sng!4v1713892145672!5m2!1sen!2sng">
            </iframe>

            <!-- Contact Info -->
            <div>
                <h3 class="text-2xl font-bold text-blue-900 mb-4">Visit or Contact Us</h3>
                <p class="text-gray-700 mb-2"><strong>Address:</strong></p>
                <p class="text-gray-600 mb-4">
                    13 Chief Mbanefo E. Uduezue Street,<br>
                    Umubele, Awka, Anambra State, Nigeria
                </p>

                <p class="text-gray-700 mb-2"><strong>Phone:</strong></p>
                <p class="text-gray-600 mb-1">📞 +234 806 602 5508</p>
                <p class="text-gray-600 mb-4">📞 +234 803 731 5741</p>

                <p class="text-gray-700 mb-2"><strong>Email:</strong></p>
                <p class="text-gray-600 mb-6">📧 info@elitescollege.com</p>

                <a href="{{ route('contact') }}"
                    class="inline-block bg-blue-900 text-white font-medium px-6 py-3 rounded-md hover:bg-blue-800 transition">
                    Contact Us
                </a>
            </div>
        </div>
    </section>



@endsection
