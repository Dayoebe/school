<div class="space-y-6">
    @if (!$contactTableReady)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
            Contact messages table is missing. Run `php artisan migrate` and refresh this page.
        </div>
    @endif

    @if (session()->has('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
        <button wire:click="$set('statusFilter','all')" class="rounded-xl border p-4 text-left transition {{ $statusFilter === 'all' ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">All</p>
            <p class="mt-1 text-2xl font-black text-slate-900">{{ $statusCounts['all'] }}</p>
        </button>
        <button wire:click="$set('statusFilter','new')" class="rounded-xl border p-4 text-left transition {{ $statusFilter === 'new' ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">New</p>
            <p class="mt-1 text-2xl font-black text-amber-700">{{ $statusCounts['new'] }}</p>
        </button>
        <button wire:click="$set('statusFilter','read')" class="rounded-xl border p-4 text-left transition {{ $statusFilter === 'read' ? 'border-blue-300 bg-blue-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Read</p>
            <p class="mt-1 text-2xl font-black text-blue-700">{{ $statusCounts['read'] }}</p>
        </button>
        <button wire:click="$set('statusFilter','in_progress')" class="rounded-xl border p-4 text-left transition {{ $statusFilter === 'in_progress' ? 'border-indigo-300 bg-indigo-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">In Progress</p>
            <p class="mt-1 text-2xl font-black text-indigo-700">{{ $statusCounts['in_progress'] }}</p>
        </button>
        <button wire:click="$set('statusFilter','resolved')" class="rounded-xl border p-4 text-left transition {{ $statusFilter === 'resolved' ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Resolved</p>
            <p class="mt-1 text-2xl font-black text-emerald-700">{{ $statusCounts['resolved'] }}</p>
        </button>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Search</label>
        <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="Name, email, phone, subject..."
            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Sender</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Subject</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">School</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Sent</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($messages as $message)
                        <tr>
                            <td class="px-4 py-3 align-top">
                                <p class="text-sm font-semibold text-slate-900">{{ $message->full_name }}</p>
                                <p class="text-xs text-slate-500">{{ $message->email }}</p>
                                <p class="text-xs text-slate-500">{{ $message->phone ?: 'No phone' }}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <p class="text-sm text-slate-900">{{ $message->subject }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($message->message, 90) }}</p>
                            </td>
                            <td class="px-4 py-3 align-top text-sm text-slate-800">{{ $message->school?->name }}</td>
                            <td class="px-4 py-3 align-top">
                                @php
                                    $statusClass = match($message->status) {
                                        'new' => 'bg-amber-100 text-amber-800',
                                        'read' => 'bg-blue-100 text-blue-800',
                                        'in_progress' => 'bg-indigo-100 text-indigo-800',
                                        'resolved' => 'bg-emerald-100 text-emerald-800',
                                        default => 'bg-slate-100 text-slate-800',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($message->status)) }}</span>
                            </td>
                            <td class="px-4 py-3 align-top text-xs text-slate-600">
                                {{ $message->created_at->format('M d, Y') }}<br>
                                {{ $message->created_at->format('h:i A') }}
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-wrap gap-2">
                                    <button wire:click="viewMessage({{ $message->id }})" class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">View</button>
                                    <button wire:click="markStatus({{ $message->id }}, 'read')" class="rounded-lg bg-blue-100 px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-200">Read</button>
                                    <button wire:click="markStatus({{ $message->id }}, 'in_progress')" class="rounded-lg bg-indigo-100 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-200">In Progress</button>
                                    <button wire:click="markStatus({{ $message->id }}, 'resolved')" class="rounded-lg bg-emerald-100 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-200">Resolve</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">No contact messages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 bg-white px-4 py-3">
            {{ $messages->links() }}
        </div>
    </div>

    @if($selectedMessage)
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-lg font-black text-slate-900">Contact Message Details</h3>
                <button wire:click="clearSelected" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">Close</button>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Sender</p>
                    <p class="mt-2 text-sm text-slate-800"><strong>Name:</strong> {{ $selectedMessage->full_name }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Email:</strong> {{ $selectedMessage->email }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Phone:</strong> {{ $selectedMessage->phone ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>School:</strong> {{ $selectedMessage->school?->name ?: 'N/A' }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Message</p>
                    <p class="mt-2 text-sm text-slate-800"><strong>Subject:</strong> {{ $selectedMessage->subject }}</p>
                    <p class="mt-2 whitespace-pre-line text-sm text-slate-800">{{ $selectedMessage->message }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Processing</p>
                    <p class="mt-2 text-sm text-slate-800"><strong>Status:</strong> {{ str_replace('_', ' ', ucfirst($selectedMessage->status)) }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Handled By:</strong> {{ $selectedMessage->handledBy?->name ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Read At:</strong> {{ $selectedMessage->read_at?->format('M d, Y h:i A') ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Resolved At:</strong> {{ $selectedMessage->resolved_at?->format('M d, Y h:i A') ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Last Response By:</strong> {{ $selectedMessage->responseBy?->name ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Last Response At:</strong> {{ $selectedMessage->response_sent_at?->format('M d, Y h:i A') ?: 'N/A' }}</p>
                </div>
            </div>

            @if(!$replyColumnsReady)
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800">
                    Reply fields are missing in the database. Run the latest contact migration to enable dashboard replies.
                </div>
            @else
                <div class="mt-5 rounded-xl border border-slate-200 bg-white p-4">
                    <h4 class="text-sm font-black uppercase tracking-wider text-slate-700">Reply To Sender</h4>

                    <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Status After Reply</label>
                            <select wire:model="responseStatus"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                                <option value="read">Read</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                            </select>
                            @error('responseStatus') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Response Note</label>
                            <textarea wire:model="responseNote" rows="5" placeholder="Write your response to the sender..."
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"></textarea>
                            @error('responseNote') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end">
                        <button wire:click="sendReply({{ $selectedMessage->id }})"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-700"
                            wire:loading.attr="disabled">
                            <i class="fas fa-paper-plane" wire:loading.remove wire:target="sendReply"></i>
                            <i class="fas fa-spinner fa-spin" wire:loading wire:target="sendReply"></i>
                            <span wire:loading.remove wire:target="sendReply">Send Reply</span>
                            <span wire:loading wire:target="sendReply">Sending...</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
