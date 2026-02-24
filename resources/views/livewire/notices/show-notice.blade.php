@php
    $today = now()->toDateString();
    $isActive = $notice->active && $notice->start_date <= $today && $notice->stop_date >= $today;
    $isUpcoming = $notice->start_date > $today;
    $isExpired = $notice->stop_date < $today;
@endphp

<div class="space-y-4">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h3 class="text-xl font-bold text-slate-900">{{ $notice->title }}</h3>
                <p class="mt-1 text-sm text-slate-600">
                    Active window:
                    {{ \Illuminate\Support\Carbon::parse($notice->start_date)->format('M d, Y') }}
                    to
                    {{ \Illuminate\Support\Carbon::parse($notice->stop_date)->format('M d, Y') }}
                </p>
            </div>

            <div>
                @if ($isActive)
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase text-emerald-700">Active</span>
                @elseif ($isUpcoming)
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold uppercase text-blue-700">Upcoming</span>
                @elseif ($isExpired)
                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold uppercase text-slate-700">Expired</span>
                @else
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase text-amber-700">Inactive</span>
                @endif
            </div>
        </div>

        <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-4">
            <p class="whitespace-pre-line text-sm leading-7 text-slate-800">{{ $notice->content }}</p>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Attachment</h4>
        @if ($notice->attachment)
            <a
                href="{{ asset('storage/' . $notice->attachment) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-3 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
            >
                <i class="fas fa-download mr-2"></i>Open or Download Attachment
            </a>
        @else
            <p class="mt-2 text-sm text-slate-500">No file attached to this notice.</p>
        @endif
    </div>
</div>

