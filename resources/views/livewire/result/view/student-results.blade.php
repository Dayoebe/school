<div class="space-y-6">
    @if(!$viewingStudent)
        <!-- Student List View -->
        <div class="bg-gradient-to-r from-gray-50 to-white rounded-2xl p-6 shadow-sm">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-search mr-2 text-indigo-600"></i>
                Find Students
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                    <select wire:model.live="selectedClass"
                        class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Section (Optional)</label>
                    <select wire:model.live="selectedSection"
                        class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Sections</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Student</label>
                    <input type="text" wire:model.live.debounce.300ms="searchTerm"
                        class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500"
                        placeholder="Search by name...">
                </div>
            </div>
        </div>

        @if($students->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-indigo-600 to-purple-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                    Student
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">
                                    Results Uploaded
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($students as $student)
                                <tr class="hover:bg-indigo-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img class="h-12 w-12 rounded-full object-cover border-2 border-indigo-200"
                                                src="{{ $student->user->profile_photo_url }}"
                                                alt="{{ $student->user->name }}">
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900">{{ $student->user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $student->admission_number }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($student->results->isEmpty())
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                No Results
                                            </span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ $student->results->count() }} Subjects
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <button wire:click="viewStudent({{ $student->id }})"
                                                class="text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 px-4 py-2 rounded-xl transition-all duration-300 flex items-center shadow">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </button>
                                            <a href="{{ route('result.print', [
                                                'student' => $student->id,
                                                'academicYearId' => $academicYearId,
                                                'semesterId' => $semesterId,
                                            ]) }}" target="_blank"
                                                class="text-white bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700 px-4 py-2 rounded-xl transition-all duration-300 flex items-center shadow">
                                                <i class="fas fa-print mr-1"></i> Print
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t bg-gray-50">
                    {{ $students->links() }}
                </div>
            </div>
        @elseif($selectedClass)
            <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center">
                <i class="fas fa-user-slash text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Students Found</h3>
                <p class="text-gray-500">Try adjusting your search filters</p>
            </div>
        @endif

    @else
        <!-- Student Detail View -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Header with Back Button -->
            <div class="flex items-center justify-between mb-6">
                <button wire:click="backToList"
                    class="flex items-center text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Back to List
                </button>
                <a href="{{ route('result.print', [
                    'student' => $studentRecord->id,
                    'academicYearId' => $academicYearId,
                    'semesterId' => $semesterId,
                ]) }}" target="_blank"
                    class="bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 text-white px-6 py-3 rounded-xl font-medium flex items-center shadow-lg">
                    <i class="fas fa-print mr-2"></i> Print Report
                </a>
            </div>

            <!-- Student Info -->
            <div class="flex items-center space-x-4 border-b border-gray-200 pb-6 mb-6">
                <img src="{{ $studentRecord->user->profile_photo_url }}" 
                    alt="{{ $studentRecord->user->name }}"
                    class="h-20 w-20 rounded-full object-cover border-4 border-indigo-200 shadow-md">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $studentRecord->user->name }}</h3>
                    <p class="text-indigo-600 text-lg">{{ $studentRecord->myClass->name ?? 'N/A' }}</p>
                    <p class="text-gray-500 text-sm">Admission No: {{ $studentRecord->admission_number ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-xl border border-blue-200">
                    <p class="text-sm text-blue-600 font-medium">Total Score</p>
                    <p class="text-2xl font-bold text-blue-700">
                        {{ collect($results)->sum('total_score') }}
                    </p>
                </div>
                <div class="bg-purple-50 p-4 rounded-xl border border-purple-200">
                    <p class="text-sm text-purple-600 font-medium">Average</p>
                    <p class="text-2xl font-bold text-purple-700">
                        {{ count($results) > 0 ? round(collect($results)->sum('total_score') / count($results), 1) : 0 }}%
                    </p>
                </div>
                <div class="bg-green-50 p-4 rounded-xl border border-green-200">
                    <p class="text-sm text-green-600 font-medium">Position</p>
                    <p class="text-2xl font-bold text-green-700">
                        {{ $studentPosition }}/{{ $totalStudents }}
                    </p>
                </div>
            </div>

            <!-- Results Table -->
            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Subject</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">CA1</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">CA2</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">CA3</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">CA4</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Exam</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Grade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Comment</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($subjects as $subject)
                            @php
                                $result = $results[$subject->id] ?? null;
                            @endphp
                            @if($result)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $subject->name }}</td>
                                    <td class="px-4 py-4 text-center text-gray-700">{{ $result['ca1_score'] ?? '-' }}</td>
                                    <td class="px-4 py-4 text-center text-gray-700">{{ $result['ca2_score'] ?? '-' }}</td>
                                    <td class="px-4 py-4 text-center text-gray-700">{{ $result['ca3_score'] ?? '-' }}</td>
                                    <td class="px-4 py-4 text-center text-gray-700">{{ $result['ca4_score'] ?? '-' }}</td>
                                    <td class="px-4 py-4 text-center text-gray-700">{{ $result['exam_score'] ?? '-' }}</td>
                                    <td class="px-4 py-4 text-center font-bold text-indigo-600">{{ $result['total_score'] }}</td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $result['grade'][0] == 'A' ? 'bg-green-100 text-green-800' : 
                                               ($result['grade'][0] == 'B' ? 'bg-blue-100 text-blue-800' : 
                                               ($result['grade'][0] == 'C' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                            {{ $result['grade'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">{{ $result['comment'] ?? '-' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>