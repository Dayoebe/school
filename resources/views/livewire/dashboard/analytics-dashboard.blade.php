@php
    $currency = 'NGN';
    $formatMoney = static fn ($value) => number_format(((int) $value) / 100, 2);
@endphp

<div class="space-y-6">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-bold text-slate-900">Analytics Dashboard</h2>
        <p class="mt-1 text-sm text-slate-600">Admissions, inquiries, fee collection, and engagement performance.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Admissions</p>
            <p class="mt-2 text-2xl font-black text-blue-900">{{ number_format($admissions['total']) }}</p>
            <p class="text-xs text-blue-800">Pending: {{ number_format($admissions['pending']) }} | Enrolled: {{ number_format($admissions['enrolled']) }}</p>
            <p class="mt-1 text-xs font-semibold text-blue-700">Conversion: {{ $admissions['conversion_rate'] }}%</p>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Inquiries</p>
            <p class="mt-2 text-2xl font-black text-emerald-900">{{ number_format($inquiries['total']) }}</p>
            <p class="text-xs text-emerald-800">New: {{ number_format($inquiries['new']) }} | Resolved: {{ number_format($inquiries['resolved']) }}</p>
            <p class="mt-1 text-xs font-semibold text-emerald-700">Response: {{ $inquiries['response_rate'] }}%</p>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Fee Collection</p>
            <p class="mt-2 text-xl font-black text-amber-900">{{ $currency }} {{ $formatMoney($fees['paid']) }}</p>
            <p class="text-xs text-amber-800">Billable: {{ $currency }} {{ $formatMoney($fees['billable']) }}</p>
            <p class="text-xs font-semibold text-amber-700">Outstanding: {{ $currency }} {{ $formatMoney($fees['outstanding']) }}</p>
        </div>
        <div class="rounded-lg border border-violet-200 bg-violet-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-violet-700">Engagement</p>
            <p class="mt-2 text-2xl font-black text-violet-900">{{ number_format($engagement['broadcasts_total']) }}</p>
            <p class="text-xs text-violet-800">Active Notices: {{ number_format($engagement['active_notices']) }} | Portal Reach: {{ number_format($engagement['portal_reach']) }}</p>
            <p class="mt-1 text-xs font-semibold text-violet-700">Gallery: {{ number_format($engagement['gallery_items']) }} | Media: {{ number_format($engagement['media_assets']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Admissions Trend</h3>
            <div class="mt-4 space-y-3">
                @foreach ($monthKeys as $key)
                    @php($value = (int) ($admissions['monthly'][$key] ?? 0))
                    @php($width = (int) round(($value / $admissionsMax) * 100))
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs font-semibold text-slate-600">
                            <span>{{ $monthLabels[$loop->index] }}</span>
                            <span>{{ number_format($value) }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-blue-500" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Inquiry Trend</h3>
            <div class="mt-4 space-y-3">
                @foreach ($monthKeys as $key)
                    @php($value = (int) ($inquiries['monthly'][$key] ?? 0))
                    @php($width = (int) round(($value / $inquiriesMax) * 100))
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs font-semibold text-slate-600">
                            <span>{{ $monthLabels[$loop->index] }}</span>
                            <span>{{ number_format($value) }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-emerald-500" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Monthly Fee Collection</h3>
            <div class="mt-4 space-y-3">
                @foreach ($monthKeys as $key)
                    @php($value = (int) ($fees['monthly_collected'][$key] ?? 0))
                    @php($width = (int) round(($value / $feeMax) * 100))
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs font-semibold text-slate-600">
                            <span>{{ $monthLabels[$loop->index] }}</span>
                            <span>{{ $currency }} {{ $formatMoney($value) }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-amber-500" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Broadcast Engagement Trend</h3>
            <div class="mt-4 space-y-3">
                @foreach ($monthKeys as $key)
                    @php($value = (int) ($engagement['monthly_broadcasts'][$key] ?? 0))
                    @php($width = (int) round(($value / $engagementMax) * 100))
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs font-semibold text-slate-600">
                            <span>{{ $monthLabels[$loop->index] }}</span>
                            <span>{{ number_format($value) }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-violet-500" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
