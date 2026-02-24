<div class="space-y-6">
    @if (session()->has('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($smsInfoMessage)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            {{ $smsInfoMessage }}
        </div>
    @endif

    @if ($canCreateBroadcast)
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900">Compose Broadcast</h2>
            <p class="mt-1 text-sm text-slate-600">Send notices by school, class, or role using portal, email, and SMS channels.</p>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                    <input type="text" wire:model="title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Broadcast title" />
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Message Body</label>
                    <textarea rows="4" wire:model="body" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Write your notice here"></textarea>
                    @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Target Type</label>
                    <select wire:model.live="targetType" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="school">Entire School</option>
                        <option value="class">Class Group</option>
                        <option value="role">Role</option>
                    </select>
                    @error('targetType') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                @if ($targetType === 'class')
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Class</label>
                        <select wire:model.live="targetClassId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Select class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class['id'] }}">{{ $class['name'] }}</option>
                            @endforeach
                        </select>
                        @error('targetClassId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Section (optional)</label>
                        <select wire:model="targetSectionId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">All Sections</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                            @endforeach
                        </select>
                        @error('targetSectionId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                @if ($targetType === 'role')
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Role</label>
                        <select wire:model="targetRole" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach (['principal', 'admin', 'teacher', 'student', 'parent'] as $role)
                                <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                        @error('targetRole') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>

            <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-800">Delivery Channels</p>
                <div class="mt-3 flex flex-wrap items-center gap-5 text-sm text-slate-700">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" wire:model="sendPortal" class="rounded border-slate-300 text-indigo-600" />
                        Portal Notice
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" wire:model="sendEmail" class="rounded border-slate-300 text-indigo-600" />
                        Email
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" wire:model="sendSms" class="rounded border-slate-300 text-indigo-600" />
                        SMS
                    </label>
                </div>
                @error('sendPortal') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="mt-5 flex justify-end">
                <button type="button" wire:click="sendBroadcast" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                    <i class="fas fa-paper-plane mr-2"></i>Send Broadcast
                </button>
            </div>
        </div>
    @endif

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Broadcast History</h3>
                <p class="text-sm text-slate-600">Recent messages and delivery metrics.</p>
            </div>
            <div class="w-full md:w-80">
                <input type="text" wire:model.live.debounce.300ms="search" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Search title or message" />
            </div>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Message</th>
                        <th class="px-4 py-3">Target</th>
                        <th class="px-4 py-3">Channels</th>
                        <th class="px-4 py-3">Recipients</th>
                        <th class="px-4 py-3">Sent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($messages as $message)
                        @php
                            $meta = $message->target_meta ?? [];
                            $targetText = 'School-wide';
                            if ($message->target_type === 'role') {
                                $targetText = 'Role: ' . ucfirst((string) data_get($meta, 'role', '')); 
                            } elseif ($message->target_type === 'class') {
                                $targetText = 'Class ID: ' . data_get($meta, 'class_id', '-');
                                if (data_get($meta, 'section_id')) {
                                    $targetText .= ' / Section ID: ' . data_get($meta, 'section_id');
                                }
                            }
                        @endphp
                        <tr>
                            <td class="px-4 py-3 align-top">
                                <p class="text-sm font-semibold text-slate-900">{{ $message->title }}</p>
                                <p class="mt-1 line-clamp-3 text-xs text-slate-600">{{ $message->body }}</p>
                                <p class="mt-1 text-[11px] text-slate-500">By {{ $message->createdBy?->name ?? 'System' }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $targetText }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">
                                <div class="space-y-1 text-xs">
                                    <p>Portal: <strong>{{ $message->send_portal ? 'Yes' : 'No' }}</strong></p>
                                    <p>Email: <strong>{{ $message->send_email ? 'Yes' : 'No' }}</strong></p>
                                    <p>SMS: <strong>{{ $message->send_sms ? 'Yes' : 'No' }}</strong></p>
                                    @if ($message->send_sms)
                                        <p>Status: <strong>{{ $message->sms_status ?? 'pending' }}</strong></p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">
                                <div class="space-y-1 text-xs">
                                    <p>Total: <strong>{{ $message->recipients_count }}</strong></p>
                                    <p>Portal: <strong>{{ $message->portal_recipient_count }}</strong></p>
                                    <p>Email: <strong>{{ $message->email_recipient_count }}</strong></p>
                                    <p>SMS: <strong>{{ $message->sms_recipient_count }}</strong></p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $message->sent_at?->toDayDateTimeString() ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">No broadcast messages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($messages->hasPages())
            <div class="mt-4 border-t border-slate-200 pt-4">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</div>
