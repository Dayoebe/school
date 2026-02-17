<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-cyan-700 rounded-2xl shadow-xl p-6">
        <h2 class="text-2xl font-bold text-white mb-2">Class Results Spreadsheet</h2>
        <p class="text-blue-100">Comprehensive overview of class performance</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Class</label>
                <select wire:model.live="selectedClassId"
                    class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">View Type</label>
                <div class="flex space-x-2">
                    <button wire:click="$set('viewType', 'termly')"
                        class="flex-1 py-3 px-4 rounded-xl font-medium transition-all {{ $viewType === 'termly' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <i class="fas fa-calendar-day mr-2"></i>Termly
                    </button>
                    <button wire:click="$set('viewType', 'annual')"
                        class="flex-1 py-3 px-4 rounded-xl font-medium transition-all {{ $viewType === 'annual' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <i class="fas fa-calendar-alt mr-2"></i>Annual
                    </button>
                </div>
            </div>

            <div class="flex items-end space-x-2">
                <button wire:click="exportToExcel"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-xl font-medium transition-all disabled">
                    <i class="fas fa-file-excel mr-2"></i>Excel
                </button>
                <button wire:click="exportToPdf"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-xl font-medium transition-all">
                    <i class="fas fa-file-pdf mr-2"></i>PDF
                </button>
            </div>
        </div>

        @if($viewType === 'termly' && !$semesterId)
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
                <i class="fas fa-info-circle text-yellow-600 text-2xl mb-2"></i>
                <p class="text-yellow-800 font-medium">Please select a term to view results</p>
            </div>
        @endif
    </div>

    @if(!empty($spreadsheetData))
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-4 text-white">
                <p class="text-blue-100 text-sm font-medium">Total Students</p>
                <p class="text-3xl font-bold mt-1">{{ $statistics['total_students'] ?? 0 }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-4 text-white">
                <p class="text-green-100 text-sm font-medium">Highest Score</p>
                <p class="text-3xl font-bold mt-1">{{ $statistics['highest_score'] ?? 0 }}</p>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-4 text-white">
                <p class="text-orange-100 text-sm font-medium">Average Score</p>
                <p class="text-3xl font-bold mt-1">{{ $statistics['average_score'] ?? 0 }}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-4 text-white">
                <p class="text-purple-100 text-sm font-medium">Pass Rate</p>
                <p class="text-3xl font-bold mt-1">{{ $statistics['pass_rate'] ?? 0 }}%</p>
            </div>
        </div>

        <!-- Spreadsheet -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                @if($viewType === 'termly')
                    {{-- Termly Results Table --}}
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-blue-600 to-cyan-700 text-white sticky top-0">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider sticky left-0 bg-blue-600">
                                    Pos
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider sticky left-12 bg-blue-600">
                                    Student Name
                                </th>
                                @foreach($subjects as $subject)
                                    <th class="px-3 py-3 text-center text-xs font-bold uppercase tracking-wider">
                                        {{ $subject->short_name ?? $subject->name }}
                                    </th>
                                @endforeach
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-blue-700">
                                    Total
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-blue-700">
                                    Average
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-blue-700">
                                    Position
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($spreadsheetData as $data)
                                <tr class="hover:bg-blue-50 transition-colors">
                                    <td class="px-3 py-4 text-sm font-bold text-gray-900 sticky left-0 bg-white">
                                        {{ $data['position'] }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-12 bg-white">
                                        {{ $data['student']->user->name }}
                                    </td>
                                    @foreach($subjects as $subject)
                                        @php
                                            $subjectData = $data['subject_scores'][$subject->id] ?? null;
                                            $score = $subjectData['score'] ?? null;
                                            $grade = $subjectData['grade'] ?? '-';
                                            $colorClass = match(true) {
                                                $score >= 75 => 'bg-green-100 text-green-800',
                                                $score >= 60 => 'bg-blue-100 text-blue-800',
                                                $score >= 50 => 'bg-yellow-100 text-yellow-800',
                                                $score >= 40 => 'bg-orange-100 text-orange-800',
                                                $score !== null => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-500'
                                            };
                                        @endphp
                                        <td class="px-3 py-4 text-center">
                                            @if($score !== null)
                                                <div class="flex flex-col items-center">
                                                    <span class="font-bold text-sm {{ $colorClass }} px-2 py-1 rounded">
                                                        {{ $score }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 mt-1">{{ $grade }}</span>
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-4 text-center bg-blue-50">
                                        <span class="font-bold text-blue-900">{{ $data['total_score'] }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-center bg-blue-50">
                                        <span class="font-bold text-blue-900">{{ $data['average'] }}%</span>
                                    </td>
                                    <td class="px-4 py-4 text-center bg-blue-50">
                                        <span class="font-bold text-blue-900">{{ $data['position'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    {{-- Annual Results Table --}}
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white sticky top-0">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider sticky left-0 bg-purple-600">
                                    Pos
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider sticky left-12 bg-purple-600">
                                    Student Name
                                </th>
                                @foreach($semesters as $semester)
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">
                                        {{ $semester->name }}
                                    </th>
                                @endforeach
                                @foreach($subjects as $subject)
                                    <th class="px-3 py-3 text-center text-xs font-bold uppercase tracking-wider bg-purple-700">
                                        {{ $subject->short_name ?? $subject->name }}
                                    </th>
                                @endforeach
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-purple-800">
                                    Grand Total
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-purple-800">
                                    Average %
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-purple-800">
                                    Position
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($spreadsheetData as $data)
                                <tr class="hover:bg-purple-50 transition-colors">
                                    <td class="px-3 py-4 text-sm font-bold text-gray-900 sticky left-0 bg-white">
                                        {{ $data['position'] }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-12 bg-white">
                                        {{ $data['student']->user->name }}
                                    </td>
                                    @foreach($semesters as $semester)
                                        <td class="px-4 py-4 text-center bg-indigo-50">
                                            <span class="font-semibold text-indigo-900">
                                                {{ $data['term_scores'][$semester->id] ?? 0 }}
                                            </span>
                                        </td>
                                    @endforeach
                                    @foreach($subjects as $subject)
                                        @php
                                            $subjectData = $data['subject_scores'][$subject->id] ?? null;
                                            $average = $subjectData['average'] ?? 0;
                                            $grade = $subjectData['grade'] ?? '-';
                                            $colorClass = match(true) {
                                                $average >= 75 => 'bg-green-100 text-green-800',
                                                $average >= 60 => 'bg-blue-100 text-blue-800',
                                                $average >= 50 => 'bg-yellow-100 text-yellow-800',
                                                $average >= 40 => 'bg-orange-100 text-orange-800',
                                                $average > 0 => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-500'
                                            };
                                        @endphp
                                        <td class="px-3 py-4 text-center">
                                            <div class="flex flex-col items-center">
                                                <span class="text-xs font-semibold {{ $colorClass }} px-2 py-1 rounded">
                                                    {{ $average }}
                                                </span>
                                                <span class="text-xs text-gray-500 mt-1">{{ $grade }}</span>
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-4 text-center bg-purple-50">
                                        <span class="font-bold text-purple-900">{{ $data['grand_total'] }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-center bg-purple-50">
                                        <span class="font-bold text-purple-900">{{ $data['annual_average'] }}%</span>
                                    </td>
                                    <td class="px-4 py-4 text-center bg-purple-50">
                                        <span class="font-bold text-purple-900">{{ $data['position'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <!-- Subject Performance Analysis (Termly Only) -->
        @if($viewType === 'termly' && !empty($statistics['subject_stats']))
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                    Subject Performance Analysis
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($statistics['subject_stats'] as $subjectId => $stats)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                            <h4 class="font-bold text-gray-800 mb-3">{{ $stats['name'] }}</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Highest:</span>
                                    <span class="font-semibold text-green-600">{{ $stats['highest'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Average:</span>
                                    <span class="font-semibold text-blue-600">{{ $stats['average'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Lowest:</span>
                                    <span class="font-semibold text-red-600">{{ $stats['lowest'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @elseif($selectedClassId && ($viewType === 'annual' || $semesterId))
        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center">
            <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Results Found</h3>
            <p class="text-gray-500">No results available for the selected period</p>
        </div>
    @endif
    
    <style>
        table {
            border-collapse: separate;
            border-spacing: 0;
    }
    
    .sticky {
        position: sticky;
        z-index: 10;
    }
    
    thead th.sticky {
        z-index: 20;
    }
</style>
            </div>