<div>
    @if ($mode === 'view')
        @php
            $hasResult = collect($results)
                ->filter(function ($r) {
                    return !empty($r['test_score']) || !empty($r['exam_score']);
                })
                ->isNotEmpty();

            $academicYearName = \App\Models\AcademicYear::find($academicYearId)?->name ?? 'N/A';
            $semesterName = \App\Models\Semester::find($semesterId)?->name ?? 'N/A';
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
                <p class="text-gray-600 mt-1">
                    There is no result for <strong>{{ $academicYearName }}</strong> -
                    <strong>{{ $semesterName }}</strong>.
                </p>

                <div class="mt-6">
                    <button type="button" wire:click="$set('mode', 'index')"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition duration-200 ease-in-out">
                        ← Back to Results
                    </button>
                </div>
            </div>
        @else
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="mb-4">
                    <h2 class="text-2xl font-bold">Result for {{ $studentRecord->user->name }}</h2>
                    <p class="text-gray-600">
                        Class: <strong>{{ $studentRecord->myClass->name }}</strong> |
                        Section: <strong>{{ $studentRecord->section->name }}</strong> |
                        Academic Year:
                        <strong>{{ \App\Models\AcademicYear::find($academicYearId)->name ?? 'N/A' }}</strong>
                        |
                        Semester: <strong>{{ \App\Models\Semester::find($semesterId)->name ?? 'N/A' }}</strong>
                    </p>
                </div>

                <table class="w-full table-auto border rounded-lg">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border">Subject</th>
                            <th class="p-2 border">Test</th>
                            <th class="p-2 border">Exam</th>
                            <th class="p-2 border">Total</th>
                            <th class="p-2 border">Teacher's Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subjects as $subject)
                            <tr>
                                <td class="p-2 border">{{ $subject->name }}</td>
                                <td class="p-2 border">{{ $results[$subject->id]['test_score'] }}</td>
                                <td class="p-2 border">{{ $results[$subject->id]['exam_score'] }}</td>
                                <td class="p-2 border">{{ $results[$subject->id]['total_score'] }}</td>
                                <td class="p-2 border">{{ $results[$subject->id]['comment'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100">
                            <th class="p-2 border">Grand Total</th>
                            <td class="p-2 border">{{ $grandTotalTest }}</td>
                            <td class="p-2 border">{{ $grandTotalExam }}</td>
                            <td class="p-2 border">{{ $grandTotal }}</td>
                            <td class="p-2 border">
                                {{ $principalComment ?? 'No comment available' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <p><strong>Percentage:</strong> {{ $percentage }}%</p>

                <div class="mt-4 flex justify-end">
                    <div class="text-right">
                        <h3 class="text-lg font-semibold text-gray-700">Total score:</h3>
                        <p class="text-xl text-blue-600 font-bold">{{ $grandTotal }}</p>
                    </div>
                </div>
                

                <tr>
                    <th class="p-2 border">Class Position</th>
                    <td class="p-2 border">
                        @php
                            $totalStudents = \App\Models\StudentRecord::where(
                                'my_class_id',
                                $studentRecord->my_class_id,
                            )->count();
                            $classPosition = $results[$subject->id]['class_position'] ?? null;
                        @endphp
                        {{ $classPosition ? "$classPosition out of $totalStudents" : '-' }}
                    </td>
                </tr>


                <div class="mt-6 text-right">
                    <button type="button" wire:click="$set('mode', 'index')"
                        class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Back</button>
                </div>
            </div>
        @endif
    @endif
</div>
