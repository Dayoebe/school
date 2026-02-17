<div class="space-y-6">
    @if(!$viewingHistory)
        <!-- Student Selection -->
        <div class="bg-gradient-to-r from-gray-50 to-white rounded-2xl p-6 shadow-sm">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-search mr-2 text-indigo-600"></i>
                Select Student
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

        <!-- Student List -->
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
                                    Class
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($students as $student)
                            @if($student->user)
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
                                    <td class="px-6 py-4 text-center text-sm text-gray-700">
                                        {{ $student->myClass->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button wire:click="viewHistory({{ $student->id }})"
                                            class="text-white bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 px-4 py-2 rounded-xl transition-all duration-300 flex items-center shadow ml-auto">
                                            <i class="fas fa-history mr-2"></i> View History
                                        </button>
                                    </td>
                                </tr>
                            @endif
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
                <p class="text-gray-500">No students with results in this class</p>
            </div>
        @endif

    @else
        <!-- Student History Detail View -->
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-700 rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between">
                    <button wire:click="backToList"
                        class="text-white hover:text-indigo-100 font-medium transition-colors flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to List
                    </button>
                    <button onclick="window.print()"
                        class="bg-white text-indigo-600 hover:bg-indigo-50 px-6 py-3 rounded-xl font-medium flex items-center shadow-lg transition-all">
                        <i class="fas fa-print mr-2"></i> Print History
                    </button>
                </div>

                <div class="flex items-center mt-6">
                    <img src="{{ $studentRecord->user->profile_photo_url }}" 
                        alt="{{ $studentRecord->user->name }}"
                        class="h-20 w-20 rounded-full object-cover border-4 border-white/40 shadow-xl">
                    <div class="ml-5 text-white">
                        <h1 class="text-2xl md:text-3xl font-bold">{{ $studentRecord->user->name }}</h1>
                        <p class="text-indigo-100 mt-1">{{ $studentRecord->myClass->name }} • {{ $studentRecord->admission_number }}</p>
                    </div>
                </div>
            </div>

            <!-- Overall Statistics -->
            @if(!empty($overallStats))
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-xl">
                        <p class="text-blue-100 text-sm font-medium">Overall Average</p>
                        <p class="text-4xl font-bold mt-2">{{ $overallStats['average_score'] }}%</p>
                    </div>

                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-xl">
                        <p class="text-green-100 text-sm font-medium">Best Subject</p>
                        <p class="text-xl font-bold mt-2">{{ $overallStats['best_subject'] }}</p>
                        <p class="text-sm text-green-100 mt-1">{{ $overallStats['best_subject_avg'] }}% avg</p>
                    </div>

                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-6 text-white shadow-xl">
                        <p class="text-red-100 text-sm font-medium">Needs Improvement</p>
                        <p class="text-xl font-bold mt-2">{{ $overallStats['worst_subject'] }}</p>
                        <p class="text-sm text-red-100 mt-1">{{ $overallStats['worst_subject_avg'] }}% avg</p>
                    </div>

                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-xl">
                        <p class="text-purple-100 text-sm font-medium">Terms Completed</p>
                        <p class="text-4xl font-bold mt-2">{{ $overallStats['total_terms'] }}</p>
                    </div>
                </div>
            @endif

            <!-- Academic History Timeline -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-history mr-2"></i>
                        Academic Performance Timeline
                    </h3>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach($historyData as $yearData)
                        <div x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }" class="p-6">
                            <button @click="expanded = !expanded" 
                                class="w-full flex justify-between items-center group">
                                <div class="flex items-center">
                                    <div class="bg-indigo-100 text-indigo-800 w-10 h-10 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="text-left">
                                        <h4 class="text-lg font-bold text-gray-800 group-hover:text-indigo-700">
                                            {{ $yearData['year']->name }}
                                        </h4>
                                        <p class="text-sm text-gray-500">{{ count($yearData['semesters']) }} terms</p>
                                    </div>
                                </div>
                                <i :class="expanded ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" 
                                    class="text-gray-500 group-hover:text-indigo-600"></i>
                            </button>

                            <div x-show="expanded" x-collapse class="mt-6 space-y-4">
                                @foreach($yearData['semesters'] as $semesterData)
                                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                        <div class="flex justify-between items-start mb-4">
                                            <div>
                                                <h5 class="font-semibold text-gray-800">{{ $semesterData['semester']->name }}</h5>
                                                <p class="text-sm text-gray-600">{{ $semesterData['subjects_count'] }} subjects • {{ $semesterData['percentage'] }}% average</p>
                                            </div>
                                            <span class="px-3 py-1 rounded-full text-sm font-bold
                                                {{ $semesterData['percentage'] >= 75 ? 'bg-green-100 text-green-800' : 
                                                   ($semesterData['percentage'] >= 60 ? 'bg-blue-100 text-blue-800' : 
                                                   ($semesterData['percentage'] >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                                {{ $semesterData['percentage'] }}%
                                            </span>
                                        </div>

                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-sm">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left font-medium text-gray-700">Subject</th>
                                                        <th class="px-4 py-2 text-center font-medium text-gray-700">Score</th>
                                                        <th class="px-4 py-2 text-center font-medium text-gray-700">Grade</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @foreach($semesterData['results'] as $result)
                                                        <tr class="hover:bg-gray-50">
                                                            <td class="px-4 py-2 font-medium text-gray-900">{{ $result->subject->name }}</td>
                                                            <td class="px-4 py-2 text-center font-bold text-indigo-600">{{ $result->total_score }}</td>
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
                                                                        'A' => 'bg-green-100 text-green-800',
                                                                        'B' => 'bg-blue-100 text-blue-800',
                                                                        'C' => 'bg-yellow-100 text-yellow-800',
                                                                        default => 'bg-red-100 text-red-800',
                                                                    };
                                                                @endphp
                                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $gradeClass }}">
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
        .bg-gradient-to-r, .bg-gradient-to-br { -webkit-print-color-adjust: exact; color-adjust: exact; }
    }
</style>
@endpush