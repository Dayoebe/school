<div class="space-y-4">
    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search Notices</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by title or content"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                />
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Status Filter</label>
                <select
                    wire:model.live="statusFilter"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    @disabled(!$canManageNotices)
                >
                    <option value="all">All</option>
                    <option value="active">Active</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="expired">Expired</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Notice</th>
                        <th class="px-4 py-3">Active Window</th>
                        <th class="px-4 py-3">State</th>
                        <th class="px-4 py-3">Attachment</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($notices as $notice)
                        @php
                            $today = now()->toDateString();
                            $isActive = $notice->active && $notice->start_date <= $today && $notice->stop_date >= $today;
                            $isUpcoming = $notice->start_date > $today;
                            $isExpired = $notice->stop_date < $today;
                        @endphp
                        <tr class="align-top">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $notice->title }}</p>
                                <p class="mt-1 text-xs text-slate-600">{{ \Illuminate\Support\Str::limit($notice->content, 140) }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-slate-800">{{ \Illuminate\Support\Carbon::parse($notice->start_date)->format('M d, Y') }}</p>
                                <p class="text-xs text-slate-500">to {{ \Illuminate\Support\Carbon::parse($notice->stop_date)->format('M d, Y') }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if ($isActive)
                                    <span class="rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-semibold uppercase text-emerald-700">Active</span>
                                @elseif ($isUpcoming)
                                    <span class="rounded-full bg-blue-100 px-2 py-1 text-[11px] font-semibold uppercase text-blue-700">Upcoming</span>
                                @elseif ($isExpired)
                                    <span class="rounded-full bg-slate-200 px-2 py-1 text-[11px] font-semibold uppercase text-slate-700">Expired</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2 py-1 text-[11px] font-semibold uppercase text-amber-700">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($notice->attachment)
                                    <a href="{{ asset('storage/' . $notice->attachment) }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-blue-700 hover:text-blue-800">
                                        Open file
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">None</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a
                                        href="{{ route('notices.show', $notice) }}"
                                        class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700"
                                    >
                                        <i class="fas fa-eye mr-1.5"></i>View
                                    </a>
                                    @can('delete', $notice)
                                        <form method="POST" action="{{ route('notices.destroy', $notice) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                onclick="return confirm('Delete this notice?')"
                                                class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700"
                                            >
                                                <i class="fas fa-trash mr-1.5"></i>Delete
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">
                                No notices found for the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($notices->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $notices->links() }}
            </div>
        @endif
    </div>
</div>
