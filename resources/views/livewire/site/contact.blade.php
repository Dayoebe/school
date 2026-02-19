@extends('layouts.app', ['mode' => 'public'])

@section('title', 'Contact Us')

@section('content')
    <div class="bg-slate-50 text-slate-900">
        <section class="bg-slate-900 py-14 text-white sm:py-16">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="inline-flex items-center gap-2 rounded-full border border-red-200/40 bg-red-500/10 px-3 py-1 text-xs font-semibold text-red-200">
                    <i class="fas fa-envelope-open-text"></i>
                    <span>Contact Portal</span>
                </div>

                <h1 class="mt-4 text-3xl font-black leading-tight sm:text-4xl lg:text-5xl">
                    Contact Our School Team
                </h1>

                <p class="mt-4 max-w-3xl text-sm leading-relaxed text-slate-200 sm:text-base">
                    Send us your questions about admission, academics, fees, or support. Your message goes directly to
                    the dashboard so the team can respond quickly.
                </p>
            </div>
        </section>

        <section class="py-12">
            <div class="mx-auto grid max-w-6xl grid-cols-1 gap-6 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-xl font-black text-slate-900">Contact Information</h2>

                        <div class="mt-4 space-y-3 text-sm text-slate-700">
                            <p>
                                <strong>Address:</strong><br>
                                13 Chief Mbanefo E. Uduezue Street,<br>
                                Umubele, Awka, Anambra State, Nigeria
                            </p>
                            <p>
                                <strong>Phone:</strong><br>
                                <a href="tel:+2348066025508" class="font-semibold text-blue-700 hover:underline">+234 806 602 5508</a><br>
                                <a href="tel:+2348037315741" class="font-semibold text-blue-700 hover:underline">+234 803 731 5741</a>
                            </p>
                            <p>
                                <strong>Email:</strong><br>
                                <a href="mailto:info@elitesinternationalcollege.com" class="font-semibold text-blue-700 hover:underline">info@elitesinternationalcollege.com</a>
                            </p>
                        </div>
                    </div>

                    <iframe class="h-80 w-full rounded-2xl border border-slate-200 shadow-sm" loading="lazy" style="border:0;" allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3930.220031261229!2d7.070343014768556!3d6.219634895504751!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x10438d77b1d8123d%3A0xc3892dc166d86778!2sUmubele%2C%20Awka%2C%20Anambra!5e0!3m2!1sen!2sng!4v1713892145672!5m2!1sen!2sng">
                    </iframe>
                </div>

                <div>
                    @livewire('contacts.public-contact-form')
                </div>
            </div>
        </section>
    </div>
@endsection
