<div>
    @if ($notices->isNotEmpty())
        <section class="overflow-hidden rounded-[1.75rem] border-2 border-amber-300 bg-amber-50 shadow-[0_24px_80px_-32px_rgba(180,83,9,0.45)]">
            <div class="border-b-2 border-amber-200 bg-amber-200 px-5 py-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-600 text-white shadow-lg">
                            <i class="fas fa-bell text-xl"></i>
                            @if ($unreadNoticeCount > 0)
                                <span class="absolute -right-2 -top-2 inline-flex h-7 min-w-7 items-center justify-center rounded-full border-2 border-white bg-slate-950 px-2 text-xs font-black text-white">
                                    {{ $unreadNoticeCount }}
                                </span>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.28em] text-red-700">Notice Board</p>
                            <h2 class="mt-1 text-2xl font-black text-slate-950">Important School Notices</h2>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-700">
                                Active notices are shown here first so everyone visiting the dashboard can quickly see current school updates.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if ($unreadNoticeCount > 0)
                            <button
                                type="button"
                                wire:click="markAllAsRead"
                                class="inline-flex items-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-800"
                            >
                                <i class="fas fa-check-double mr-2"></i>Mark all read
                            </button>
                        @endif

                        @if ($canManageNotices)
                            <a
                                href="{{ route('notices.index') }}"
                                class="inline-flex items-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-800"
                                wire:navigate
                            >
                                <i class="fas fa-bullhorn mr-2"></i>Manage Notices
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 xl:grid-cols-2">
                @foreach ($notices as $notice)
                    @php($isUnread = (bool) $notice->getAttribute('is_unread'))
                    <article class="relative rounded-[1.35rem] border-2 p-4 shadow-sm {{ $isUnread ? 'border-red-300 bg-white' : 'border-amber-200 bg-white/80' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($isUnread)
                                        <span class="inline-flex items-center rounded-full bg-red-600 px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.18em] text-white">
                                            <i class="fas fa-circle mr-1 text-[7px]"></i>New
                                        </span>
                                    @endif

                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-800">
                                        Active
                                    </span>
                                </div>

                                <h3 class="mt-3 text-lg font-black text-slate-950">{{ $notice->title }}</h3>
                                <p class="mt-1 text-xs font-bold uppercase tracking-wide text-amber-700">
                                    {{ $notice->start_date?->format('M d, Y') }}
                                    to
                                    {{ $notice->stop_date?->format('M d, Y') }}
                                </p>
                                @if ($notice->creator)
                                    <p class="mt-1 text-xs text-slate-500">Posted by {{ $notice->creator->name }}</p>
                                @endif
                            </div>

                            <button
                                type="button"
                                wire:click="{{ $isUnread ? 'markAsRead' : 'markAsUnread' }}({{ $notice->id }})"
                                class="shrink-0 rounded-full border px-3 py-1.5 text-xs font-bold transition {{ $isUnread ? 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100' }}"
                            >
                                {{ $isUnread ? 'Mark read' : 'Mark unread' }}
                            </button>
                        </div>

                        <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700">
                            {{ \Illuminate\Support\Str::limit($notice->content, 320) }}
                        </p>

                        @if ($notice->attachment)
                            <div class="mt-4">
                                <a
                                    href="{{ asset('storage/' . $notice->attachment) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center rounded-lg bg-amber-100 px-3 py-2 text-sm font-bold text-amber-800 transition hover:bg-amber-200"
                                >
                                    <i class="fas fa-paperclip mr-2"></i>Open attachment
                                </a>
                            </div>
                        @endif

                        @can('update', $notice)
                            <div class="mt-4">
                                <a
                                    href="{{ route('notices.edit', $notice) }}"
                                    class="inline-flex items-center rounded-lg bg-slate-950 px-3 py-2 text-sm font-bold text-white transition hover:bg-slate-800"
                                    wire:navigate
                                >
                                    <i class="fas fa-pen mr-2"></i>Edit notice
                                </a>
                            </div>
                        @endcan
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
