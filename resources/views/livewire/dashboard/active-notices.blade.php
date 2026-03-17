<div>
    @if ($notices->isNotEmpty())
        <section class="rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 via-orange-50 to-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Notice Board</p>
                    <h2 class="mt-1 text-2xl font-bold text-slate-900">School Notices</h2>
                    <p class="mt-2 text-sm text-slate-700">
                        Check out the latest notices and announcements for your school. Stay informed about important updates, events, and news that affect you.
                    </p>
                </div>

                @if ($canManageNotices)
                    <a
                        href="{{ route('notices.index') }}"
                        class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800"
                        wire:navigate
                    >
                        <i class="fas fa-bullhorn mr-2"></i>Manage Notices
                    </a>
                @endif
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 xl:grid-cols-2">
                @foreach ($notices as $notice)
                    <article class="rounded-xl border border-amber-200 bg-white/90 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-slate-900">{{ $notice->title }}</h3>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-amber-700">
                                    {{ \Illuminate\Support\Carbon::parse($notice->start_date)->format('M d, Y') }}
                                    to
                                    {{ \Illuminate\Support\Carbon::parse($notice->stop_date)->format('M d, Y') }}
                                </p>
                            </div>

                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold uppercase text-emerald-700">
                                Active
                            </span>
                        </div>

                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">
                            {{ \Illuminate\Support\Str::limit($notice->content, 280) }}
                        </p>

                        @if ($notice->attachment)
                            <div class="mt-4">
                                <a
                                    href="{{ asset('storage/' . $notice->attachment) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center text-sm font-semibold text-amber-700 transition hover:text-amber-800"
                                >
                                    <i class="fas fa-paperclip mr-2"></i>Open attachment
                                </a>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
