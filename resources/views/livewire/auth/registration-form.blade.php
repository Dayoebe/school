@isset($roles)
    <form action="{{ route('register') }}" method="POST" class="w-full space-y-4" x-data="{ showPassword: false, showPasswordConfirmation: false }">
        @csrf

        <div>
            <label for="role" class="mb-2 block text-sm font-bold text-slate-700">Register As</label>
            <select
                id="role"
                name="role"
                required
                @class([
                    'w-full rounded-xl border px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:outline-none focus:ring-4',
                    'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('role'),
                    'border-slate-300 focus:border-red-400 focus:ring-red-100' => !$errors->has('role'),
                ])
            >
                <option value="">Select role</option>
                @foreach ($roles as $item)
                    <option value="{{ $item['name'] }}" @selected(old('role') === $item['name']) class="capitalize">
                        {{ $item['name'] }}
                    </option>
                @endforeach
            </select>
            @error('role')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="school" class="mb-2 block text-sm font-bold text-slate-700">School</label>
            <select
                id="school"
                name="school"
                @class([
                    'w-full rounded-xl border px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:outline-none focus:ring-4',
                    'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('school'),
                    'border-slate-300 focus:border-red-400 focus:ring-red-100' => !$errors->has('school'),
                ])
            >
                <option value="">Select school</option>
                @foreach ($schools as $item)
                    <option value="{{ $item['id'] }}" @selected((string) old('school') === (string) $item['id'])>
                        {{ $item['name'] }} - {{ $item['address'] }}
                    </option>
                @endforeach
            </select>
            @error('school')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="name" class="mb-2 block text-sm font-bold text-slate-700">Full Name</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                required
                autocomplete="name"
                @class([
                    'w-full rounded-xl border px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:outline-none focus:ring-4',
                    'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('name'),
                    'border-slate-300 focus:border-red-400 focus:ring-red-100' => !$errors->has('name'),
                ])
                placeholder="Enter your full name"
            >
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="mb-2 block text-sm font-bold text-slate-700">Email Address</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                @class([
                    'w-full rounded-xl border px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:outline-none focus:ring-4',
                    'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('email'),
                    'border-slate-300 focus:border-red-400 focus:ring-red-100' => !$errors->has('email'),
                ])
                placeholder="you@example.com"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-bold text-slate-700">Password</label>
            <div class="relative">
                <input
                    :type="showPassword ? 'text' : 'password'"
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    @class([
                        'w-full rounded-xl border px-4 py-3 pr-12 text-sm text-slate-900 shadow-sm transition focus:outline-none focus:ring-4',
                        'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('password'),
                        'border-slate-300 focus:border-red-400 focus:ring-red-100' => !$errors->has('password'),
                    ])
                    placeholder="Create a password"
                >
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 px-4 text-slate-500 transition hover:text-slate-700"
                    aria-label="Toggle password visibility"
                >
                    <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-bold text-slate-700">Confirm Password</label>
            <div class="relative">
                <input
                    :type="showPasswordConfirmation ? 'text' : 'password'"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    @class([
                        'w-full rounded-xl border px-4 py-3 pr-12 text-sm text-slate-900 shadow-sm transition focus:outline-none focus:ring-4',
                        'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('password_confirmation'),
                        'border-slate-300 focus:border-red-400 focus:ring-red-100' => !$errors->has('password_confirmation'),
                    ])
                    placeholder="Confirm your password"
                >
                <button
                    type="button"
                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                    class="absolute inset-y-0 right-0 px-4 text-slate-500 transition hover:text-slate-700"
                    aria-label="Toggle password confirmation visibility"
                >
                    <i class="fas" :class="showPasswordConfirmation ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            @error('password_confirmation')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="site-primary-bg w-full rounded-xl px-5 py-3 text-sm font-bold text-white transition hover:opacity-90">
            Register
        </button>
    </form>
@else
   <p>Couldn't create user, Roles not found.</p> 
@endisset
