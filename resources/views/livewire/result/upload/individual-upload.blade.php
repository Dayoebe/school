<div
    class="space-y-6"
    id="result-upload-offline-root"
    data-user-id="{{ (int) auth()->id() }}"
    data-school-id="{{ (int) (auth()->user()?->school_id ?? 0) }}"
    data-academic-year-id="{{ (int) ($academicYearId ?? 0) }}"
    data-semester-id="{{ (int) ($semesterId ?? 0) }}"
    data-selected-class-id="{{ (int) ($selectedClass ?? 0) }}"
    data-selected-student-id="{{ (int) ($selectedStudent ?? 0) }}"
    data-loaded-student-id="{{ (int) ($studentRecord?->id ?? 0) }}"
    data-loaded-class-id="{{ (int) ($loadedClassId ?? 0) }}"
>
    <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-cyan-950">Offline Draft Protection</h3>
                <p class="mt-2 max-w-3xl text-sm leading-7 text-cyan-900">
                    Load a student while online, then keep entering scores even if the internet drops. This page stores the open result sheet on this device so you can restore it and press Save All when the connection returns.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span
                    id="result-upload-draft-status"
                    class="hidden rounded-full border border-cyan-300 bg-white px-3 py-1 text-xs font-semibold text-cyan-900"
                ></span>
                <button
                    type="button"
                    id="result-upload-restore-draft"
                    class="hidden rounded-lg border border-cyan-300 bg-white px-3 py-2 text-xs font-semibold text-cyan-900 hover:bg-cyan-100"
                >
                    Restore Draft
                </button>
                <button
                    type="button"
                    id="result-upload-clear-draft"
                    class="hidden rounded-lg border border-rose-300 bg-white px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50"
                >
                    Clear Draft
                </button>
            </div>
        </div>
    </div>

    <!-- Student Selection -->
    <div class="bg-gradient-to-r from-gray-50 to-white rounded-2xl p-6 shadow-sm border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-user-plus mr-2 text-indigo-600"></i>
            Select Student
        </h3>

        @if($isRestrictedTeacherResultUploader)
            <p class="mb-4 text-sm text-gray-600">
                Only your assigned teaching classes are available. Subject scores remain limited to the subjects you teach, while class report fields are reserved for the class teacher.
            </p>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                <select id="result-upload-class" wire:model.live="selectedClass"
                    class="w-full border-2 border-black rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Section (Optional)</label>
                <select wire:model.live="selectedSection"
                    class="w-full border-2 border-black rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Sections</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </div> --}}

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                <select id="result-upload-student" wire:model.live="selectedStudent"
                    class="w-full border-2 border-black rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    @if(!$selectedClass) disabled @endif>
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                        @if($student->user)
                            <option value="{{ $student->id }}">
                                {{ $student->user->name }} ({{ $student->admission_number }})
                            </option>
                        @endif
                    @endforeach
                </select>
                
                @if($selectedClass && $students->isEmpty())
                    <p class="mt-2 text-sm text-amber-600">⚠️ No students found in this class for the selected academic period.</p>
                @endif

                @if($classes->isEmpty())
                    <p class="mt-2 text-sm text-amber-600">No uploadable classes are currently assigned to your account.</p>
                @endif

                @if($selectedClass && !$semesterId)
                    <p class="mt-2 text-sm text-red-600">No term is configured for this academic year. Set it in Result Term Settings first.</p>
                @endif
            </div>

            <div class="flex items-end">
                <button wire:click="loadStudent"
                    type="button"
                    @disabled(!$selectedStudent)
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-3 px-4 rounded-xl transition-all duration-300 shadow-lg flex items-center justify-center"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-75">
                    <span wire:loading.remove wire:target="loadStudent">
                        <i class="fas fa-arrow-right mr-2"></i>
                        @if($selectedStudent)
                            Load Student
                        @else
                            Select a Student
                        @endif
                    </span>
                    <span wire:loading wire:target="loadStudent">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Loading...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Results Entry Form -->
    @if($studentRecord)
        @php
            $hasAnyError = false;
        @endphp
        
        <div class="bg-white rounded-2xl shadow-xl p-8 space-y-6 border border-gray-200">
            <!-- Student Info Header -->
            <div class="flex items-center space-x-4 border-b border-gray-200 pb-6">
                <img src="{{ $studentRecord->user->profile_photo_url }}" 
                    alt="{{ $studentRecord->user->name }}"
                    class="h-20 w-20 rounded-full object-cover border-4 border-indigo-200 shadow-md">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $studentRecord->user->name }}</h3>
                    <p class="text-indigo-600 text-lg">{{ $studentRecord->myClass->name ?? 'N/A' }}</p>
                    <p class="text-gray-500 text-sm">Admission No: {{ $studentRecord->admission_number ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Results Table -->
            <div class="overflow-x-auto rounded-xl shadow-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-indigo-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                Subject
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider w-24">
                                CA1 (10)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider w-24">
                                CA2 (10)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider w-24">
                                CA3 (10)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider w-24">
                                CA4 (10)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider w-24">
                                Exam (60)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider w-24">
                                Total (100)
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                Comment
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($subjects as $subject)
                            @php
                                $ca1 = $results[$subject->id]['ca1_score'] ?? 0;
                                $ca2 = $results[$subject->id]['ca2_score'] ?? 0;
                                $ca3 = $results[$subject->id]['ca3_score'] ?? 0;
                                $ca4 = $results[$subject->id]['ca4_score'] ?? 0;
                                $exam = $results[$subject->id]['exam_score'] ?? 0;
                                
                                $ca1Invalid = $ca1 > 10;
                                $ca2Invalid = $ca2 > 10;
                                $ca3Invalid = $ca3 > 10;
                                $ca4Invalid = $ca4 > 10;
                                $examInvalid = $exam > 60;
                                $subjectHasError = $ca1Invalid || $ca2Invalid || $ca3Invalid || $ca4Invalid || $examInvalid;
                                
                                if($subjectHasError) $hasAnyError = true;
                            @endphp
                            
                            <tr class="hover:bg-gray-50 transition-colors @if($subjectHasError) bg-red-50 @endif">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $subject->name }}
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <div class="relative">
                                        <input type="number"
                                            wire:model.live.debounce.500ms="results.{{ $subject->id }}.ca1_score"
                                            data-result-subject-id="{{ $subject->id }}"
                                            data-result-subject-field="ca1_score"
                                            class="w-full border-2 @if($ca1Invalid) border-red-500 bg-red-50 text-red-700 @else border-black @endif rounded-lg text-center px-2 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                            min="0" max="10" step="1">
                                        @if($ca1Invalid)
                                            <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" title="Maximum is 10">!</div>
                                        @endif
                                    </div>
                                    @if($ca1Invalid)
                                        <div class="text-xs text-red-600 mt-1 font-medium">Max: 10</div>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <div class="relative">
                                        <input type="number"
                                            wire:model.live.debounce.500ms="results.{{ $subject->id }}.ca2_score"
                                            data-result-subject-id="{{ $subject->id }}"
                                            data-result-subject-field="ca2_score"
                                            class="w-full border-2 @if($ca2Invalid) border-red-500 bg-red-50 text-red-700 @else border-black @endif rounded-lg text-center px-2 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                            min="0" max="10" step="1">
                                        @if($ca2Invalid)
                                            <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" title="Maximum is 10">!</div>
                                        @endif
                                    </div>
                                    @if($ca2Invalid)
                                        <div class="text-xs text-red-600 mt-1 font-medium">Max: 10</div>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <div class="relative">
                                        <input type="number"
                                            wire:model.live.debounce.500ms="results.{{ $subject->id }}.ca3_score"
                                            data-result-subject-id="{{ $subject->id }}"
                                            data-result-subject-field="ca3_score"
                                            class="w-full border-2 @if($ca3Invalid) border-red-500 bg-red-50 text-red-700 @else border-black @endif rounded-lg text-center px-2 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                            min="0" max="10" step="1">
                                        @if($ca3Invalid)
                                            <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" title="Maximum is 10">!</div>
                                        @endif
                                    </div>
                                    @if($ca3Invalid)
                                        <div class="text-xs text-red-600 mt-1 font-medium">Max: 10</div>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <div class="relative">
                                        <input type="number"
                                            wire:model.live.debounce.500ms="results.{{ $subject->id }}.ca4_score"
                                            data-result-subject-id="{{ $subject->id }}"
                                            data-result-subject-field="ca4_score"
                                            class="w-full border-2 @if($ca4Invalid) border-red-500 bg-red-50 text-red-700 @else border-black @endif rounded-lg text-center px-2 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                            min="0" max="10" step="1">
                                        @if($ca4Invalid)
                                            <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" title="Maximum is 10">!</div>
                                        @endif
                                    </div>
                                    @if($ca4Invalid)
                                        <div class="text-xs text-red-600 mt-1 font-medium">Max: 10</div>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <div class="relative">
                                        <input type="number"
                                            wire:model.live.debounce.500ms="results.{{ $subject->id }}.exam_score"
                                            data-result-subject-id="{{ $subject->id }}"
                                            data-result-subject-field="exam_score"
                                            class="w-full border-2 @if($examInvalid) border-red-500 bg-red-50 text-red-700 @else border-black @endif rounded-lg text-center px-2 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                            min="0" max="60" step="1">
                                        @if($examInvalid)
                                            <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" title="Maximum is 60">!</div>
                                        @endif
                                    </div>
                                    @if($examInvalid)
                                        <div class="text-xs text-red-600 mt-1 font-medium">Max: 60</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center font-bold @if($subjectHasError) text-red-600 @else text-indigo-600 @endif">
                                    {{ 
                                        (int)($ca1 ?? 0) +
                                        (int)($ca2 ?? 0) +
                                        (int)($ca3 ?? 0) +
                                        (int)($ca4 ?? 0) +
                                        (int)($exam ?? 0)
                                    }}
                                    @if($subjectHasError)
                                        <div class="text-xs text-red-500 font-normal mt-1">Check scores</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text"
                                        wire:model.live.debounce.500ms="results.{{ $subject->id }}.comment"
                                        data-result-subject-id="{{ $subject->id }}"
                                        data-result-subject-field="comment"
                                        class="w-full border-2 border-black rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                        placeholder="Teacher's comment">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500 text-lg">
                                    <i class="fas fa-exclamation-circle mr-2"></i> No subjects found for this student
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($canManageClassTeacherReport || $canEditPrincipalComment)
                <!-- Attendance & Comments -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                    @if($canManageClassTeacherReport)
                        <div class="bg-gray-50 p-6 rounded-xl shadow-inner border border-gray-200">
                            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-clipboard-check mr-2 text-purple-600"></i> Attendance Record
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Days Present</label>
                                    <input type="number" id="result-upload-present-days" wire:model.live.debounce.500ms="presentDays"
                                        class="w-full border-2 border-black rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        min="0" placeholder="e.g., 90">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Days Absent</label>
                                    <input type="number" id="result-upload-absent-days" wire:model.live.debounce.500ms="absentDays"
                                        class="w-full border-2 border-black rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        min="0" placeholder="e.g., 5">
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="bg-gray-50 p-6 rounded-xl shadow-inner border border-gray-200">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-comment-dots mr-2 text-blue-600"></i> Overall Comments
                        </h3>
                        <div class="space-y-3">
                            @if($canManageClassTeacherReport)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Teacher's Comment</label>
                                    <textarea id="result-upload-overall-comment" wire:model.live.debounce.500ms="overallTeacherComment"
                                        class="w-full border-2 border-black rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 h-20 resize-y"
                                        placeholder="Overall teacher's comment..."></textarea>
                                </div>
                            @endif

                            @if($canEditPrincipalComment)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Principal's Comment</label>
                                    <textarea id="result-upload-principal-comment" wire:model.live.debounce.500ms="principalComment"
                                        class="w-full border-2 border-black rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 h-20 resize-y"
                                        placeholder="Principal's comment..."></textarea>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($canManageClassTeacherReport)
                    <!-- Traits & Activities (Condensed) -->
                    <div class="bg-gray-50 p-6 rounded-xl shadow-inner border border-gray-200">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-star mr-2 text-yellow-600"></i> Traits & Activities (1-5 Scale)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <h4 class="font-semibold text-blue-800 mb-3">PSYCHOMOTOR</h4>
                                @foreach($psychomotorScores as $trait => $value)
                                    @php
                                        $traitInvalid = $value > 5;
                                        if($traitInvalid) $hasAnyError = true;
                                    @endphp
                                    <div class="mb-3">
                                        <label class="block text-sm text-gray-700 mb-1">{{ $trait }}</label>
                                        <div class="relative">
                                            <input type="number" wire:model.live.debounce.500ms="psychomotorScores.{{ $trait }}"
                                                data-result-trait-group="psychomotor"
                                                data-result-trait-key="{{ $trait }}"
                                                class="w-full border-2 @if($traitInvalid) border-red-500 bg-red-50 text-red-700 @else border-black @endif rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                                min="1" max="5">
                                            @if($traitInvalid)
                                                <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" title="Maximum is 5">!</div>
                                            @endif
                                        </div>
                                        @if($traitInvalid)
                                            <div class="text-xs text-red-600 mt-1 font-medium">Max: 5</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div>
                                <h4 class="font-semibold text-blue-800 mb-3">AFFECTIVE</h4>
                                @foreach($affectiveScores as $trait => $value)
                                    @php
                                        $traitInvalid = $value > 5;
                                        if($traitInvalid) $hasAnyError = true;
                                    @endphp
                                    <div class="mb-3">
                                        <label class="block text-sm text-gray-700 mb-1">{{ $trait }}</label>
                                        <div class="relative">
                                            <input type="number" wire:model.live.debounce.500ms="affectiveScores.{{ $trait }}"
                                                data-result-trait-group="affective"
                                                data-result-trait-key="{{ $trait }}"
                                                class="w-full border-2 @if($traitInvalid) border-red-500 bg-red-50 text-red-700 @else border-black @endif rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                                min="1" max="5">
                                            @if($traitInvalid)
                                                <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" title="Maximum is 5">!</div>
                                            @endif
                                        </div>
                                        @if($traitInvalid)
                                            <div class="text-xs text-red-600 mt-1 font-medium">Max: 5</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div>
                                <h4 class="font-semibold text-blue-800 mb-3">CO-CURRICULAR</h4>
                                @foreach($coCurricularScores as $activity => $value)
                                    @php
                                        $activityInvalid = $value > 5;
                                        if($activityInvalid) $hasAnyError = true;
                                    @endphp
                                    <div class="mb-3">
                                        <label class="block text-sm text-gray-700 mb-1">{{ $activity }}</label>
                                        <div class="relative">
                                            <input type="number" wire:model.live.debounce.500ms="coCurricularScores.{{ $activity }}"
                                                data-result-trait-group="co_curricular"
                                                data-result-trait-key="{{ $activity }}"
                                                class="w-full border-2 @if($activityInvalid) border-red-500 bg-red-50 text-red-700 @else border-black @endif rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                                min="1" max="5">
                                            @if($activityInvalid)
                                                <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" title="Maximum is 5">!</div>
                                            @endif
                                        </div>
                                        @if($activityInvalid)
                                            <div class="text-xs text-red-600 mt-1 font-medium">Max: 5</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @elseif($isRestrictedTeacherResultUploader)
                <div class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-900">
                    Class report fields for attendance, overall comment, traits, and principal comment are not available here because you are not the class teacher for this class.
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-6 border-t">
                <div>
                    <p class="text-sm text-gray-500 italic flex items-center">
                        <i class="fas fa-info-circle mr-2"></i> When you are online, score fields save automatically as you type.
                    </p>
                    <p class="mt-2 text-sm text-cyan-700 flex items-center">
                        <i class="fas fa-wifi mr-2"></i> If the internet drops, this device keeps a local draft of the open sheet. Restore it after reconnecting, then press Save All Data to sync comments, attendance, and traits.
                    </p>
                    @if($hasAnyError)
                        <p class="text-sm text-red-600 font-medium mt-2 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Some scores exceed maximum values. Please fix before saving.
                        </p>
                    @endif
                </div>
                <button wire:click="saveAll" wire:loading.attr="disabled"
                    @if($hasAnyError) disabled title="Fix errors before saving" @endif
                    class="bg-gradient-to-r @if($hasAnyError) from-gray-400 to-gray-500 cursor-not-allowed @else from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 @endif text-white font-medium px-8 py-3 rounded-xl shadow-lg transition-all duration-300 flex items-center">
                    <span wire:loading.remove wire:target="saveAll">
                        <i class="fas fa-save mr-2"></i> Save All Data
                    </span>
                    <span wire:loading wire:target="saveAll">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Saving...
                    </span>
                </button>
            </div>
        </div>
    @else
        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center">
            <i class="fas fa-user-graduate text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Student Selected</h3>
            <p class="text-gray-500">Select a student above to begin entering results</p>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        (() => {
            if (window.__resultUploadOfflineDraftBooted) {
                return;
            }

            window.__resultUploadOfflineDraftBooted = true;

            const ROOT_ID = 'result-upload-offline-root';
            const STORAGE_PREFIX = 'elites:result-upload:draft:v1';
            const FIELD_SELECTOR = [
                '#result-upload-class',
                '#result-upload-student',
                '#result-upload-present-days',
                '#result-upload-absent-days',
                '#result-upload-overall-comment',
                '#result-upload-principal-comment',
                '[data-result-subject-id][data-result-subject-field]',
                '[data-result-trait-group][data-result-trait-key]'
            ].join(', ');

            const getRoot = () => document.getElementById(ROOT_ID);

            const currentContext = (root) => {
                if (!root) {
                    return null;
                }

                return {
                    schoolId: root.dataset.schoolId || '0',
                    userId: root.dataset.userId || '0',
                    academicYearId: root.dataset.academicYearId || '0',
                    semesterId: root.dataset.semesterId || '0',
                    selectedClassId: root.dataset.selectedClassId || '0',
                    selectedStudentId: root.dataset.selectedStudentId || '0',
                    loadedClassId: root.dataset.loadedClassId || '0',
                    loadedStudentId: root.dataset.loadedStudentId || '0',
                };
            };

            const buildStorageKey = (context) => {
                if (
                    !context ||
                    context.academicYearId === '0' ||
                    context.semesterId === '0' ||
                    context.loadedStudentId === '0' ||
                    context.loadedClassId === '0'
                ) {
                    return null;
                }

                return [
                    STORAGE_PREFIX,
                    context.schoolId,
                    context.userId,
                    context.academicYearId,
                    context.semesterId,
                    context.loadedClassId,
                    context.loadedStudentId,
                ].join(':');
            };

            const loadDraft = (root) => {
                const storageKey = buildStorageKey(currentContext(root));

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

            const escapeAttrValue = (value) => {
                if (window.CSS && typeof window.CSS.escape === 'function') {
                    return window.CSS.escape(value);
                }

                return String(value).replace(/["\\]/g, '\\$&');
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

            const collectDraftState = (root) => ({
                selectedClass: root.querySelector('#result-upload-class')?.value || '',
                selectedStudent: root.querySelector('#result-upload-student')?.value || '',
                presentDays: root.querySelector('#result-upload-present-days')?.value || '',
                absentDays: root.querySelector('#result-upload-absent-days')?.value || '',
                overallTeacherComment: root.querySelector('#result-upload-overall-comment')?.value || '',
                principalComment: root.querySelector('#result-upload-principal-comment')?.value || '',
                results: Object.fromEntries(
                    Array.from(root.querySelectorAll('[data-result-subject-id][data-result-subject-field]')).map((field) => {
                        const subjectId = field.dataset.resultSubjectId;
                        const fieldName = field.dataset.resultSubjectField;

                        return [
                            `${subjectId}:${fieldName}`,
                            field.value,
                        ];
                    })
                ),
                psychomotorScores: Object.fromEntries(
                    Array.from(root.querySelectorAll('[data-result-trait-group="psychomotor"][data-result-trait-key]')).map((field) => [
                        field.dataset.resultTraitKey,
                        field.value,
                    ])
                ),
                affectiveScores: Object.fromEntries(
                    Array.from(root.querySelectorAll('[data-result-trait-group="affective"][data-result-trait-key]')).map((field) => [
                        field.dataset.resultTraitKey,
                        field.value,
                    ])
                ),
                coCurricularScores: Object.fromEntries(
                    Array.from(root.querySelectorAll('[data-result-trait-group="co_curricular"][data-result-trait-key]')).map((field) => [
                        field.dataset.resultTraitKey,
                        field.value,
                    ])
                ),
            });

            const persistDraft = (root) => {
                const context = currentContext(root);
                const storageKey = buildStorageKey(context);

                if (!storageKey) {
                    refreshDraftUi('Load a student online first to protect this result sheet locally.');
                    return;
                }

                try {
                    window.localStorage.setItem(storageKey, JSON.stringify({
                        savedAt: new Date().toISOString(),
                        context,
                        state: collectDraftState(root),
                    }));
                } catch (error) {
                    return;
                }

                refreshDraftUi();
            };

            const clearDraft = (root, statusMessage = 'Local result draft cleared.') => {
                const storageKey = buildStorageKey(currentContext(root));

                if (!storageKey) {
                    refreshDraftUi(statusMessage);
                    return;
                }

                window.localStorage.removeItem(storageKey);
                root.dataset.autoDraftRestored = '0';
                refreshDraftUi(statusMessage);
            };

            const restoreDraft = (root, options = {}) => {
                const draft = loadDraft(root);

                if (!draft?.state) {
                    refreshDraftUi('No matching result draft found on this device.');
                    return;
                }

                const state = draft.state;

                dispatchFieldUpdate(root.querySelector('#result-upload-present-days'), state.presentDays || '');
                dispatchFieldUpdate(root.querySelector('#result-upload-absent-days'), state.absentDays || '');
                dispatchFieldUpdate(root.querySelector('#result-upload-overall-comment'), state.overallTeacherComment || '');
                dispatchFieldUpdate(root.querySelector('#result-upload-principal-comment'), state.principalComment || '');

                Object.entries(state.results || {}).forEach(([compoundKey, value]) => {
                    const [subjectId, fieldName] = compoundKey.split(':');

                    if (!subjectId || !fieldName) {
                        return;
                    }

                    const field = root.querySelector(`[data-result-subject-id="${subjectId}"][data-result-subject-field="${fieldName}"]`);
                    dispatchFieldUpdate(field, value);
                });

                Object.entries(state.psychomotorScores || {}).forEach(([traitKey, value]) => {
                    const field = root.querySelector(`[data-result-trait-group="psychomotor"][data-result-trait-key="${escapeAttrValue(traitKey)}"]`);
                    dispatchFieldUpdate(field, value);
                });

                Object.entries(state.affectiveScores || {}).forEach(([traitKey, value]) => {
                    const field = root.querySelector(`[data-result-trait-group="affective"][data-result-trait-key="${escapeAttrValue(traitKey)}"]`);
                    dispatchFieldUpdate(field, value);
                });

                Object.entries(state.coCurricularScores || {}).forEach(([traitKey, value]) => {
                    const field = root.querySelector(`[data-result-trait-group="co_curricular"][data-result-trait-key="${escapeAttrValue(traitKey)}"]`);
                    dispatchFieldUpdate(field, value);
                });

                if (!options.silent) {
                    refreshDraftUi('Local result draft restored. Press Save All Data after reconnecting.');
                }
            };

            const refreshDraftUi = (statusMessage = '') => {
                const root = getRoot();

                if (!root) {
                    return;
                }

                const statusChip = root.querySelector('#result-upload-draft-status');
                const restoreButton = root.querySelector('#result-upload-restore-draft');
                const clearButton = root.querySelector('#result-upload-clear-draft');

                if (!statusChip || !restoreButton || !clearButton) {
                    return;
                }

                const context = currentContext(root);
                const storageKey = buildStorageKey(context);

                if (!storageKey) {
                    statusChip.classList.remove('hidden');
                    statusChip.textContent = statusMessage || 'Load a student online first to protect this result sheet locally.';
                    restoreButton.classList.add('hidden');
                    clearButton.classList.add('hidden');
                    return;
                }

                const draft = loadDraft(root);

                if (!draft?.state) {
                    statusChip.classList.remove('hidden');
                    statusChip.textContent = statusMessage || (navigator.onLine ? 'No local result draft for this sheet yet.' : 'Offline. No local result draft for this sheet yet.');
                    restoreButton.classList.add('hidden');
                    clearButton.classList.add('hidden');
                    return;
                }

                const savedLabel = formatTimestamp(draft.savedAt);
                const defaultMessage = navigator.onLine
                    ? `Local result draft ready${savedLabel ? ` from ${savedLabel}` : ''}. Restore it, then press Save All Data to sync everything.`
                    : `Offline draft active${savedLabel ? ` from ${savedLabel}` : ''}. Keep entering scores on this sheet.`;

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
            }, 250);

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

                if (event.target.closest('#result-upload-restore-draft')) {
                    restoreDraft(root);
                }

                if (event.target.closest('#result-upload-clear-draft')) {
                    clearDraft(root);
                }
            }, true);

            document.addEventListener('DOMContentLoaded', () => refreshDraftUi());
            document.addEventListener('livewire:navigated', () => refreshDraftUi());
            window.addEventListener('online', () => refreshDraftUi('Internet restored. Review the draft, then press Save All Data to sync.'));
            window.addEventListener('offline', () => refreshDraftUi('Offline mode. Changes on this result sheet are being kept on this device.'));

            document.addEventListener('livewire:init', () => {
                if (!window.Livewire || window.__resultUploadOfflineDraftLivewireHooked) {
                    return;
                }

                window.__resultUploadOfflineDraftLivewireHooked = true;

                if (typeof window.Livewire.hook === 'function') {
                    window.Livewire.hook('morph.updated', ({ el }) => {
                        if (el?.id === ROOT_ID || el?.querySelector?.(`#${ROOT_ID}`)) {
                            window.requestAnimationFrame(() => refreshDraftUi());
                        }
                    });
                }

                window.Livewire.on('result-upload-saved', () => {
                    const root = getRoot();

                    if (!root) {
                        return;
                    }

                    clearDraft(root, 'Result upload synced. Local draft cleared.');
                });
            }, { once: true });
        })();
    </script>
@endpush
