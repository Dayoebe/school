<div x-data>
    @if ($mode === 'upload')
        <div class="bg-white rounded-2xl shadow-lg p-8 space-y-6">
            <h2 class="text-2xl font-semibold text-gray-800">Upload Student Results</h2>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm text-gray-700">
                <div><span class="font-semibold">Student:</span> {{ $studentRecord->user->name }}</div>
                <div><span class="font-semibold">Class:</span> {{ $studentRecord->myClass->name ?? 'N/A' }}</div>
                <div><span class="font-semibold">Section:</span> {{ $studentRecord->section->name ?? 'N/A' }}</div>
                <div><span class="font-semibold">Academic Year:</span>
                    {{ \App\Models\AcademicYear::find($academicYearId)?->name ?? 'N/A' }}</div>
                <div><span class="font-semibold">Semester:</span>
                    {{ \App\Models\Semester::find($semesterId)?->name ?? 'N/A' }}</div>
            </div>

            @if (session()->has('error'))
                <div class="bg-red-100 text-red-700 px-4 py-2 rounded-md">{{ session('error') }}</div>
            @endif

            @if (session()->has('success'))
                <div class="bg-green-100 text-green-700 px-4 py-2 rounded-md">{{ session('success') }}</div>
            @endif

            @if (isset($positions[$studentRecord->id]))
                <div><span class="font-semibold">Position:</span>
                    {{ $positions[$studentRecord->student_id]['position'] }}</div>
            @endif

            <form wire:submit.prevent="saveResults" class="space-y-4">
                <div class="overflow-x-auto rounded-lg shadow-sm">
                    <table class="min-w-full text-sm bg-white border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50 text-left text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2 border">Subject</th>
                                <th class="px-4 py-2 border text-center">Test<br>(max 40)</th>
                                <th class="px-4 py-2 border text-center">Exam<br>(max 60)</th>
                                <th class="px-4 py-2 border">Comment</th>
                                <th class="px-4 py-2 border text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($subjects as $subject)
                                @php
                                    $data = $results[$subject->id] ?? [
                                        'test_score' => 0,
                                        'exam_score' => 0,
                                        'comment' => '',
                                        'grade' => '',
                                    ];
                                    $test = $data['test_score'];
                                    $exam = $data['exam_score'];
                                    $total = (int) $test + (int) $exam;
                                    $grade = $data['grade'] ?? '';
                                    $gradeStyles = [
                                        'A1' => 'bg-green-100 text-green-800',
                                        'B2' => 'bg-emerald-100 text-emerald-800',
                                        'B3' => 'bg-lime-100 text-lime-800',
                                        'C4' => 'bg-yellow-100 text-yellow-800',
                                        'C5' => 'bg-amber-100 text-amber-800',
                                        'C6' => 'bg-orange-100 text-orange-800',
                                        'D7' => 'bg-rose-100 text-rose-800',
                                        'E8' => 'bg-red-200 text-red-800',
                                        'F9' => 'bg-red-600 text-white',
                                    ];
                                    $gradeEmoji = [
                                        'A1' => '🌟',
                                        'B2' => '🎯',
                                        'B3' => '🔥',
                                        'C4' => '👍',
                                        'C5' => '🧠',
                                        'C6' => '📘',
                                        'D7' => '📉',
                                        'E8' => '⚠️',
                                        'F9' => '💀',
                                    ];
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 border font-medium text-gray-800">
                                        {{ $subject->name }}
                                    </td>
                                    <td class="px-4 py-2 border text-center">
                                        <div class="relative">
                                            <input type="number"
                                                wire:model.live="results.{{ $subject->id }}.test_score"
                                                class="w-full border rounded px-2 py-1 @if ((int) ($results[$subject->id]['test_score'] ?? 0) > 40) border-red-500 @endif"
                                                min="0" max="40">
                                            @if ((int) ($results[$subject->id]['test_score'] ?? 0) > 40)
                                                <p class="text-red-500 text-xs mt-1">Max test score is 40</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 border text-center">
                                        <div class="relative">
                                            <input type="number"
                                                wire:model.live="results.{{ $subject->id }}.exam_score"
                                                class="w-full border rounded px-2 py-1 @if ((int) ($results[$subject->id]['exam_score'] ?? 0) > 60) border-red-500 @endif"
                                                min="0" max="60">
                                            @if ((int) ($results[$subject->id]['exam_score'] ?? 0) > 60)
                                                <p class="text-red-500 text-xs mt-1">Max exam score is 60</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 border">
                                        <input type="text" wire:model.live="results.{{ $subject->id }}.comment"
                                            class="w-full border rounded px-2 py-1">
                                    </td>
                                    <td class="px-4 py-2 border text-center">
                                        <span
                                            class="font-semibold px-2 py-1 rounded {{ $gradeStyles[$grade] ?? 'bg-gray-200 text-gray-800' }}">
                                            {{ $total }} <span class="text-xs">({{ $grade }})
                                                {{ $gradeEmoji[$grade] ?? '' }}</span>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>


                    <div class="mt-6 space-y-4">
                        <div class="mb-4">
                            <label for="teacher_comment" class="block font-semibold">Overall Teacher's Comment</label>
                            <textarea id="teacher_comment" wire:model.lazy="overallTeacherComment" class="w-full border p-2 rounded"></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="principal_comment" class="block font-semibold">Principal's Comment</label>
                            <textarea id="principal_comment" wire:model.lazy="principalComment" class="w-full border p-2 rounded"></textarea>
                        </div>

                    </div>
                </div>

                <div class="flex items-center justify-between pt-4">
                    <p class="text-sm text-gray-500 italic">Changes are saved automatically when you leave each field.
                    </p>
                    <div class="space-x-2">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg shadow transition">
                            Save All
                        </button>
                        <button type="button" wire:click="$set('mode', 'index')"
                            class="bg-gray-400 hover:bg-gray-500 text-white font-medium px-4 py-2 rounded-lg transition">
                            Cancel
                        </button>
                        <button type="button" wire:click="goBack"
                            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Back</button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>
