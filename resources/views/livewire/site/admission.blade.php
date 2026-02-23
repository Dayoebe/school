@extends('layouts.app', ['mode' => 'public'])

@section('title', 'Admission')

@php
    $settings = $publicSiteSettings ?? [];
    $admissionPage = data_get($settings, 'admission_page', []);
    $contactPhonePrimary = (string) data_get($settings, 'contact.phone_primary', '');
    $contactPhonePrimaryHref = preg_replace('/[^0-9+]/', '', $contactPhonePrimary);
@endphp

@section('content')
    <div class="bg-slate-50 text-slate-900">
        <section class="relative overflow-hidden bg-slate-900 py-14 sm:py-16">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -left-20 -top-20 h-56 w-56 rounded-full bg-red-500/20 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-64 w-64 rounded-full bg-violet-500/20 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="animate__animated animate__fadeInDown inline-flex items-center gap-2 rounded-full border border-orange-200/40 bg-orange-500/10 px-3 py-1 text-xs font-semibold text-orange-200">
                    <i class="fas fa-file-signature"></i>
                    <span>{{ data_get($admissionPage, 'hero_badge') }}</span>
                </div>

                <h1 class="animate__animated animate__fadeInUp mt-4 text-3xl font-black leading-tight text-white sm:text-4xl lg:text-5xl">
                    {{ data_get($admissionPage, 'hero_title') }}
                    <span class="mt-1 block text-red-300">{{ data_get($admissionPage, 'hero_highlight') }}</span>
                </h1>

                <p class="animate__animated animate__fadeInUp animate__delay-1s mt-4 max-w-3xl text-sm leading-relaxed text-slate-200 sm:text-base">
                    {{ data_get($admissionPage, 'hero_description') }}
                </p>

                <div class="mt-6 flex flex-wrap gap-2 text-xs font-bold">
                    <a href="#form" class="rounded-full bg-red-100 px-4 py-2 text-red-700 transition hover:bg-red-200">Admission Form</a>
                    <a href="#process" class="rounded-full bg-blue-100 px-4 py-2 text-blue-700 transition hover:bg-blue-200">Process</a>
                    <a href="#requirements" class="rounded-full bg-teal-100 px-4 py-2 text-teal-700 transition hover:bg-teal-200">Requirements</a>
                </div>
            </div>
        </section>

        <section id="process" class="py-12">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-blue-700">How It Works</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Simple, trackable admission flow</h2>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-red-700">Step 1</p>
                        <h3 class="mt-2 text-base font-black text-red-900">Submit Form</h3>
                        <p class="mt-2 text-sm text-red-900/80">Provide student and guardian details using the admission form.</p>
                    </div>
                    <div class="rounded-2xl border border-orange-200 bg-orange-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-orange-700">Step 2</p>
                        <h3 class="mt-2 text-base font-black text-orange-900">Dashboard Review</h3>
                        <p class="mt-2 text-sm text-orange-900/80">Admin team reviews submissions from the Admission Registrations dashboard.</p>
                    </div>
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Step 3</p>
                        <h3 class="mt-2 text-base font-black text-blue-900">Contact / Screening</h3>
                        <p class="mt-2 text-sm text-blue-900/80">Qualified candidates are contacted for follow-up and school interaction.</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Step 4</p>
                        <h3 class="mt-2 text-base font-black text-emerald-900">Enrollment</h3>
                        <p class="mt-2 text-sm text-emerald-900/80">Approved applications are enrolled as real student records in dashboard.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="form" class="bg-white py-12">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-red-700">Admission Form</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Register a student</h2>
                    <p class="mt-2 text-sm text-slate-600">Fill all required fields correctly. You will receive a reference number after submission.</p>
                </div>

                @livewire('admissions.public-admission-form')
            </div>
        </section>

        <section id="requirements" class="py-12">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-violet-700">Required Information</p>
                        <ul class="mt-3 space-y-2 text-sm text-slate-700">
                            <li><i class="fas fa-check-circle mr-2 text-lime-600"></i>Student full name, gender, and date of birth</li>
                            <li><i class="fas fa-check-circle mr-2 text-lime-600"></i>Preferred class (and section if available)</li>
                            <li><i class="fas fa-check-circle mr-2 text-lime-600"></i>Parent or guardian full contact details</li>
                            <li><i class="fas fa-check-circle mr-2 text-lime-600"></i>Residential address and previous school (if any)</li>
                        </ul>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-teal-700">Need Help?</p>
                        <p class="mt-3 text-sm text-slate-700">
                            If you need help filling the form, contact the admissions team.
                        </p>
                        <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                            <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">
                                <i class="fas fa-envelope"></i>
                                <span>Contact Admissions</span>
                            </a>
                            <a href="tel:{{ $contactPhonePrimaryHref }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100">
                                <i class="fas fa-phone"></i>
                                <span>Call {{ $contactPhonePrimary }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
