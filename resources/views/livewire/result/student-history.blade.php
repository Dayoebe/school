<div class="space-y-6">
    @if(!$viewingHistory)
        @if($canBrowseAllStudents)
            <div class="rounded-[1.75rem] border border-orange-200 bg-orange-50 p-6 shadow-sm">
                <h3 class="mb-4 flex items-center text-xl font-semibold text-slate-900">
                    <i class="fas fa-search mr-3 text-orange-600"></i>
                    Select Student
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
                    Assigned Class History
                </h3>
                <p class="text-sm leading-6 text-slate-600">
                    You can only view academic history for students in your class-teacher assignment.
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
                    <i class="fas fa-history mr-3 text-rose-600"></i>
                    {{ $isStudentResultViewer ? 'My Academic History' : 'My Children\'s Academic History' }}
                </h3>
                <p class="text-sm leading-6 text-slate-600">
                    {{ $isStudentResultViewer ? 'You can only view your own academic history.' : 'You can only view academic history for students linked to your parent account.' }}
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
                                    Class
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-[0.24em] text-white">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach($students as $student)
                                @if($student->user)
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
                                        <td class="px-6 py-4 text-center text-sm text-slate-700">
                                            {{ $student->myClass->name }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button wire:click="viewHistory({{ $student->id }})"
                                                class="ml-auto flex items-center rounded-xl bg-violet-600 px-4 py-2 text-white shadow-sm transition-colors hover:bg-violet-700">
                                                <i class="fas fa-history mr-2"></i> View History
                                            </button>
                                        </td>
                                    </tr>
                                @endif
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
                    {{ $canBrowseAllStudents ? 'No students with results in this class' : ($isRestrictedTeacherResultViewer ? 'No student in your assigned class matched your search.' : ($isStudentResultViewer ? 'No academic history is available for your account yet.' : 'No linked child history matched your search.')) }}
                </p>
            </div>
        @endif

    @else
        <div class="space-y-6">
            <div class="rounded-[1.75rem] bg-slate-900 p-6 text-white shadow-xl">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        @if(!$isStudentResultViewer)
                            <button wire:click="backToList"
                                class="mb-4 flex items-center text-sm font-medium text-amber-200 transition-colors hover:text-white">
                                <i class="fas fa-arrow-left mr-2"></i> Back to List
                            </button>
                        @endif

                        <div class="flex items-center">
                            <img src="{{ $studentRecord->user->profile_photo_url }}"
                                alt="{{ $studentRecord->user->name }}"
                                class="h-20 w-20 rounded-full border-4 border-white/20 object-cover shadow-xl">
                            <div class="ml-5">
                                <h1 class="text-2xl font-bold md:text-3xl">{{ $studentRecord->user->name }}</h1>
                                <p class="mt-1 text-sky-200">{{ $studentRecord->myClass->name }} • {{ $studentRecord->admission_number }}</p>
                            </div>
                        </div>
                    </div>

                    <button onclick="window.print()"
                        class="inline-flex items-center rounded-xl bg-amber-500 px-6 py-3 font-medium text-slate-950 shadow-sm transition-colors hover:bg-amber-400">
                        <i class="fas fa-print mr-2"></i> Print History
                    </button>
                </div>
            </div>

            @if(!empty($overallStats))
                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    <div class="rounded-2xl bg-blue-500 p-6 text-white shadow-xl">
                        <p class="text-sm font-medium text-blue-100">Overall Average</p>
                        <p class="mt-2 text-4xl font-bold">{{ $overallStats['average_score'] }}%</p>
                    </div>

                    <div class="rounded-2xl bg-emerald-500 p-6 text-white shadow-xl">
                        <p class="text-sm font-medium text-emerald-100">Best Subject</p>
                        <p class="mt-2 text-xl font-bold">{{ $overallStats['best_subject'] }}</p>
                        <p class="mt-1 text-sm text-emerald-100">{{ $overallStats['best_subject_avg'] }}% avg</p>
                    </div>

                    <div class="rounded-2xl bg-rose-500 p-6 text-white shadow-xl">
                        <p class="text-sm font-medium text-rose-100">Needs Improvement</p>
                        <p class="mt-2 text-xl font-bold">{{ $overallStats['worst_subject'] }}</p>
                        <p class="mt-1 text-sm text-rose-100">{{ $overallStats['worst_subject_avg'] }}% avg</p>
                    </div>

                    <div class="rounded-2xl bg-violet-500 p-6 text-white shadow-xl">
                        <p class="text-sm font-medium text-violet-100">Terms Completed</p>
                        <p class="mt-2 text-4xl font-bold">{{ $overallStats['total_terms'] }}</p>
                    </div>
                </div>
            @endif

            <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-xl">
                <div class="bg-indigo-600 px-6 py-4">
                    <h3 class="flex items-center text-xl font-bold text-white">
                        <i class="fas fa-history mr-2"></i>
                        Academic Performance Timeline
                    </h3>
                </div>

                <div class="divide-y divide-slate-200">
                    @foreach($historyData as $yearData)
                        <div x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }" class="p-6">
                            <button @click="expanded = !expanded"
                                class="group flex w-full items-center justify-between">
                                <div class="flex items-center">
                                    <div class="mr-4 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-800">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="text-left">
                                        <h4 class="text-lg font-bold text-slate-800 transition-colors group-hover:text-indigo-700">
                                            {{ $yearData['year']->name }}
                                        </h4>
                                        <p class="text-sm text-slate-500">{{ count($yearData['semesters']) }} terms</p>
                                    </div>
                                </div>
                                <i :class="expanded ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"
                                    class="text-slate-500 transition-colors group-hover:text-indigo-600"></i>
                            </button>

                            <div x-show="expanded" x-collapse class="mt-6 space-y-4">
                                @foreach($yearData['semesters'] as $semesterData)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                                        <div class="mb-4 flex items-start justify-between">
                                            <div>
                                                <h5 class="font-semibold text-slate-800">{{ $semesterData['semester']->name }}</h5>
                                                <p class="text-sm text-slate-600">{{ $semesterData['subjects_count'] }} subjects • {{ $semesterData['percentage'] }}% average</p>
                                            </div>
                                            <span class="rounded-full px-3 py-1 text-sm font-bold
                                                {{ $semesterData['percentage'] >= 75 ? 'bg-emerald-100 text-emerald-800' :
                                                   ($semesterData['percentage'] >= 60 ? 'bg-blue-100 text-blue-800' :
                                                   ($semesterData['percentage'] >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')) }}">
                                                {{ $semesterData['percentage'] }}%
                                            </span>
                                        </div>

                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-sm">
                                                <thead class="bg-slate-100">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left font-medium text-slate-700">Subject</th>
                                                        <th class="px-4 py-2 text-center font-medium text-slate-700">Score</th>
                                                        <th class="px-4 py-2 text-center font-medium text-slate-700">Grade</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-200">
                                                    @foreach($semesterData['results'] as $result)
                                                        <tr class="hover:bg-white">
                                                            <td class="px-4 py-2 font-medium text-slate-900">{{ $result->subject->name }}</td>
                                                            <td class="px-4 py-2 text-center font-bold text-indigo-700">{{ $result->total_score }}</td>
                                                            <td class="px-4 py-2 text-center">
                                                                @php
                                                                    $grade = match(true) {
                                                                        $result->total_score >= 75 => 'A1',
                                                                        $result->total_score >= 70 => 'B2',
                                                                        $result->total_score >= 65 => 'B3',
                                                                        $result->total_score >= 60 => 'C4',
                                                                        $result->total_score >= 55 => 'C5',
                                                                        $result->total_score >= 50 => 'C6',
                                                                        $result->total_score >= 45 => 'D7',
                                                                        $result->total_score >= 40 => 'E8',
                                                                        default => 'F9',
                                                                    };
                                                                    $gradeClass = match($grade[0]) {
                                                                        'A' => 'bg-emerald-100 text-emerald-800',
                                                                        'B' => 'bg-blue-100 text-blue-800',
                                                                        'C' => 'bg-amber-100 text-amber-800',
                                                                        default => 'bg-red-100 text-red-800',
                                                                    };
                                                                @endphp
                                                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $gradeClass }}">
                                                                    {{ $grade }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    @media print {
        .no-print, button { display: none !important; }
    }
</style>
@endpush
