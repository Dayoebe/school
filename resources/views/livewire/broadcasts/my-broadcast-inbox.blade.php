<div class="space-y-6">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Portal Notices</h2>
                <p class="mt-1 text-sm text-slate-600">Messages sent to your account through the school broadcast system.</p>
            </div>
            <div class="w-full md:w-80">
                <input type="text" wire:model.live.debounce.300ms="search" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Search notices" />
            </div>
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($messages as $entry)
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ $entry->broadcastMessage?->title ?? 'Untitled Broadcast' }}</h3>
                        <p class="text-xs text-slate-500">
                            {{ $entry->portal_delivered_at?->toDayDateTimeString() }}
                            @if ($entry->broadcastMessage?->createdBy)
                                • by {{ $entry->broadcastMessage->createdBy->name }}
                            @endif
                        </p>
                    </div>
                    <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">Portal</span>
                </div>
                <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $entry->broadcastMessage?->body }}</p>
            </article>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-10 text-center text-sm text-slate-500">
                No portal notices found.
            </div>
        @endforelse
    </div>

    @if ($messages->hasPages())
        <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
            {{ $messages->links() }}
        </div>
    @endif
</div>
