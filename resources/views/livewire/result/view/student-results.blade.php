<div class="space-y-6">
    <div class="rounded-[1.75rem] border border-amber-200 bg-amber-50 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-800">Result Period</p>
                <h3 class="mt-2 text-2xl font-bold text-slate-900">Academic period</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Select the academic year and term to view the relevant result.
                </p>
            </div>

            <div class="rounded-2xl bg-white p-3 shadow-sm">
                <livewire:result.academic-period-selector />
            </div>
        </div>
    </div>

    @if(!$viewingStudent)
        @if($canBrowseAllStudents)
            <div class="rounded-[1.75rem] border border-orange-200 bg-orange-50 p-6 shadow-sm">
                <h3 class="mb-4 flex items-center text-xl font-semibold text-slate-900">
                    <i class="fas fa-search mr-3 text-orange-600"></i>
                    Find Students
                </h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Class</label>
                        <select wire:model.live="selectedClass"
                            class="w-full rounded-xl border border-orange-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200">
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Section (Optional)</label>
                        <select wire:model.live="selectedSection"
                            class="w-full rounded-xl border border-orange-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200">
                            <option value="">All Sections</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Search Student</label>
                        <input type="text" wire:model.live.debounce.300ms="searchTerm"
                            class="w-full rounded-xl border border-orange-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200"
                            placeholder="Search by name...">
                    </div>
                </div>
            </div>
        @elseif($isRestrictedTeacherResultViewer)
            <div class="rounded-[1.75rem] border border-blue-200 bg-blue-50 p-6 shadow-sm">
                <h3 class="mb-2 flex items-center text-xl font-semibold text-slate-900">
                    <i class="fas fa-chalkboard-teacher mr-3 text-blue-600"></i>
                    Assigned Class Results
                </h3>
                <p class="text-sm leading-6 text-slate-600">
                    You can only view full student reports for students in your class-teacher assignment.
                </p>

                <div class="mt-4 max-w-xl">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Search Student</label>
                    <input type="text" wire:model.live.debounce.300ms="searchTerm"
                        class="w-full rounded-xl border border-blue-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-200"
                        placeholder="Search by name...">
                </div>
            </div>
        @else
            <div class="rounded-[1.75rem] border border-rose-200 bg-rose-50 p-6 shadow-sm">
                <h3 class="mb-2 flex items-center text-xl font-semibold text-slate-900">
                    <i class="fas {{ $isStudentResultViewer ? 'fa-user-graduate' : 'fa-user-shield' }} mr-3 text-rose-600"></i>
                    {{ $isStudentResultViewer ? 'My Result' : 'My Children\'s Results' }}
                </h3>
                <p class="text-sm leading-6 text-slate-600">
                    {{ $isStudentResultViewer ? 'You can only view your own result.' : 'You can only view results for students linked to your parent account.' }}
                </p>

                @if($isParentResultViewer)
                    <div class="mt-4 max-w-xl">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Search Child</label>
                        <input type="text" wire:model.live.debounce.300ms="searchTerm"
                            class="w-full rounded-xl border border-rose-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-rose-400 focus:ring-2 focus:ring-rose-200"
                            placeholder="Search by name...">
                    </div>
                @endif
            </div>
        @endif

        @if($students->isNotEmpty())
            <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-indigo-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-white">
                                    Student
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-[0.24em] text-white">
                                    Results Uploaded
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-[0.24em] text-white">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach($students as $student)
                                <tr class="transition-colors hover:bg-indigo-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img class="h-12 w-12 rounded-full border-2 border-indigo-200 object-cover"
                                                src="{{ $student->user->profile_photo_url }}"
                                                alt="{{ $student->user->name }}">
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-slate-900">{{ $student->user->name }}</div>
                                                <div class="text-sm text-slate-500">{{ $student->admission_number }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($student->results->isEmpty())
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                                No Results
                                            </span>
                                        @else
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                                {{ $student->results->count() }} Subjects
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <button wire:click="viewStudent({{ $student->id }})"
                                                class="flex items-center rounded-xl bg-blue-600 px-4 py-2 text-white shadow-sm transition-colors hover:bg-blue-700">
                                                <i class="fas fa-eye mr-2"></i> View
                                            </button>
                                            <a href="{{ route('result.print', [
                                                'student' => $student->id,
                                                'academicYearId' => $academicYearId,
                                                'semesterId' => $semesterId,
                                            ]) }}" target="_blank"
                                                class="flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-white shadow-sm transition-colors hover:bg-emerald-700">
                                                <i class="fas fa-print mr-2"></i> Print
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                    {{ $students->links() }}
                </div>
            </div>
        @elseif($canBrowseAllStudents ? $selectedClass : true)
            <div class="rounded-[1.75rem] border-2 border-dashed border-slate-300 bg-slate-50 p-12 text-center">
                <i class="fas fa-user-slash mb-4 text-5xl text-slate-300"></i>
                <h3 class="mb-2 text-xl font-semibold text-slate-700">No Students Found</h3>
                <p class="text-slate-500">
                    {{ $canBrowseAllStudents ? 'Try adjusting your search filters' : ($isRestrictedTeacherResultViewer ? 'No student in your assigned class matched your search.' : ($isStudentResultViewer ? 'No result record is available for your account yet.' : 'No linked child result matched your search.')) }}
                </p>
            </div>
        @endif

    @else
        <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-xl">
            <div class="bg-slate-900 px-8 py-6 text-white">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        @if(!$isStudentResultViewer)
                            <button wire:click="backToList"
                                class="mb-4 flex items-center text-sm font-medium text-amber-200 transition-colors hover:text-white">
                                <i class="fas fa-arrow-left mr-2"></i> Back to List
                            </button>
                        @endif

                        <div class="flex items-center gap-4">
                            <img src="{{ $studentRecord->user->profile_photo_url }}"
                                alt="{{ $studentRecord->user->name }}"
                                class="h-20 w-20 rounded-full border-4 border-white/20 object-cover shadow-md">
                            <div>
                                <h3 class="text-2xl font-bold">{{ $studentRecord->user->name }}</h3>
                                <p class="mt-1 text-lg text-sky-200">{{ $studentRecord->myClass->name ?? 'N/A' }}</p>
                                <p class="text-sm text-slate-300">Admission No: {{ $studentRecord->admission_number ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('result.print', [
                        'student' => $studentRecord->id,
                        'academicYearId' => $academicYearId,
                        'semesterId' => $semesterId,
                    ]) }}" target="_blank"
                        class="inline-flex items-center rounded-xl bg-emerald-600 px-6 py-3 font-medium text-white shadow-sm transition-colors hover:bg-emerald-700">
                        <i class="fas fa-print mr-2"></i> Print Report
                    </a>
                </div>
            </div>

            <div class="p-8">
                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-2xl bg-blue-500 p-4 text-white shadow-sm">
                        <p class="text-sm font-medium text-blue-100">Total Score</p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ collect($results)->sum('total_score') }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-violet-500 p-4 text-white shadow-sm">
                        <p class="text-sm font-medium text-violet-100">Average</p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ count($results) > 0 ? round(collect($results)->sum('total_score') / count($results), 1) : 0 }}%
                        </p>
                    </div>
                    <div class="rounded-2xl bg-emerald-500 p-4 text-white shadow-sm">
                        <p class="text-sm font-medium text-emerald-100">Position</p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ $studentPosition }}/{{ $totalStudents }}
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-700">Subject</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-700">CA1</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-700">CA2</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-700">CA3</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-700">CA4</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-700">Exam</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-700">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-700">Grade</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-700">Comment</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach($subjects as $subject)
                                @php
                                    $result = $results[$subject->id] ?? null;
                                @endphp
                                @if($result)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 font-medium text-slate-900">{{ $subject->name }}</td>
                                        <td class="px-4 py-4 text-center text-slate-700">{{ $result['ca1_score'] ?? '-' }}</td>
                                        <td class="px-4 py-4 text-center text-slate-700">{{ $result['ca2_score'] ?? '-' }}</td>
                                        <td class="px-4 py-4 text-center text-slate-700">{{ $result['ca3_score'] ?? '-' }}</td>
                                        <td class="px-4 py-4 text-center text-slate-700">{{ $result['ca4_score'] ?? '-' }}</td>
                                        <td class="px-4 py-4 text-center text-slate-700">{{ $result['exam_score'] ?? '-' }}</td>
                                        <td class="px-4 py-4 text-center font-bold text-blue-700">{{ $result['total_score'] }}</td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="rounded-full px-2 py-1 text-xs font-semibold
                                                {{ $result['grade'][0] == 'A' ? 'bg-emerald-100 text-emerald-800' :
                                                   ($result['grade'][0] == 'B' ? 'bg-blue-100 text-blue-800' :
                                                   ($result['grade'][0] == 'C' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')) }}">
                                                {{ $result['grade'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-700">{{ $result['comment'] ?? '-' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
