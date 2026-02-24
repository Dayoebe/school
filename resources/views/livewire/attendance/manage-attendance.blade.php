<div class="space-y-6">
    @if (session()->has('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (!$hasAcademicYear)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Set an active academic year first before recording attendance.
        </div>
    @endif

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-slate-900">Daily Attendance</h2>
        <p class="mt-1 text-sm text-slate-600">Record and update student attendance by date, class, and section.</p>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Date</label>
                <input type="date" wire:model.live="attendanceDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                @error('attendanceDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Class</label>
                <select wire:model.live="selectedClassId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select class</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class['id'] }}">{{ $class['name'] }}</option>
                    @endforeach
                </select>
                @error('selectedClassId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Section</label>
                <select wire:model.live="selectedSectionId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All Sections</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                    @endforeach
                </select>
                @error('selectedSectionId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Session Notes</label>
                <input type="text" wire:model.defer="sessionNotes" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Optional note" />
                @error('sessionNotes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Present</p>
            <p class="mt-1 text-2xl font-bold text-emerald-900">{{ $summary['present'] }}</p>
        </div>
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-700">Absent</p>
            <p class="mt-1 text-2xl font-bold text-red-900">{{ $summary['absent'] }}</p>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Late</p>
            <p class="mt-1 text-2xl font-bold text-amber-900">{{ $summary['late'] }}</p>
        </div>
        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Excused</p>
            <p class="mt-1 text-2xl font-bold text-blue-900">{{ $summary['excused'] }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Admission No</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Remark</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($students as $student)
                        @php($studentRecordId = $student['student_record_id'])
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $student['name'] }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $student['admission_number'] ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <select wire:model.defer="statuses.{{ $studentRecordId }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                    <option value="excused">Excused</option>
                                </select>
                                @error('statuses.' . $studentRecordId) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" wire:model.defer="remarks.{{ $studentRecordId }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Optional remark" />
                                @error('remarks.' . $studentRecordId) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">No students found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($canEditAttendance)
        <div class="flex justify-end">
            <button type="button" wire:click="saveAttendance" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                <i class="fas fa-save mr-2"></i>
                {{ $loadedSessionId ? 'Update Attendance' : 'Save Attendance' }}
            </button>
        </div>
    @endif
</div>
