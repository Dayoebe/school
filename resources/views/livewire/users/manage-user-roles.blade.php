<div class="space-y-6">
    @if (session()->has('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-bold text-slate-900">Users & Roles</h2>
        <p class="mt-1 text-sm text-slate-600">
            Assign roles from one place. Super-admin role elevation is restricted to current super-admin accounts.
        </p>

        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Name, email, or phone"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                />
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Role Filter</label>
                <select
                    wire:model.live="roleFilter"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                >
                    <option value="all">All Roles</option>
                    @foreach ($roleOptions as $roleKey => $roleLabel)
                        <option value="{{ $roleKey }}">{{ $roleLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">School</label>
                <select
                    wire:model.live="schoolFilter"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    @disabled(!$canManageAcrossSchools)
                >
                    <option value="all">All Schools</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}">{{ $school->name }} ({{ $school->code }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[920px] text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">School</th>
                        <th class="px-4 py-3">Current Role</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Set Role</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        @php
                            $currentRoles = $user->roles
                                ->pluck('name')
                                ->map(fn ($name) => $name === 'super_admin' ? 'super-admin' : $name)
                                ->unique()
                                ->values();
                        @endphp
                        <tr class="align-top">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                                <p class="text-xs text-slate-600">{{ $user->email }}</p>
                                @if ($user->phone)
                                    <p class="text-xs text-slate-500">{{ $user->phone }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-slate-700">{{ $user->school?->name ?? 'N/A' }}</p>
                                @if ($user->school?->code)
                                    <p class="text-xs text-slate-500">{{ $user->school->code }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($currentRoles as $roleName)
                                        <span @class([
                                            'rounded-full px-2 py-1 text-[11px] font-semibold uppercase',
                                            'bg-blue-100 text-blue-700' => $roleName === 'super-admin',
                                            'bg-slate-100 text-slate-700' => $roleName !== 'super-admin',
                                        ])>
                                            {{ str_replace('_', ' ', $roleName) }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400">No role</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($user->locked)
                                    <span class="rounded-full bg-red-100 px-2 py-1 text-[11px] font-semibold uppercase text-red-700">
                                        Locked
                                    </span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-semibold uppercase text-emerald-700">
                                        Active
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <select
                                    wire:model.defer="selectedRoles.{{ $user->id }}"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >
                                    <option value="">Select role</option>
                                    @foreach ($roleOptions as $roleKey => $roleLabel)
                                        <option value="{{ $roleKey }}">{{ $roleLabel }}</option>
                                    @endforeach
                                </select>
                                @error('selectedRoles.' . $user->id)
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    type="button"
                                    wire:click="updateUserRole({{ $user->id }})"
                                    class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700"
                                >
                                    <i class="fas fa-user-shield mr-2"></i>
                                    Update Role
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                                No users found for the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

