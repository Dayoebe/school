<div class="space-y-6">
    <!-- Selection Form -->
    <div class="bg-gradient-to-r from-gray-50 to-white rounded-2xl p-6 shadow-sm border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-filter mr-2 text-indigo-600"></i>
            Select Class and Subject
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                <select wire:model.live="selectedClass"
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
                    class="w-full border-2 border-black rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    @if(!$selectedClass) disabled @endif>
                    <option value="">All Sections</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </div> --}}

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                <select wire:model.live="selectedSubject"
                    class="w-full border-2 border-black rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    @if(!$selectedClass) disabled @endif>
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button wire:click="loadStudents" @if(!$selectedClass || !$selectedSubject) disabled @endif
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-3 px-4 rounded-xl transition-all duration-300 shadow-lg flex items-center justify-center">
                    <i class="fas fa-users mr-2"></i> Load Students
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Entry Table -->
@if(is_array($students) ? count($students) > 0 : $students->isNotEmpty())
<div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-4 flex justify-between items-center">
        <h3 class="text-xl font-bold text-white flex items-center">
            <i class="fas fa-table mr-2"></i>
            Bulk Entry - {{ $subjects->firstWhere('id', $selectedSubject)?->name }}
        </h3>
        <span class="bg-white/20 px-4 py-2 rounded-lg text-white font-medium">
            {{ is_array($students) ? count($students) : $students->count() }} Students
        </span>
    </div>

            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Student
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider w-24">
                                CA1 (10)
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider w-24">
                                CA2 (10)
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider w-24">
                                CA3 (10)
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider w-24">
                                CA4 (10)
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider w-24">
                                Exam (60)
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider w-24">
                                Total
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider w-20">
                                Action
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Comment
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($students as $student)
                            @php
                                $studentData = $bulkResults[$student->id] ?? [];
                                $ca1 = (int) ($studentData['ca1_score'] ?? 0);
                                $ca2 = (int) ($studentData['ca2_score'] ?? 0);
                                $ca3 = (int) ($studentData['ca3_score'] ?? 0);
                                $ca4 = (int) ($studentData['ca4_score'] ?? 0);
                                $exam = (int) ($studentData['exam_score'] ?? 0);
                                
                                $ca1Invalid = $ca1 > 10;
                                $ca2Invalid = $ca2 > 10;
                                $ca3Invalid = $ca3 > 10;
                                $ca4Invalid = $ca4 > 10;
                                $examInvalid = $exam > 60;
                                $hasError = $ca1Invalid || $ca2Invalid || $ca3Invalid || $ca4Invalid || $examInvalid;
                            @endphp
                            
                            <tr class="hover:bg-gray-50 transition-colors @if($hasError) bg-red-50 @endif">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img class="h-10 w-10 rounded-full object-cover border-2 border-indigo-200"
                                                    src="{{ $student->user->profile_photo_url }}" alt="{{ $student->user->name }}">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $student->user->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $student->admission_number }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-2 py-2 text-center">
                                            <div class="relative">
                                                <input type="number"
                                                    wire:model.live.debounce.500ms="bulkResults.{{ $student->id }}.ca1_score"
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
                                                    wire:model.live.debounce.500ms="bulkResults.{{ $student->id }}.ca2_score"
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
                                                    wire:model.live.debounce.500ms="bulkResults.{{ $student->id }}.ca3_score"
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
                                                    wire:model.live.debounce.500ms="bulkResults.{{ $student->id }}.ca4_score"
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
                                                    wire:model.live.debounce.500ms="bulkResults.{{ $student->id }}.exam_score"
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

                                        <td class="px-4 py-4 text-center font-bold @if($hasError) text-red-600 @else text-indigo-600 @endif">
                                            {{ 
                                                $ca1 + $ca2 + $ca3 + $ca4 + $exam
                                            }}
                                            @if($hasError)
                                                <div class="text-xs text-red-500 font-normal mt-1">Check scores</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <button wire:click="deleteResult({{ $student->id }})"
                                                onclick="return confirm('Delete this result?')"
                                                class="text-red-600 hover:text-red-800 transition-colors" title="Delete result">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>

                                        <td class="px-6 py-4">
                                            <input type="text" wire:model.live.debounce.500ms="bulkResults.{{ $student->id }}.comment"
                                                class="w-full border-2 border-black rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                                placeholder="Comment">
                                        </td>

                                    </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t">
                <div>
                    <p class="text-sm text-gray-600 italic">
                        <i class="fas fa-info-circle mr-1"></i>
                        Scores are saved automatically as you type
                    </p>
                    @if($this->hasAnyError)
                        <p class="text-sm text-red-600 font-medium mt-1 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Some scores exceed maximum values. Please fix before saving.
                        </p>
                    @endif
                </div>
                <button wire:click="saveAll" wire:loading.attr="disabled"
                    @if($this->hasAnyError) disabled title="Fix errors before saving" @endif
                    class="bg-gradient-to-r @if($this->hasAnyError) from-gray-400 to-gray-500 cursor-not-allowed @else from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 @endif text-white font-medium px-6 py-3 rounded-xl shadow-lg transition-all duration-300 flex items-center">
                    <span wire:loading.remove wire:target="saveAll">
                        <i class="fas fa-save mr-2"></i> Save All Results
                    </span>
                    <span wire:loading wire:target="saveAll">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Saving...
                    </span>
                </button>
            </div>
        </div>
    @else
        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center">
            <i class="fas fa-users text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Students Loaded</h3>
            <p class="text-gray-500">Select a class and subject, then click "Load Students" to begin</p>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        // Auto-save notification
        window.addEventListener('success', event => {
            // Optional: Show toast notification
        });
    </script>
@endpush