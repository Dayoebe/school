@extends('layouts.app')

@section('title', 'My Profile')
@section('page_heading', 'My Profile')

@section('content')
    @php
        $roleLabels = $user->roles
            ->pluck('name')
            ->map(fn ($name) => $name === 'super_admin' ? 'super-admin' : $name)
            ->unique()
            ->map(fn ($name) => ucwords(str_replace(['-', '_'], ' ', $name)));
    @endphp

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <img
                        src="{{ $user->profile_photo_url }}"
                        alt="{{ $user->name }}"
                        class="h-16 w-16 rounded-full border border-slate-200 object-cover"
                    />
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                        <p class="text-sm text-slate-600">{{ $user->email }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse ($roleLabels as $roleLabel)
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold uppercase text-slate-700">
                                    {{ $roleLabel }}
                                </span>
                            @empty
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold uppercase text-slate-500">
                                    No role
                                </span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs text-slate-600 sm:grid-cols-4">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                        <p class="font-semibold uppercase tracking-wide text-slate-500">School</p>
                        <p class="mt-1 text-slate-800">{{ $user->school?->name ?? 'N/A' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                        <p class="font-semibold uppercase tracking-wide text-slate-500">School Code</p>
                        <p class="mt-1 text-slate-800">{{ $user->school?->code ?? 'N/A' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                        <p class="font-semibold uppercase tracking-wide text-slate-500">Email Status</p>
                        <p class="mt-1 text-slate-800">{{ $user->email_verified_at ? 'Verified' : 'Unverified' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                        <p class="font-semibold uppercase tracking-wide text-slate-500">Account Since</p>
                        <p class="mt-1 text-slate-800">{{ $user->created_at?->format('M d, Y') ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900">Profile Information</h3>
            <p class="mt-1 text-sm text-slate-600">Update your personal and contact details.</p>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1 block text-sm font-semibold text-slate-700">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="mb-1 block text-sm font-semibold text-slate-700">Phone</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="gender" class="mb-1 block text-sm font-semibold text-slate-700">Gender</label>
                        <select id="gender" name="gender"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <option value="">Select</option>
                            <option value="male" @selected(old('gender', $user->gender) === 'male')>Male</option>
                            <option value="female" @selected(old('gender', $user->gender) === 'female')>Female</option>
                        </select>
                        @error('gender') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="birthday" class="mb-1 block text-sm font-semibold text-slate-700">Birthday</label>
                        <input
                            id="birthday"
                            name="birthday"
                            type="date"
                            value="{{ old('birthday', optional($user->birthday)->format('Y-m-d')) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        />
                        @error('birthday') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="blood_group" class="mb-1 block text-sm font-semibold text-slate-700">Blood Group</label>
                        <input id="blood_group" name="blood_group" type="text" value="{{ old('blood_group', $user->blood_group) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                        @error('blood_group') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="state" class="mb-1 block text-sm font-semibold text-slate-700">State</label>
                        <input id="state" name="state" type="text" value="{{ old('state', $user->state) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                        @error('state') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="city" class="mb-1 block text-sm font-semibold text-slate-700">City</label>
                        <input id="city" name="city" type="text" value="{{ old('city', $user->city) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                        @error('city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="nationality" class="mb-1 block text-sm font-semibold text-slate-700">Nationality</label>
                        <input id="nationality" name="nationality" type="text" value="{{ old('nationality', $user->nationality) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                        @error('nationality') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="religion" class="mb-1 block text-sm font-semibold text-slate-700">Religion</label>
                        <input id="religion" name="religion" type="text" value="{{ old('religion', $user->religion) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                        @error('religion') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="mb-1 block text-sm font-semibold text-slate-700">Address</label>
                        <textarea id="address" name="address" rows="3"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">{{ old('address', $user->address) }}</textarea>
                        @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="profile_photo" class="mb-1 block text-sm font-semibold text-slate-700">Profile Photo</label>
                        <input id="profile_photo" name="profile_photo" type="file" accept="image/*"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                        <p class="mt-1 text-xs text-slate-500">Accepted image files up to 3MB.</p>
                        @error('profile_photo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Save Profile
                    </button>
                </div>
            </form>
        </section>

        <section id="password" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900">Security</h3>
            <p class="mt-1 text-sm text-slate-600">Use a strong password and update it regularly.</p>

            <form action="{{ route('password.change.update') }}" method="POST" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                @csrf

                <div class="md:col-span-2">
                    <label for="current_password" class="mb-1 block text-sm font-semibold text-slate-700">Current Password</label>
                    <input id="current_password" name="current_password" type="password"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                    @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-semibold text-slate-700">New Password</label>
                    <input id="password" name="password" type="password"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-slate-700">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                </div>

                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">
                        <i class="fas fa-lock mr-2"></i>Change Password
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection

