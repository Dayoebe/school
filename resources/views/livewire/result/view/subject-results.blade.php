<div class="space-y-6">
    <!-- Selection Form -->
    <div class="bg-gradient-to-r from-gray-50 to-white rounded-2xl p-6 shadow-sm">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-filter mr-2 text-indigo-600"></i>
            Select Subject to View Results
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                <select wire:model.live="selectedSubject"
                    class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500"
                    @if(!$selectedClass) disabled @endif>
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button wire:click="loadResults" 
                    @if(!$selectedClass || !$selectedSubject) disabled @endif
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-3 px-4 rounded-xl transition-all duration-300 shadow-lg flex items-center justify-center">
                    <i class="fas fa-chart-bar mr-2"></i> Load Results
                </button>
            </div>
        </div>
    </div>

    <!-- Subject Statistics -->
    @if(!empty($subjectStats))
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-xl">
                <p class="text-blue-100 text-sm font-medium">Total Students</p>
                <p class="text-4xl font-bold mt-2">{{ $subjectStats['total_students'] }}</p>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-xl">
                <p class="text-green-100 text-sm font-medium">Highest Score</p>
                <p class="text-4xl font-bold mt-2">{{ $subjectStats['highest_score'] }}</p>
            </div>

            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-6 text-white shadow-xl">
                <p class="text-red-100 text-sm font-medium">Lowest Score</p>
                <p class="text-4xl font-bold mt-2">{{ $subjectStats['lowest_score'] }}</p>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-xl">
                <p class="text-purple-100 text-sm font-medium">Average Score</p>
                <p class="text-4xl font-bold mt-2">{{ $subjectStats['average_score'] }}</p>
            </div>

            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl p-6 text-white shadow-xl">
                <p class="text-yellow-100 text-sm font-medium">Pass Rate</p>
                <p class="text-4xl font-bold mt-2">{{ $subjectStats['pass_rate'] }}%</p>
            </div>
        </div>

        <!-- Grade Distribution Chart -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chart-pie mr-2 text-indigo-600"></i>
                Grade Distribution
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                @foreach($subjectStats['grade_distribution'] as $grade => $count)
                    <div class="text-center p-4 rounded-xl border-2 
                        {{ $grade == 'A' ? 'border-green-200 bg-green-50' : 
                           ($grade == 'B' ? 'border-blue-200 bg-blue-50' : 
                           ($grade == 'C' ? 'border-yellow-200 bg-yellow-50' : 
                           ($grade == 'D' ? 'border-orange-200 bg-orange-50' : 
                           ($grade == 'E' ? 'border-red-200 bg-red-50' : 'border-gray-200 bg-gray-50')))) }}">
                        <p class="text-3xl font-bold 
                            {{ $grade == 'A' ? 'text-green-600' : 
                               ($grade == 'B' ? 'text-blue-600' : 
                               ($grade == 'C' ? 'text-yellow-600' : 
                               ($grade == 'D' ? 'text-orange-600' : 
                               ($grade == 'E' ? 'text-red-600' : 'text-gray-600')))) }}">
                            {{ $count }}
                        </p>
                        <p class="text-sm font-medium text-gray-700 mt-2">Grade {{ $grade }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Subject Results Table -->
    @if($subjectResults->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-book mr-2"></i>
                    {{ $subjects->firstWhere('id', $selectedSubject)?->name }} - Student Performance
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Rank
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Student
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                                CA1 (10)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                                CA2 (10)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                                CA3 (10)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                                CA4 (10)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Exam (60)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider bg-indigo-50">
                                Total (100)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Grade
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Comment
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($subjectResults as $result)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full 
                                        {{ $result->rank == 1 ? 'bg-yellow-100 text-yellow-800' : 
                                           ($result->rank == 2 ? 'bg-gray-100 text-gray-800' : 
                                           ($result->rank == 3 ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800')) }} font-bold">
                                        {{ $result->rank }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-full object-cover border-2 border-indigo-200"
                                            src="{{ $result->student->user->profile_photo_url }}"
                                            alt="{{ $result->student->user->name }}">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $result->student->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $result->student->admission_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center text-gray-700">{{ $result->ca1_score ?? '-' }}</td>
                                <td class="px-4 py-4 text-center text-gray-700">{{ $result->ca2_score ?? '-' }}</td>
                                <td class="px-4 py-4 text-center text-gray-700">{{ $result->ca3_score ?? '-' }}</td>
                                <td class="px-4 py-4 text-center text-gray-700">{{ $result->ca4_score ?? '-' }}</td>
                                <td class="px-4 py-4 text-center font-medium text-gray-900">{{ $result->exam_score ?? '-' }}</td>
                                <td class="px-4 py-4 text-center font-bold text-indigo-600 bg-indigo-50">
                                    {{ $result->total_score }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @php
                                        $grade = $this->calculateGrade($result->total_score);
                                        $gradeClass = match($grade[0]) {
                                            'A' => 'bg-green-100 text-green-800',
                                            'B' => 'bg-blue-100 text-blue-800',
                                            'C' => 'bg-yellow-100 text-yellow-800',
                                            'D', 'E' => 'bg-orange-100 text-orange-800',
                                            default => 'bg-red-100 text-red-800',
                                        };
                                    @endphp
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $gradeClass }}">
                                        {{ $grade }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $result->teacher_comment ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($selectedClass && $selectedSubject)
        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center">
            <i class="fas fa-info-circle text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Click "Load Results" to View</h3>
            <p class="text-gray-500">Click the button above to load subject results</p>
        </div>
    @else
        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center">
            <i class="fas fa-book text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Subject Selected</h3>
            <p class="text-gray-500">Please select a class and subject to view results</p>
        </div>
    @endif
</div>