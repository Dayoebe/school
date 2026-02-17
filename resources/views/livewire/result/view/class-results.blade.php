<div class="space-y-6">
    <!-- Selection Form -->
    <div class="bg-gradient-to-r from-gray-50 to-white rounded-2xl p-6 shadow-sm">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-filter mr-2 text-indigo-600"></i>
            Select Class to View Results
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Section (Optional)</label>
                <select wire:model.live="selectedSection"
                    class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Sections</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button wire:click="loadResults" 
                    @if(!$selectedClass) disabled @endif
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-3 px-4 rounded-xl transition-all duration-300 shadow-lg flex items-center justify-center">
                    <i class="fas fa-eye mr-2"></i> Load Results
                </button>
            </div>
        </div>
    </div>

    <!-- Class Results Table -->
    @if($classResults->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header with Print Button -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-4 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        {{ $classes->firstWhere('id', $selectedClass)?->name }} Results
                    </h3>
                    <p class="text-indigo-100 text-sm mt-1">{{ $classResults->count() }} Students</p>
                </div>
                <a href="{{ route('result.print-class', [
                    'academicYearId' => $academicYearId,
                    'semesterId' => $semesterId,
                    'classId' => $selectedClass,
                ]) }}" target="_blank"
                    class="bg-white text-indigo-600 hover:bg-indigo-50 px-6 py-3 rounded-xl font-medium flex items-center shadow-lg transition-all">
                    <i class="fas fa-print mr-2"></i> Print All Results
                </a>
            </div>

            <!-- Performance Statistics -->
            <div class="bg-gray-50 px-6 py-4 border-b grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-sm text-gray-600">Class Average</p>
                    <p class="text-2xl font-bold text-indigo-600">
                        {{ $classResults->avg('average_score') ? round($classResults->avg('average_score'), 1) : 0 }}%
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Highest Score</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ $classResults->max('average_score') ? round($classResults->max('average_score'), 1) : 0 }}%
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Lowest Score</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ $classResults->min('average_score') ? round($classResults->min('average_score'), 1) : 0 }}%
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Pass Rate</p>
                    <p class="text-2xl font-bold text-purple-600">
                        {{ $classResults->count() > 0 ? round(($classResults->filter(fn($s) => $s->average_score >= 50)->count() / $classResults->count()) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>

            <!-- Scrollable Results Table -->
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider sticky left-0 bg-gray-50 z-20">
                                Rank
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider sticky left-16 bg-gray-50 z-20">
                                Student
                            </th>
                            @foreach($subjects as $subject)
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    {{ $subject->name }}
                                </th>
                            @endforeach
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider bg-indigo-50">
                                Total
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider bg-purple-50">
                                Average
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($classResults as $student)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-indigo-600 sticky left-0 bg-white">
                                    {{ $student->position }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap sticky left-16 bg-white">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-full object-cover border-2 border-indigo-200"
                                            src="{{ $student->user->profile_photo_url }}"
                                            alt="{{ $student->user->name }}">
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $student->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $student->admission_number }}</div>
                                        </div>
                                    </div>
                                </td>

                                @foreach($subjects as $subject)
                                    @php
                                        $result = $student->subject_results[$subject->id] ?? null;
                                    @endphp
                                    <td class="px-4 py-4 text-center">
                                        @if($result)
                                            <div class="flex flex-col items-center">
                                                <span class="font-bold text-gray-900">{{ $result['total_score'] }}</span>
                                                <span class="text-xs px-2 py-0.5 rounded-full mt-1
                                                    {{ $result['grade'][0] == 'A' ? 'bg-green-100 text-green-800' : 
                                                       ($result['grade'][0] == 'B' ? 'bg-blue-100 text-blue-800' : 
                                                       ($result['grade'][0] == 'C' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                                    {{ $result['grade'] }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="px-4 py-4 text-center font-bold text-indigo-600 bg-indigo-50">
                                    {{ $student->total_score }}
                                </td>

                                <td class="px-4 py-4 text-center bg-purple-50">
                                    <span class="px-3 py-1 rounded-full font-bold text-sm
                                        {{ $student->average_score >= 75 ? 'bg-green-100 text-green-800' : 
                                           ($student->average_score >= 60 ? 'bg-blue-100 text-blue-800' : 
                                           ($student->average_score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                        {{ $student->average_score }}%
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('result.print', [
                                            'student' => $student->id,
                                            'academicYearId' => $academicYearId,
                                            'semesterId' => $semesterId,
                                        ]) }}" target="_blank"
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                            title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('result.print', [
                                            'student' => $student->id,
                                            'academicYearId' => $academicYearId,
                                            'semesterId' => $semesterId,
                                        ]) }}" target="_blank"
                                            class="text-green-600 hover:text-green-900 transition-colors"
                                            title="Print Report">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($selectedClass)
        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center">
            <i class="fas fa-info-circle text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Click "Load Results" to View</h3>
            <p class="text-gray-500">Select your class and click the button above to load class results</p>
        </div>
    @else
        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center">
            <i class="fas fa-users text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Class Selected</h3>
            <p class="text-gray-500">Please select a class to view results</p>
        </div>
    @endif
</div>

@push('styles')
<style>
    /* Sticky column shadows for better visibility */
    .sticky[class*="left-0"],
    .sticky[class*="left-16"] {
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
    }
</style>
@endpush