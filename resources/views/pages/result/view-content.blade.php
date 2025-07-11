<div>
    @if ($mode === 'view')
        @php
            // Filter results to only include subjects with a total score
            $filteredResults = collect($results)->filter(fn($r) => !is_null($r['total_score']));

            $hasResult = $filteredResults->isNotEmpty();

            $academicYearName = \App\Models\AcademicYear::find($academicYearId)?->name ?? 'N/A';
            $semesterName = \App\Models\Semester::find($semesterId)?->name ?? 'N/A';

            // Define grade styles for consistency
            $gradeStyles = [
                'A1' => 'bg-green-100 text-green-800',
                'B2' => 'bg-blue-100 text-blue-800',
                'B3' => 'bg-blue-100 text-blue-800',
                'C4' => 'bg-yellow-100 text-yellow-800',
                'C5' => 'bg-yellow-100 text-yellow-800',
                'C6' => 'bg-yellow-100 text-yellow-800',
                'D7' => 'bg-orange-100 text-orange-800',
                'E8' => 'bg-orange-100 text-orange-800',
                'F9' => 'bg-red-100 text-red-800',
            ];

            // Define score background styles
            $scoreBackgroundStyles = function($score) {
                if ($score >= 75) return 'bg-green-50';
                if ($score >= 60) return 'bg-blue-50';
                if ($score >= 40) return 'bg-yellow-50';
                return 'bg-red-50';
            };

            // Calculate dynamic comments here
            $percentage = $totalPossibleMarks > 0 ? round(($grandTotal / $totalPossibleMarks) * 100, 2) : 0;

            $dynamicTeacherComment = match (true) {
                $percentage >= 80 => 'An excellent and truly outstanding performance. Keep it up!',
                $percentage >= 70 => 'Very good work this term. A commendable effort.',
                $percentage >= 60 => 'A good and consistent performance. Well done.',
                $percentage >= 50 => 'This is a satisfactory result, but there is room for improvement.',
                $percentage >= 40 => 'Shows potential but needs to apply more effort to improve.',
                default => 'An unsatisfactory performance. Requires significant improvement.',
            };
            $dynamicPrincipalComment = match (true) {
                $percentage >= 80 => 'Outstanding achievement! A model student for others to emulate.',
                $percentage >= 70 => 'A very strong performance. We are proud of your progress.',
                $percentage >= 60 => 'Good results. Continue to aim higher next term.',
                $percentage >= 50 => 'An adequate performance. Greater focus is required for better results.',
                $percentage >= 40 => 'A marginal pass. Serious improvement is required.',
                default => 'This result is below the expected standard. Urgent intervention is needed.',
            };

            // Determine final comments based on priority: manual comment first, then dynamic
            // Assuming $overallTeacherComment and $principalComment are passed from Livewire component
            $finalTeacherComment = !empty($overallTeacherComment) && $overallTeacherComment !== 'Impressive'
                                ? $overallTeacherComment
                                : $dynamicTeacherComment;

            $finalPrincipalComment = !empty($principalComment) && $principalComment !== 'Keep up the good work!'
                                    ? $principalComment
                                    : $dynamicPrincipalComment;

        @endphp

        @if (!$hasResult)
            <div class="flex flex-col items-center justify-center bg-white p-6 rounded-lg shadow-sm text-center">
                <div class="text-red-500 mb-4">
                    <!-- Warning Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14M12 2a10 10 0 100 20 10 10 0 000-20z" />
                    </svg>
                </div>

                <h2 class="text-xl font-semibold text-gray-700">
                    No Result Found
                </h2>
                <p class="text-gray-500 mt-2">
                    There are no results recorded for {{ $studentRecord->user->name }}
                    for the {{ $semesterName }} of {{ $academicYearName }}.
                </p>
                <button type="button" wire:click="$set('mode', 'index')"
                    class="mt-6 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-300 shadow-md">
                    Go Back
                </button>
            </div>
        @else
            <div class="bg-blue-50 rounded-2xl shadow-xl p-8 space-y-6"> {{-- Added bg-blue-50 for entire page --}}
                <!-- Student Info Header -->
                <div class="flex items-center space-x-4 border-b border-blue-200 pb-4 mb-4 bg-white p-4 rounded-xl shadow-sm"> {{-- Added bg-white for header --}}
                    <img src="{{ $studentRecord->user->profile_photo_url }}" alt="Student Photo"
                        class="h-20 w-20 rounded-full object-cover border-4 border-indigo-200 shadow-md">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">{{ $studentRecord->user->name }}</h3>
                        <p class="text-indigo-600 text-lg">{{ $studentRecord->myClass->name ?? 'N/A' }} -
                            {{ $academicYearName }} - {{ $semesterName }}</p>
                        <p class="text-gray-500 text-sm">Admission No: {{ $studentRecord->admission_number ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Overall Summary -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-indigo-100 p-6 rounded-xl shadow-inner"> {{-- Adjusted background --}}
                    <div class="flex flex-col items-center justify-center p-4 bg-white rounded-lg shadow-sm">
                        <p class="text-sm font-medium text-gray-600">Total Score</p>
                        <p class="text-3xl font-bold text-indigo-700 mt-1">{{ $grandTotal }}<span class="text-lg text-gray-500">/{{ $totalPossibleMarks }}</span></p>
                    </div>
                    <div class="flex flex-col items-center justify-center p-4 bg-white rounded-lg shadow-sm">
                        <p class="text-sm font-medium text-gray-600">Percentage</p>
                        <p class="text-3xl font-bold text-purple-700 mt-1">{{ $percentage }}%</p>
                    </div>
                    <div class="flex flex-col items-center justify-center p-4 bg-white rounded-lg shadow-sm">
                        <p class="text-sm font-medium text-gray-600">Class Position</p>
                        <p class="text-3xl font-bold text-green-700 mt-1">
                            {{ $positions[$studentRecord->id]['position'] ?? 'N/A' }}
                            <span class="text-lg text-gray-500">of {{ $positions[$studentRecord->id]['total_students'] ?? 'N/A' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="overflow-x-auto rounded-xl shadow-md border border-gray-200 bg-white"> {{-- Added bg-white for table container --}}
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Subject</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    CA1 (10)</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    CA2 (10)</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    CA3 (10)</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    CA4 (10)</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Exam (60)</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total (100)</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Grade</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Teacher Comment</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($subjects as $subject)
                                @php
                                    $result = $results[$subject->id] ?? null;
                                    $total = $result['total_score'] ?? null;
                                    $grade = $result['grade'] ?? null;
                                    $comment = $result['comment'] ?? null;
                                @endphp
                                {{-- Only display row if total_score is not null --}}
                                @if (!is_null($total))
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $subject->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ $result['ca1_score'] ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ $result['ca2_score'] ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ $result['ca3_score'] ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ $result['ca4_score'] ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ $result['exam_score'] ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-center {{ $scoreBackgroundStyles($total) }}">
                                            {{ $total }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center {{ $gradeStyles[$grade] ?? 'bg-gray-200' }}"> {{-- Added background color to grade cell --}}
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $gradeStyles[$grade] ?? 'bg-gray-200 text-gray-800' }}">
                                                {{ $grade ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            {{ $comment ?? '-' }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Comments & Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200"> {{-- Adjusted background --}}
                        <h4 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-comment-dots mr-2 text-indigo-500"></i> Teacher's Comment
                        </h4>
                        <p class="text-gray-700 italic">
                            {{ $finalTeacherComment }}
                        </p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200"> {{-- Adjusted background --}}
                        <h4 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-user-tie mr-2 text-purple-500"></i> Principal's Comment
                        </h4>
                        <p class="text-gray-700 italic">
                            {{ $finalPrincipalComment }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" wire:click="$set('mode', 'index')"
                        class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-300 shadow-md flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Students
                    </button>
                    <a href="{{ route('result.print', [
                        'student' => $studentRecord->id,
                        'academicYearId' => $academicYearId,
                        'semesterId' => $semesterId,
                    ]) }}"
                        target="_blank"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-300 shadow-md">
                        <i class="fas fa-print mr-2"></i> Print Result
                    </a>
                </div>
            </div>
        @endif
    @endif
</div>
