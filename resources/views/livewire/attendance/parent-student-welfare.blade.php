<div class="space-y-6">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-slate-900">Child Attendance & Discipline</h2>
        <p class="mt-1 text-sm text-slate-600">Track attendance trends and discipline reports shared by the school.</p>

        @if ($children === [])
            <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                No linked students found for this parent account.
            </p>
        @else
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Student</label>
                    <select wire:model.live="selectedStudentRecordId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($children as $child)
                            <option value="{{ $child['student_record_id'] }}">
                                {{ $child['name'] }} {{ $child['admission_number'] ? '(' . $child['admission_number'] . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Attendance Month</label>
                    <input type="month" wire:model.live="month" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
            </div>
        @endif
    </div>

    @if ($selectedChild)
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Present</p>
                <p class="mt-1 text-2xl font-bold text-emerald-900">{{ $attendanceSummary['present'] }}</p>
            </div>
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-red-700">Absent</p>
                <p class="mt-1 text-2xl font-bold text-red-900">{{ $attendanceSummary['absent'] }}</p>
            </div>
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Late</p>
                <p class="mt-1 text-2xl font-bold text-amber-900">{{ $attendanceSummary['late'] }}</p>
            </div>
            <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Excused</p>
                <p class="mt-1 text-2xl font-bold text-blue-900">{{ $attendanceSummary['excused'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h3 class="text-lg font-semibold text-slate-900">Attendance Records</h3>
                </div>
                <div class="max-h-[460px] overflow-y-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Class</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($attendance as $record)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $record->attendanceSession?->attendance_date?->format('M d, Y') ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-1 text-xs font-semibold uppercase
                                            {{ $record->status === 'present' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                            {{ $record->status === 'absent' ? 'bg-red-100 text-red-700' : '' }}
                                            {{ $record->status === 'late' ? 'bg-amber-100 text-amber-700' : '' }}
                                            {{ $record->status === 'excused' ? 'bg-blue-100 text-blue-700' : '' }}">
                                            {{ $record->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-700">
                                        {{ $record->attendanceSession?->myClass?->name ?? '-' }}
                                        @if ($record->attendanceSession?->section)
                                            <span class="text-xs text-slate-500">({{ $record->attendanceSession->section->name }})</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">No attendance records for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h3 class="text-lg font-semibold text-slate-900">Discipline Reports</h3>
                    <p class="text-xs text-slate-500">Only parent-visible incidents are shown.</p>
                </div>
                <div class="max-h-[460px] overflow-y-auto">
                    <div class="space-y-3 p-4">
                        @forelse ($discipline as $incident)
                            <article class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="text-sm font-semibold text-slate-900">{{ $incident->category }}</h4>
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[11px] font-semibold uppercase text-slate-700">{{ $incident->severity }}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">{{ $incident->incident_date?->format('M d, Y') }}</p>
                                <p class="mt-2 text-sm text-slate-700">{{ $incident->description }}</p>
                                @if ($incident->action_taken)
                                    <p class="mt-2 text-xs text-slate-600"><strong>Action:</strong> {{ $incident->action_taken }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="rounded-lg border border-slate-200 bg-white px-4 py-8 text-center text-sm text-slate-500">
                                No discipline incidents shared for this child.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
