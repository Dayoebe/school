<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-yellow-500 via-orange-500 to-red-500 rounded-2xl shadow-xl p-6">
        <h2 class="text-2xl font-bold text-white mb-2 flex items-center">
            <i class="fas fa-trophy mr-3"></i>
            Top Performers
        </h2>
        <p class="text-orange-100">Recognizing outstanding academic achievement</p>
    </div>

    <!-- Class Filter & View Type -->
    <div class="bg-white rounded-2xl shadow-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center gap-4">
                <label class="text-sm font-medium text-gray-700">Class:</label>
                <select wire:model.live="selectedClassId"
                    class="flex-1 border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-orange-500">
                    <option value="">All Classes (School-Wide)</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">View:</label>
                <div class="flex-1 flex gap-2">
                    <button wire:click="$set('viewType', 'termly')"
                        class="flex-1 py-2 px-4 rounded-xl font-medium transition-all {{ $viewType === 'termly' ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <i class="fas fa-calendar-day mr-2"></i>Termly
                    </button>
                    <button wire:click="$set('viewType', 'annual')"
                        class="flex-1 py-2 px-4 rounded-xl font-medium transition-all {{ $viewType === 'annual' ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <i class="fas fa-calendar-alt mr-2"></i>Annual
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(!$academicYearId)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
            <i class="fas fa-info-circle text-yellow-600 text-3xl mb-3"></i>
            <p class="text-yellow-800 font-medium">Please select an academic year to view top performers</p>
        </div>
    @elseif($viewType === 'termly' && !$semesterId)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
            <i class="fas fa-info-circle text-yellow-600 text-3xl mb-3"></i>
            <p class="text-yellow-800 font-medium">Please select a term to view termly top performers</p>
        </div>
    @elseif(empty($topPerformers))
        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Results Found</h3>
            <p class="text-gray-500">No valid results available for the selected period</p>
        </div>
    @else
        <!-- Top 3 Overall -->
        @if(!empty($topPerformers['top_3']) && $topPerformers['top_3']->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-crown text-yellow-500 mr-2"></i>
                    Top 3 Best Students (By Average)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($topPerformers['top_3'] as $index => $performer)
                        <div class="border-2 {{ $index === 0 ? 'border-yellow-400 bg-yellow-50' : ($index === 1 ? 'border-gray-400 bg-gray-50' : 'border-orange-400 bg-orange-50') }} rounded-xl p-6 text-center">
                            <div class="text-5xl mb-3">
                                {{ $index === 0 ? '🥇' : ($index === 1 ? '🥈' : '🥉') }}
                            </div>
                            <img src="{{ $performer['student']->user->profile_photo_url }}"
                                class="w-20 h-20 rounded-full mx-auto mb-3 object-cover border-4 {{ $index === 0 ? 'border-yellow-400' : ($index === 1 ? 'border-gray-400' : 'border-orange-400') }}">
                            <h4 class="font-bold text-lg text-gray-900">{{ $performer['student']->user->name }}</h4>
                            <p class="text-sm text-gray-600 mb-2">{{ $performer['student']->myClass->name }}</p>
                            <div class="bg-white rounded-lg p-3 mt-3">
                                <div class="text-3xl font-bold text-blue-600">{{ $performer['average'] }}%</div>
                                <div class="text-xs text-gray-500 mt-1">Average Score</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Other Top Performers -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Highest Total Score -->
            @if(!empty($topPerformers['highest_total']))
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-300 rounded-xl p-6">
                    <h4 class="font-bold text-lg text-blue-900 mb-4 flex items-center">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Highest Total Score
                    </h4>
                    <div class="flex items-center">
                        <img src="{{ $topPerformers['highest_total']['student']->user->profile_photo_url }}"
                            class="w-16 h-16 rounded-full object-cover border-4 border-white mr-4">
                        <div class="flex-1">
                            <div class="font-bold text-gray-900">{{ $topPerformers['highest_total']['student']->user->name }}</div>
                            <div class="text-sm text-gray-600">{{ $topPerformers['highest_total']['student']->myClass->name }}</div>
                            <div class="text-2xl font-bold text-blue-600 mt-2">{{ $topPerformers['highest_total']['total'] }} points</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Most A Grades -->
            @if(!empty($topPerformers['most_as']) && $topPerformers['most_as']['a_grades'] > 0)
                <div class="bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-300 rounded-xl p-6">
                    <h4 class="font-bold text-lg text-green-900 mb-4 flex items-center">
                        <i class="fas fa-star mr-2"></i>
                        Most A Grades (75%+)
                    </h4>
                    <div class="flex items-center">
                        <img src="{{ $topPerformers['most_as']['student']->user->profile_photo_url }}"
                            class="w-16 h-16 rounded-full object-cover border-4 border-white mr-4">
                        <div class="flex-1">
                            <div class="font-bold text-gray-900">{{ $topPerformers['most_as']['student']->user->name }}</div>
                            <div class="text-sm text-gray-600">{{ $topPerformers['most_as']['student']->myClass->name }}</div>
                            <div class="text-2xl font-bold text-green-600 mt-2">{{ $topPerformers['most_as']['a_grades'] }} A's</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Most Consistent -->
            @if(!empty($topPerformers['most_consistent']))
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-300 rounded-xl p-6">
                    <h4 class="font-bold text-lg text-purple-900 mb-4 flex items-center">
                        <i class="fas fa-equals mr-2"></i>
                        Most Consistent Performance
                    </h4>
                    <div class="flex items-center">
                        <img src="{{ $topPerformers['most_consistent']['student']->user->profile_photo_url }}"
                            class="w-16 h-16 rounded-full object-cover border-4 border-white mr-4">
                        <div class="flex-1">
                            <div class="font-bold text-gray-900">{{ $topPerformers['most_consistent']['student']->user->name }}</div>
                            <div class="text-sm text-gray-600">{{ $topPerformers['most_consistent']['student']->myClass->name }}</div>
                            <div class="text-2xl font-bold text-purple-600 mt-2">{{ $topPerformers['most_consistent']['average'] }}% avg</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Best in Each Subject -->
        @if(!empty($topPerformers['best_in_subjects']))
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-medal mr-2 text-indigo-600"></i>
                    Best in Each Subject
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($topPerformers['best_in_subjects'] as $best)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="font-bold text-indigo-600 mb-2">{{ $best['subject']->name }}</div>
                            <div class="flex items-center">
                                <img src="{{ $best['student']->user->profile_photo_url }}"
                                    class="w-10 h-10 rounded-full object-cover mr-3">
                                <div class="flex-1">
                                    <div class="font-semibold text-sm text-gray-900">{{ $best['student']->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $best['student']->myClass->name }}</div>
                                </div>
                                <div class="text-lg font-bold text-indigo-600">{{ $best['score'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>