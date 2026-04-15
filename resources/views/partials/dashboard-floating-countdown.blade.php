@php
    $countdownUser = auth()->user();
    $showDashboardCountdown = $countdownUser && $countdownUser->hasAnyRole(['super-admin', 'super_admin']);
@endphp

@if ($showDashboardCountdown)
@php
    $countdownNow = now();
    $countdownTarget = $countdownNow->copy()->setDate($countdownNow->year, 6, 30)->setTime(23, 59, 59);

    if ($countdownNow->greaterThan($countdownTarget)) {
        $countdownTarget->addYear();
    }

    $countdownUnits = [
        'months' => [
            'label' => 'Months',
            'card' => 'border-rose-300 bg-rose-100 text-rose-950',
            'label_text' => 'text-rose-700',
        ],
        'weeks' => [
            'label' => 'Weeks',
            'card' => 'border-orange-300 bg-orange-100 text-orange-950',
            'label_text' => 'text-orange-700',
        ],
        'days' => [
            'label' => 'Days',
            'card' => 'border-amber-300 bg-amber-100 text-amber-950',
            'label_text' => 'text-amber-700',
        ],
        'hours' => [
            'label' => 'Hours',
            'card' => 'border-emerald-300 bg-emerald-100 text-emerald-950',
            'label_text' => 'text-emerald-700',
        ],
        'minutes' => [
            'label' => 'Minutes',
            'card' => 'border-sky-300 bg-sky-100 text-sky-950',
            'label_text' => 'text-sky-700',
        ],
        'seconds' => [
            'label' => 'Seconds',
            'card' => 'border-violet-300 bg-violet-100 text-violet-950',
            'label_text' => 'text-violet-700',
        ],
    ];
@endphp

<div class="pointer-events-none fixed bottom-4 right-4 z-[9999] sm:bottom-6 sm:right-6">
    <div class="pointer-events-auto relative w-[min(24rem,calc(100vw-1.5rem))] overflow-hidden rounded-[2rem] border-4 border-slate-900 bg-white text-slate-900 shadow-[0_30px_70px_-22px_rgba(15,23,42,0.45)]"
        x-data="{
            targetIso: @js($countdownTarget->toIso8601String()),
            open: true,
            interval: null,
            isComplete: false,
            totals: { months: 0, weeks: 0, days: 0, hours: 0, minutes: 0, seconds: 0 },
            init() {
                this.tick();
                this.interval = window.setInterval(() => this.tick(), 1000);
            },
            tick() {
                const now = new Date();
                const target = new Date(this.targetIso);
                const diffMs = target.getTime() - now.getTime();
        
                if (diffMs <= 0) {
                    this.isComplete = true;
                    this.totals = { months: 0, weeks: 0, days: 0, hours: 0, minutes: 0, seconds: 0 };
        
                    if (this.interval) {
                        window.clearInterval(this.interval);
                    }
        
                    return;
                }
        
                const months = this.monthDiff(now, target);
                const monthAnchor = new Date(now.getTime());
                monthAnchor.setMonth(monthAnchor.getMonth() + months);
        
                let remainingMs = Math.max(target.getTime() - monthAnchor.getTime(), 0);
        
                const weeks = Math.floor(remainingMs / (7 * 24 * 60 * 60 * 1000));
                remainingMs -= weeks * 7 * 24 * 60 * 60 * 1000;
        
                const days = Math.floor(remainingMs / (24 * 60 * 60 * 1000));
                remainingMs -= days * 24 * 60 * 60 * 1000;
        
                const hours = Math.floor(remainingMs / (60 * 60 * 1000));
                remainingMs -= hours * 60 * 60 * 1000;
        
                const minutes = Math.floor(remainingMs / (60 * 1000));
                remainingMs -= minutes * 60 * 1000;
        
                const seconds = Math.floor(remainingMs / 1000);
        
                this.isComplete = false;
                this.totals = {
                    months,
                    weeks,
                    days,
                    hours,
                    minutes,
                    seconds,
                };
            },
            monthDiff(fromDate, toDate) {
                let months = ((toDate.getFullYear() - fromDate.getFullYear()) * 12) + (toDate.getMonth() - fromDate.getMonth());
                const anchor = new Date(fromDate.getTime());
        
                anchor.setMonth(anchor.getMonth() + months);
        
                if (anchor > toDate) {
                    months -= 1;
                }
        
                return Math.max(months, 0);
            },
            format(value) {
                return new Intl.NumberFormat().format(value);
            },
        }" x-show="open" x-transition.opacity.scale.origin.bottom.right>
        <div class="absolute -left-5 top-20 h-14 w-14 rounded-full bg-sky-300 opacity-90"></div>
        <div class="absolute right-4 top-16 h-9 w-9 rounded-full bg-teal-300 opacity-95"></div>
        <div class="absolute -bottom-6 right-8 h-20 w-20 rounded-full bg-emerald-200 opacity-90"></div>

        <div class="relative border-b-4 border-slate-900 bg-amber-300 px-4 py-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="mt-3 text-xl font-black text-slate-950">Website shutting down</h3>
                    <p class="mt-2 max-w-xs text-xs leading-5 text-slate-800">
                        Countdown to {{ $countdownTarget->format('F j, Y') }}
                    </p>
                </div>

                <button type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border-2 border-slate-900 bg-slate-900 text-white transition hover:bg-white hover:text-slate-900"
                    @click="open = false" aria-label="Hide countdown">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>

        <div class="relative bg-stone-50 p-4">

            <div class="mt-4 grid grid-cols-3 gap-2.5">
                @foreach ($countdownUnits as $unitKey => $unit)
                    <div
                        class="rounded-[1.35rem] border-2 border-slate-900 px-3 py-3 shadow-[4px_4px_0_0_rgba(15,23,42,0.15)] {{ $unit['card'] }}">
                        <p class="text-[10px] font-bold uppercase tracking-[0.22em] {{ $unit['label_text'] }}">
                            {{ $unit['label'] }}</p>
                        <p class="mt-2 text-xl font-black leading-none" x-text="format(totals.{{ $unitKey }})"></p>
                    </div>
                @endforeach
            </div>

            <p class="mt-4 rounded-[1.2rem] border-2 border-slate-900 bg-emerald-300 px-4 py-3 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-900"
                x-show="isComplete">
                The countdown has reached June 30.
            </p>
        </div>
    </div>
</div>
@endif
