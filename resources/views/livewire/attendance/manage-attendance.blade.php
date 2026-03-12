<div
    class="space-y-6"
    id="attendance-offline-root"
    data-user-id="{{ (int) auth()->id() }}"
    data-school-id="{{ (int) (auth()->user()?->school_id ?? 0) }}"
>
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

    <div class="rounded-lg border border-cyan-200 bg-cyan-50 px-4 py-4 text-sm text-cyan-950">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="font-semibold">Offline draft protection</p>
                <p class="mt-1 text-cyan-900">
                    This page keeps the current attendance sheet on this device. If the internet drops, continue marking the open sheet and sync it when the connection returns.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span
                    id="attendance-draft-status"
                    class="hidden rounded-full border border-cyan-300 bg-white px-3 py-1 text-xs font-semibold text-cyan-900"
                ></span>
                <button
                    type="button"
                    id="attendance-restore-draft"
                    class="hidden rounded-lg border border-cyan-300 bg-white px-3 py-2 text-xs font-semibold text-cyan-900 hover:bg-cyan-100"
                >
                    Restore Draft
                </button>
                <button
                    type="button"
                    id="attendance-clear-draft"
                    class="hidden rounded-lg border border-rose-300 bg-white px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50"
                >
                    Clear Draft
                </button>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-slate-900">Daily Attendance</h2>
        <p class="mt-1 text-sm text-slate-600">Record and update student attendance by date, class, and section.</p>

        @if ($isRestrictedTeacherAttendanceManager)
            <p class="mt-3 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-900">
                Only classes where you are assigned as class teacher are available here.
            </p>
        @endif

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Date</label>
                <input
                    id="attendance-date"
                    type="date"
                    wire:model.live="attendanceDate"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                />
                @error('attendanceDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Class</label>
                <select id="attendance-class" wire:model.live="selectedClassId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select class</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class['id'] }}">{{ $class['name'] }}</option>
                    @endforeach
                </select>
                @error('selectedClassId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Section</label>
                <select id="attendance-section" wire:model.live="selectedSectionId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All Sections</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                    @endforeach
                </select>
                @error('selectedSectionId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Session Notes</label>
                <input
                    id="attendance-notes"
                    type="text"
                    wire:model.defer="sessionNotes"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                    placeholder="Optional note"
                />
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
                                <select
                                    wire:model.defer="statuses.{{ $studentRecordId }}"
                                    data-attendance-status="{{ $studentRecordId }}"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                >
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                    <option value="excused">Excused</option>
                                </select>
                                @error('statuses.' . $studentRecordId) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </td>
                            <td class="px-4 py-3">
                                <input
                                    type="text"
                                    wire:model.defer="remarks.{{ $studentRecordId }}"
                                    data-attendance-remark="{{ $studentRecordId }}"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                    placeholder="Optional remark"
                                />
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
            <button
                type="button"
                id="attendance-save-button"
                wire:click="saveAttendance"
                class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"
            >
                <i class="fas fa-save mr-2"></i>
                {{ $loadedSessionId ? 'Update Attendance' : 'Save Attendance' }}
            </button>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        (() => {
            if (window.__attendanceOfflineDraftBooted) {
                return;
            }

            window.__attendanceOfflineDraftBooted = true;

            const ROOT_ID = 'attendance-offline-root';
            const STORAGE_PREFIX = 'elites:attendance:draft:v1';
            const FIELD_SELECTOR = '#attendance-date, #attendance-class, #attendance-section, #attendance-notes, [data-attendance-status], [data-attendance-remark]';

            const getRoot = () => document.getElementById(ROOT_ID);

            const getStorageKey = (root) => {
                if (!root) {
                    return null;
                }

                return [
                    STORAGE_PREFIX,
                    root.dataset.schoolId || '0',
                    root.dataset.userId || '0',
                ].join(':');
            };

            const loadDraft = (root) => {
                const storageKey = getStorageKey(root);

                if (!storageKey) {
                    return null;
                }

                try {
                    const rawDraft = window.localStorage.getItem(storageKey);

                    return rawDraft ? JSON.parse(rawDraft) : null;
                } catch (error) {
                    return null;
                }
            };

            const formatTimestamp = (value) => {
                if (!value) {
                    return '';
                }

                try {
                    return new Date(value).toLocaleString();
                } catch (error) {
                    return '';
                }
            };

            const dispatchFieldUpdate = (element, value) => {
                if (!element || value === undefined || value === null) {
                    return;
                }

                element.value = value;
                element.dispatchEvent(new Event('input', { bubbles: true }));
                element.dispatchEvent(new Event('change', { bubbles: true }));
            };

            const collectDraftState = (root) => {
                const dateInput = root.querySelector('#attendance-date');
                const classInput = root.querySelector('#attendance-class');
                const sectionInput = root.querySelector('#attendance-section');
                const notesInput = root.querySelector('#attendance-notes');

                return {
                    attendanceDate: dateInput?.value || '',
                    selectedClassId: classInput?.value || '',
                    selectedSectionId: sectionInput?.value || '',
                    sessionNotes: notesInput?.value || '',
                    statuses: Object.fromEntries(
                        Array.from(root.querySelectorAll('[data-attendance-status]')).map((field) => [
                            field.dataset.attendanceStatus,
                            field.value,
                        ])
                    ),
                    remarks: Object.fromEntries(
                        Array.from(root.querySelectorAll('[data-attendance-remark]')).map((field) => [
                            field.dataset.attendanceRemark,
                            field.value,
                        ])
                    ),
                };
            };

            const persistDraft = (root) => {
                const storageKey = getStorageKey(root);

                if (!storageKey) {
                    return;
                }

                const state = collectDraftState(root);

                try {
                    window.localStorage.setItem(storageKey, JSON.stringify({
                        savedAt: new Date().toISOString(),
                        state,
                    }));
                } catch (error) {
                    return;
                }

                refreshDraftUi();
            };

            const clearDraft = (root, statusMessage = 'Local draft cleared.') => {
                const storageKey = getStorageKey(root);

                if (!storageKey) {
                    return;
                }

                window.localStorage.removeItem(storageKey);
                refreshDraftUi(statusMessage);
            };

            const applyStudentFields = (root, draftState, attempt = 0) => {
                const statusFields = Array.from(root.querySelectorAll('[data-attendance-status]'));
                const draftStatusKeys = Object.keys(draftState.statuses || {});

                if (draftStatusKeys.length > 0 && statusFields.length === 0 && attempt < 10) {
                    window.setTimeout(() => applyStudentFields(root, draftState, attempt + 1), 250);
                    return;
                }

                statusFields.forEach((field) => {
                    const value = draftState.statuses?.[field.dataset.attendanceStatus];

                    if (value !== undefined) {
                        dispatchFieldUpdate(field, value);
                    }
                });

                Array.from(root.querySelectorAll('[data-attendance-remark]')).forEach((field) => {
                    const value = draftState.remarks?.[field.dataset.attendanceRemark];

                    if (value !== undefined) {
                        dispatchFieldUpdate(field, value);
                    }
                });
            };

            const restoreDraft = (root, options = {}) => {
                const draft = loadDraft(root);

                if (!draft?.state) {
                    refreshDraftUi('No draft found on this device.');
                    return;
                }

                const state = draft.state;
                const classField = root.querySelector('#attendance-class');
                const currentClass = classField?.value || '';
                const draftClass = state.selectedClassId || '';

                dispatchFieldUpdate(root.querySelector('#attendance-date'), state.attendanceDate || '');
                dispatchFieldUpdate(root.querySelector('#attendance-notes'), state.sessionNotes || '');

                if (!navigator.onLine && currentClass !== '' && draftClass !== '' && currentClass !== draftClass) {
                    refreshDraftUi('Draft belongs to another class. Reconnect before restoring that sheet.');
                    return;
                }

                dispatchFieldUpdate(classField, draftClass);

                window.setTimeout(() => {
                    dispatchFieldUpdate(root.querySelector('#attendance-section'), state.selectedSectionId || '');
                }, 250);

                window.setTimeout(() => {
                    dispatchFieldUpdate(root.querySelector('#attendance-notes'), state.sessionNotes || '');
                    applyStudentFields(root, state);
                }, 700);

                if (!options.silent) {
                    refreshDraftUi('Draft restored to the current sheet.');
                }
            };

            const refreshDraftUi = (statusMessage = '') => {
                const root = getRoot();

                if (!root) {
                    return;
                }

                const draft = loadDraft(root);
                const statusChip = root.querySelector('#attendance-draft-status');
                const restoreButton = root.querySelector('#attendance-restore-draft');
                const clearButton = root.querySelector('#attendance-clear-draft');

                if (!statusChip || !restoreButton || !clearButton) {
                    return;
                }

                if (!draft?.state) {
                    statusChip.classList.remove('hidden');
                    statusChip.textContent = statusMessage || (navigator.onLine ? 'No local draft yet.' : 'Offline. No local draft yet.');
                    restoreButton.classList.add('hidden');
                    clearButton.classList.add('hidden');
                    return;
                }

                const savedLabel = formatTimestamp(draft.savedAt);
                const defaultMessage = navigator.onLine
                    ? `Draft saved locally${savedLabel ? ` at ${savedLabel}` : ''}.`
                    : `Offline draft active${savedLabel ? ` from ${savedLabel}` : ''}.`;

                statusChip.classList.remove('hidden');
                statusChip.textContent = statusMessage || defaultMessage;
                restoreButton.classList.remove('hidden');
                clearButton.classList.remove('hidden');

                if (!navigator.onLine && root.dataset.autoDraftRestored !== '1') {
                    root.dataset.autoDraftRestored = '1';
                    restoreDraft(root, { silent: true });
                }
            };

            const debounce = (callback, delay) => {
                let timeoutId = null;

                return (...args) => {
                    if (timeoutId !== null) {
                        window.clearTimeout(timeoutId);
                    }

                    timeoutId = window.setTimeout(() => callback(...args), delay);
                };
            };

            const debouncedPersist = debounce(() => {
                const root = getRoot();

                if (root) {
                    persistDraft(root);
                }
            }, 200);

            document.addEventListener('input', (event) => {
                if (!event.target.closest(`#${ROOT_ID}`) || !event.target.matches(FIELD_SELECTOR)) {
                    return;
                }

                debouncedPersist();
            }, true);

            document.addEventListener('change', (event) => {
                if (!event.target.closest(`#${ROOT_ID}`) || !event.target.matches(FIELD_SELECTOR)) {
                    return;
                }

                debouncedPersist();
            }, true);

            document.addEventListener('click', (event) => {
                const root = getRoot();

                if (!root) {
                    return;
                }

                const restoreButton = event.target.closest('#attendance-restore-draft');
                const clearButton = event.target.closest('#attendance-clear-draft');

                if (restoreButton) {
                    restoreDraft(root);
                }

                if (clearButton) {
                    clearDraft(root);
                }
            }, true);

            document.addEventListener('DOMContentLoaded', () => refreshDraftUi());
            document.addEventListener('livewire:navigated', () => refreshDraftUi());
            window.addEventListener('online', () => refreshDraftUi('Internet restored. Review the sheet and press Save Attendance to sync.'));
            window.addEventListener('offline', () => refreshDraftUi('Offline mode. Changes on this sheet are being kept on this device.'));

            document.addEventListener('livewire:init', () => {
                if (!window.Livewire || window.__attendanceOfflineDraftLivewireHooked) {
                    return;
                }

                window.__attendanceOfflineDraftLivewireHooked = true;

                window.Livewire.on('attendance-saved', () => {
                    const root = getRoot();

                    if (!root) {
                        return;
                    }

                    root.dataset.autoDraftRestored = '0';
                    clearDraft(root, 'Attendance synced. Local draft cleared.');
                });
            }, { once: true });
        })();
    </script>
@endpush
