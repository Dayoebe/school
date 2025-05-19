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
            <div>
                <div class="text-center text-gray-500">
                    No result for {{ $academicYearName }} {{ $semesterName }}.
                </div>
                <div class="mt-6 text-right">
                    <button type="button" wire:click="$set('mode', 'index')"
                        class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Back</button>
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
                </table>


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
