<div class="space-y-6">
    @if (session()->has('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($canCreateIncident)
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900">Log New Incident</h2>
            <p class="mt-1 text-sm text-slate-600">Record behavior or discipline events and control parent visibility.</p>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Class</label>
                    <select wire:model.live="formClassId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select class</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class['id'] }}">{{ $class['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Section</label>
                    <select wire:model.live="formSectionId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">All Sections</option>
                        @foreach ($formSections as $section)
                            <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Student</label>
                    <select wire:model="studentRecordId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select student</option>
                        @foreach ($formStudents as $student)
                            <option value="{{ $student['student_record_id'] }}">
                                {{ $student['name'] }} {{ $student['admission_number'] ? '(' . $student['admission_number'] . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('studentRecordId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Incident Date</label>
                    <input type="date" wire:model="incidentDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    @error('incidentDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                    <input type="text" wire:model="category" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="e.g. Late coming" />
                    @error('category') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Severity</label>
                    <select wire:model="severity" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                    @error('severity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                    <textarea rows="3" wire:model="description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Describe the incident"></textarea>
                    @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Action Taken (optional)</label>
                    <textarea rows="3" wire:model="actionTaken" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Any immediate action"></textarea>
                    @error('actionTaken') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model="parentVisible" class="rounded border-slate-300 text-indigo-600" />
                    Visible to parent in portal
                </label>
                <button type="button" wire:click="createIncident" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                    <i class="fas fa-plus mr-2"></i>Log Incident
                </button>
            </div>
        </div>
    @endif

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Student, category, description" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Severity</label>
                <select wire:model.live="severityFilter" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="all">All</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Class</label>
                <select wire:model.live="filterClassId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All Classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class['id'] }}">{{ $class['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Section</label>
                <select wire:model.live="filterSectionId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All Sections</option>
                    @foreach ($filterSections as $section)
                        <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Severity</th>
                        <th class="px-4 py-3">Parent</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($incidents as $incident)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $incident->incident_date?->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $incident->studentRecord?->user?->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $incident->category }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded-full px-2 py-1 text-xs font-semibold uppercase',
                                    'bg-slate-100 text-slate-700' => $incident->severity === 'low',
                                    'bg-amber-100 text-amber-700' => $incident->severity === 'medium',
                                    'bg-orange-100 text-orange-700' => $incident->severity === 'high',
                                    'bg-red-100 text-red-700' => $incident->severity === 'critical',
                                ])>{{ $incident->severity }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $incident->parent_visible ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $incident->parent_visible ? 'Visible' : 'Hidden' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $incident->resolved_at ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $incident->resolved_at ? 'Resolved' : 'Open' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($canUpdateIncident)
                                    <div class="flex items-center gap-2">
                                        <button type="button" wire:click="markResolved({{ $incident->id }})" class="rounded bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-200">
                                            {{ $incident->resolved_at ? 'Reopen' : 'Resolve' }}
                                        </button>
                                        <button type="button" wire:click="toggleParentVisibility({{ $incident->id }})" class="rounded bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200">
                                            Toggle Parent
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="bg-slate-50/40">
                            <td colspan="7" class="px-4 py-3 text-sm text-slate-700">
                                <strong>Description:</strong> {{ $incident->description }}
                                @if ($incident->action_taken)
                                    <br>
                                    <strong>Action:</strong> {{ $incident->action_taken }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">No discipline incidents found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($incidents->hasPages())
            <div class="mt-4 border-t border-slate-200 pt-4">
                {{ $incidents->links() }}
            </div>
        @endif
    </div>
</div>
