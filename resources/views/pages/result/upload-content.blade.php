<div x-data="{
    isSaving: @entangle('isSaving').live,
    showSuccess: false,
    successMessage: '',
    showError: false,
    errorMessage: '',
    confirmingClearScores: null, // For confirming score clear action
    // Function to calculate total score for a subject in real-time
    calculateTotal: function(ca1, ca2, ca3, ca4, exam) {
        return (parseInt(ca1) || 0) + (parseInt(ca2) || 0) + (parseInt(ca3) || 0) + (parseInt(ca4) || 0) + (parseInt(exam) || 0);
    }
}" x-init="Livewire.on('showSuccess', (message) => {
    successMessage = message;
    showSuccess = true;
    setTimeout(() => { showSuccess = false }, 3000);
});
Livewire.on('showError', (message) => {
    errorMessage = message;
    showError = true;
    setTimeout(() => { showError = false }, 5000);
});">
    @if ($mode === 'upload')
        <div class="bg-white rounded-2xl shadow-lg p-8 space-y-6 border border-gray-200">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center mb-6">
                <i class="fas fa-cloud-arrow-up mr-3 text-indigo-600"></i> Upload Student Results
            </h2>

            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-700 bg-gray-50 p-4 rounded-xl shadow-inner border border-gray-100">
                <div class="flex items-center"><span class="font-semibold mr-2 text-gray-800"><i
                            class="fas fa-user-graduate text-blue-500 mr-1"></i> Student:</span>
                    {{ $studentRecord->user->name }}</div>
                <div class="flex items-center"><span class="font-semibold mr-2 text-gray-800"><i
                            class="fas fa-school text-green-500 mr-1"></i> Class:</span>
                    {{ $studentRecord->myClass->name ?? 'N/A' }}</div>
                <div class="flex items-center"><span class="font-semibold mr-2 text-gray-800"><i
                            class="fas fa-layer-group text-purple-500 mr-1"></i> Section:</span>
                    {{ $studentRecord->section->name ?? 'N/A' }}</div>
                <div class="flex items-center"><span class="font-semibold mr-2 text-gray-800"><i
                            class="fas fa-calendar-alt text-red-500 mr-1"></i> Academic Year:</span>
                    {{ \App\Models\AcademicYear::find($academicYearId)?->name ?? 'N/A' }}</div>
                <div class="flex items-center"><span class="font-semibold mr-2 text-gray-800"><i
                            class="fas fa-calendar-week text-yellow-500 mr-1"></i> Semester:</span>
                    {{ \App\Models\Semester::find($semesterId)?->name ?? 'N/A' }}</div>
            </div>

            {{-- Success/Error Notifications (using Alpine.js toasts) --}}
            <div x-show="showSuccess" x-transition
                class="bg-green-100 text-green-700 px-4 py-3 rounded-xl flex items-center shadow-md border border-green-200">
                <i class="fas fa-check-circle mr-3 text-lg"></i>
                <span x-text="successMessage"></span>
            </div>
            <div x-show="showError" x-transition
                class="bg-red-100 text-red-700 px-4 py-3 rounded-xl flex items-center shadow-md border border-red-200">
                <i class="fas fa-times-circle mr-3 text-lg"></i>
                <span x-text="errorMessage"></span>
            </div>

            <form wire:submit="saveResults">
                <div class="overflow-x-auto rounded-xl shadow-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-indigo-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider sticky top-0 bg-indigo-50 z-10">
                                    Subject</th>
                                <th
                                    class="px-2 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider sticky top-0 bg-indigo-50 z-10 w-[70px]">
                                    CA1 (10)</th>
                                <th
                                    class="px-2 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider sticky top-0 bg-indigo-50 z-10 w-[70px]">
                                    CA2 (10)</th>
                                <th
                                    class="px-2 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider sticky top-0 bg-indigo-50 z-10 w-[70px]">
                                    CA3 (10)</th>
                                <th
                                    class="px-2 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider sticky top-0 bg-indigo-50 z-10 w-[70px]">
                                    CA4 (10)</th>
                                <th
                                    class="px-2 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider sticky top-0 bg-indigo-50 z-10 w-[70px]">
                                    Exam (60)</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider sticky top-0 bg-indigo-50 z-10 w-[80px]">
                                    Total (100)</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider sticky top-0 bg-indigo-50 z-10">
                                    Comment</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider sticky top-0 bg-indigo-50 z-10">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($subjects as $subject)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $subject->name }}</td>
                                    <td class="px-2 py-1 text-center">
                                        <input type="number"
                                            wire:model.live.debounce.500ms="results.{{ $subject->id }}.ca1_score"
                                            class="w-full border-gray-300 rounded-lg shadow-sm text-center px-1 py-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            min="0" max="10">
                                        @error('results.' . $subject->id . '.ca1_score')
                                            <span class="text-red-500 text-xs block">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td class="px-2 py-1 text-center">
                                        <input type="number"
                                            wire:model.live.debounce.500ms="results.{{ $subject->id }}.ca2_score"
                                            class="w-full border-gray-300 rounded-lg shadow-sm text-center px-1 py-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            min="0" max="10">
                                        @error('results.' . $subject->id . '.ca2_score')
                                            <span class="text-red-500 text-xs block">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td class="px-2 py-1 text-center">
                                        <input type="number"
                                            wire:model.live.debounce.500ms="results.{{ $subject->id }}.ca3_score"
                                            class="w-full border-gray-300 rounded-lg shadow-sm text-center px-1 py-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            min="0" max="10">
                                        @error('results.' . $subject->id . '.ca3_score')
                                            <span class="text-red-500 text-xs block">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td class="px-2 py-1 text-center">
                                        <input type="number"
                                            wire:model.live.debounce.500ms="results.{{ $subject->id }}.ca4_score"
                                            class="w-full border-gray-300 rounded-lg shadow-sm text-center px-1 py-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            min="0" max="10">
                                        @error('results.' . $subject->id . '.ca4_score')
                                            <span class="text-red-500 text-xs block">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td class="px-2 py-1 text-center">
                                        <input type="number"
                                            wire:model.live.debounce.500ms="results.{{ $subject->id }}.exam_score"
                                            class="w-full border-gray-300 rounded-lg shadow-sm text-center px-1 py-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            min="0" max="60">
                                        @error('results.' . $subject->id . '.exam_score')
                                            <span class="text-red-500 text-xs block">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-bold text-gray-800">
                                        {{-- Real-time total calculation using Alpine.js --}}
                                        <span
                                            x-text="calculateTotal(
                                            $wire.results[{{ $subject->id }}].ca1_score,
                                            $wire.results[{{ $subject->id }}].ca2_score,
                                            $wire.results[{{ $subject->id }}].ca3_score,
                                            $wire.results[{{ $subject->id }}].ca4_score,
                                            $wire.results[{{ $subject->id }}].exam_score
                                        )"></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" wire:model.live.blur="results.{{ $subject->id }}.comment"
                                            class="w-full border-gray-300 rounded-lg shadow-sm px-2 py-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            placeholder="Teacher's comment">
                                        @error('results.' . $subject->id . '.comment')
                                            <span class="text-red-500 text-xs block">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <button type="button" wire:click="deleteResult({{ $subject->id }})"
                                            class="text-red-600 hover:text-red-800 transition-colors duration-200 transform hover:scale-110"
                                            title="Delete Result">
                                            <i class="fas fa-trash-alt text-lg"></i>
                                        </button>
                                        <button type="button" @click="confirmingClearScores = {{ $subject->id }}"
                                            class="text-orange-600 hover:text-orange-800 transition-colors duration-200 transform hover:scale-110 ml-2"
                                            title="Clear Scores">
                                            <i class="fas fa-eraser text-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-8 text-center text-gray-500 text-lg">
                                        <i class="fas fa-exclamation-circle mr-2"></i> No subjects found for this
                                        class.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Overall Comments Section --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                    <div class="bg-gray-50 p-6 rounded-xl shadow-inner border border-gray-200">
                        <label for="overallTeacherComment"
                            class="block font-semibold text-gray-800 mb-2 flex items-center">
                            <i class="fas fa-chalkboard-teacher mr-2 text-blue-600"></i> Overall Teacher's Comment
                        </label>
                        <textarea id="overallTeacherComment" wire:model.live.blur="overallTeacherComment"
                            class="w-full border-gray-300 rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 text-gray-800 h-24 resize-y"
                            placeholder="Enter overall comment for the student's performance..."></textarea>
                        @error('overallTeacherComment')
                            <span class="text-red-500 text-xs block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="bg-gray-50 p-6 rounded-xl shadow-inner border border-gray-200">
                        <label for="principal_comment"
                            class="block font-semibold text-gray-800 mb-2 flex items-center">
                            <i class="fas fa-user-tie mr-2 text-green-600"></i> Principal's Comment
                        </label>
                        <textarea id="principal_comment" wire:model.live.blur="principalComment"
                            class="w-full border-gray-300 rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 text-gray-800 h-24 resize-y"
                            placeholder="Enter principal's comment..."></textarea>
                        @error('principalComment')
                            <span class="text-red-500 text-xs block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Section for Attendance and Extra-curricular Activities --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                    <!-- Attendance Section -->
                    <div class="bg-gray-50 p-6 rounded-xl shadow-inner border border-gray-200">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-clipboard-check mr-2 text-purple-600"></i> Attendance Record
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="presentDays" class="block text-sm font-medium text-gray-700 mb-2">Days
                                    Present</label>
                                <input type="number" wire:model.live.blur="presentDays" id="presentDays"
                                    class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 text-gray-800"
                                    min="0" placeholder="e.g., 90">
                                @error('presentDays')
                                    <span class="text-red-500 text-xs block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="absentDays" class="block text-sm font-medium text-gray-700 mb-2">Days
                                    Absent</label>
                                <input type="number" wire:model.live.blur="absentDays" id="absentDays"
                                    class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 text-gray-800"
                                    min="0" placeholder="e.g., 5">
                                @error('absentDays')
                                    <span class="text-red-500 text-xs block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Extra-curricular & Traits Section -->
                    <div class="bg-gray-50 p-6 rounded-xl shadow-inner border border-gray-200 mt-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-star mr-2 text-yellow-600"></i> Extra-curricular & Traits (1-5 Scale)
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Psychomotor Traits -->
                            <div class="border border-gray-300 p-3 rounded-lg shadow-sm bg-white">
                                <h4
                                    class="font-bold text-blue-800 border-b border-gray-200 pb-2 mb-3 flex items-center">
                                    <i class="fas fa-brain mr-2 text-blue-500"></i> PSYCHOMOTOR
                                </h4>
                                <ul class="space-y-3">
                                    @foreach ($psychomotorScores as $trait => $value)
                                        <li>
                                            <label for="psychomotor-{{ Str::slug($trait) }}"
                                                class="block text-gray-700 mb-1">
                                                {{ $trait }}:
                                            </label>
                                            <input type="number"
                                                wire:model.live.blur="psychomotorScores.{{ $trait }}"
                                                id="psychomotor-{{ Str::slug($trait) }}"
                                                class="w-full border-gray-300 rounded-lg shadow-sm px-2 py-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                min="1" max="5" placeholder="1-5">
                                            @error('psychomotorScores.' . $trait)
                                                <span class="text-red-500 text-xs block mt-1">{{ $message }}</span>
                                            @enderror
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <!-- Affective Traits -->
                            <div class="border border-gray-300 p-3 rounded-lg shadow-sm bg-white">
                                <h4
                                    class="font-bold text-blue-800 border-b border-gray-200 pb-2 mb-3 flex items-center">
                                    <i class="fas fa-heart mr-2 text-red-500"></i> AFFECTIVE
                                </h4>
                                <ul class="space-y-3">
                                    @foreach ($affectiveScores as $trait => $value)
                                        <li>
                                            <label for="affective-{{ Str::slug($trait) }}"
                                                class="block text-gray-700 mb-1">
                                                {{ $trait }}:
                                            </label>
                                            <input type="number"
                                                wire:model.live.blur="affectiveScores.{{ $trait }}"
                                                id="affective-{{ Str::slug($trait) }}"
                                                class="w-full border-gray-300 rounded-lg shadow-sm px-2 py-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                min="1" max="5" placeholder="1-5">
                                            @error('affectiveScores.' . $trait)
                                                <span class="text-red-500 text-xs block mt-1">{{ $message }}</span>
                                            @enderror
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <!-- Co-curricular Activities -->
                            <div class="border border-gray-300 p-3 rounded-lg shadow-sm bg-white">
                                <h4
                                    class="font-bold text-blue-800 border-b border-gray-200 pb-2 mb-3 flex items-center">
                                    <i class="fas fa-running mr-2 text-green-500"></i> CO-CURRICULAR
                                </h4>
                                <ul class="space-y-3">
                                    @foreach ($coCurricularScores as $activity => $value)
                                        <li>
                                            <label for="cocurricular-{{ Str::slug($activity) }}"
                                                class="block text-gray-700 mb-1">
                                                {{ $activity }}:
                                            </label>
                                            <input type="number"
                                                wire:model.live.blur="coCurricularScores.{{ $activity }}"
                                                id="cocurricular-{{ Str::slug($activity) }}"
                                                class="w-full border-gray-300 rounded-lg shadow-sm px-2 py-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                min="1" max="5" placeholder="1-5">
                                            @error('coCurricularScores.' . $activity)
                                                <span class="text-red-500 text-xs block mt-1">{{ $message }}</span>
                                            @enderror
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="flex items-center justify-between pt-8">
                    <p class="text-sm text-gray-500 italic flex items-center">
                        <i class="fas fa-info-circle mr-2"></i> Scores are saved automatically as you type. Comments,
                        attendance, and extra-curriculars are saved on blur or when you click 'Save All Comments'.
                    </p>
                    <div class="space-x-3 flex">
                        <button type="submit"
                            class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-medium px-6 py-3 rounded-xl shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                            <span wire:loading wire:target="saveResults" class="mr-2"><i
                                    class="fas fa-spinner fa-spin"></i></span>
                            <i class="fas fa-save mr-2" wire:loading.remove wire:target="saveResults"></i> Save All
                            Comments
                        </button>
                        <button type="button" wire:click="goBack"
                            class="bg-gray-400 hover:bg-gray-500 text-white font-medium px-6 py-3 rounded-xl shadow-md transition duration-300 transform hover:scale-105 flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Students
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Clear Scores Confirmation Modal --}}
        <div x-show="confirmingClearScores"
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-75 flex items-center justify-center p-4">
            <div
                class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-eraser text-orange-600 text-xl"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Clear Scores
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to clear all scores for this subject? This action will set all
                                    CA and Exam scores to zero for this subject.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button"
                        wire:click="clearSubjectScores(confirmingClearScores); confirmingClearScores = null"
                        class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Clear Scores
                    </button>
                    <button type="button" @click="confirmingClearScores = null"
                        class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

    @endif
</div>
