<div>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
        <!-- Page Title -->
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white">
                <i class="fas fa-user-graduate mr-3"></i>Promote Students
            </h1>
            <p class="mt-3 text-lg text-gray-600 dark:text-gray-400">
                Move students to the next academic year while preserving records
            </p>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 max-w-4xl mx-auto bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        <span class="text-green-800 font-medium">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-green-500 hover:text-green-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 max-w-4xl mx-auto bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                        <span class="text-red-800 font-medium">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        <!-- Tabs -->
        <div class="flex justify-center mb-8">
            <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                <button wire:click="switchView('promote')" class="{{ $currentView === 'promote' ? 'bg-indigo-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100' }} px-8 py-3 font-medium rounded-l-lg transition-colors">
                    <i class="fas fa-arrow-up mr-2"></i>Promote Students
                </button>
                <button wire:click="switchView('history')" class="{{ $currentView === 'history' || $currentView === 'view' ? 'bg-indigo-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100' }} px-8 py-3 font-medium rounded-r-lg transition-colors">
                    <i class="fas fa-history mr-2"></i>Promotion History
                </button>
            </div>
        </div>

        {{-- ====================== PROMOTE VIEW ====================== --}}
        @if($currentView === 'promote')
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Select Classes & Load Students</h2>
                </div>

                <div class="p-8">
                    <!-- Academic Year Selection -->
                    <div class="mb-8 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-6 border-2 border-indigo-200 dark:border-indigo-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-indigo-600"></i>Academic Year Selection
                        </h3>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    From Academic Year <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.live="fromAcademicYear" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700">
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    To Academic Year <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.live="toAcademicYear" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700">
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-10">
                        <!-- From Class -->
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-5 flex items-center gap-3">
                                <span class="w-10 h-10 bg-red-600 text-white rounded-full flex items-center justify-center font-bold">1</span>
                                Current Class
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Class <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model.live="oldClass" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700">
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if($oldSections->count() > 0)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Section (Optional)
                                        </label>
                                        <select wire:model.live="oldSection" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700">
                                            <option value="all">All Sections</option>
                                            @foreach($oldSections as $section)
                                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- To Class -->
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-5 flex items-center gap-3">
                                <span class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">2</span>
                                Promote To
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        New Class <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model.live="newClass" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700">
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        New Section (Optional)
                                    </label>
                                    <select wire:model.live="newSection" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700">
                                        <option value="none">No Section</option>
                                        @foreach($newSections as $section)
                                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-8">
                        <button wire:click="loadStudents" wire:loading.attr="disabled" class="inline-flex items-center gap-3 px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow-md transition-all disabled:opacity-50">
                            <i class="fas fa-search"></i>
                            <span wire:loading.remove wire:target="loadStudents">Load Students</span>
                            <span wire:loading wire:target="loadStudents">Loading...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            @if(!empty($students))
                <div class="mt-8 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div>
                                <h3 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    Students ({{ count($students) }})
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Select students to promote</p>
                            </div>

                            <div class="flex items-center gap-4">
                                <span class="text-lg font-medium text-gray-600 dark:text-gray-400">
                                    Selected: <span class="font-bold text-indigo-600">{{ count($selectedStudents) }}</span>
                                </span>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model.live="selectAll" class="w-5 h-5 text-indigo-600 rounded">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select All</span>
                                </label>
                            </div>
                        </div>

                        <!-- Search & Filter -->
                        <div class="mt-4 grid md:grid-cols-2 gap-4">
                            <input type="text" wire:model.live.debounce.300ms="searchStudent" 
                                   placeholder="Search by name, email, or admission number..." 
                                   class="px-4 py-2 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700">
                            <select wire:model.live="filterStatus" class="px-4 py-2 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700">
                                <option value="all">All Students</option>
                                <option value="ready">Ready to Promote</option>
                                <option value="promoted">Already Promoted</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        <input type="checkbox" wire:model.live="selectAll" class="rounded text-indigo-600">
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Current Class</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Adm. No</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($students as $student)
                                    <tr class="{{ $student['already_promoted'] ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                        <td class="px-6 py-4">
                                            <input type="checkbox" wire:model.live="selectedStudents" value="{{ $student['id'] }}" 
                                                   {{ $student['already_promoted'] ? 'disabled' : '' }}
                                                   class="rounded text-indigo-600 {{ $student['already_promoted'] ? 'opacity-50' : '' }}">
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $student['name'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $student['email'] }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                                {{ $student['original_class'] }}
                                            </span>
                                            @if($student['original_section'])
                                                <span class="ml-1 text-xs text-gray-600">({{ $student['original_section'] }})</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($student['already_promoted'])
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-check-circle text-orange-600"></i>
                                                    <div>
                                                        <span class="block text-sm font-bold text-orange-800">Already Promoted</span>
                                                        <span class="block text-xs text-orange-600">
                                                            → {{ $student['promoted_class'] }}
                                                            @if($student['promoted_section'])({{ $student['promoted_section'] }})@endif
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-sm text-green-600 font-medium">
                                                    <i class="fas fa-check"></i>Ready
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $student['admission_number'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">No students found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-8 py-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 text-center">
                        <button wire:click="promoteStudents" wire:loading.attr="disabled" 
                                {{ empty($selectedStudents) ? 'disabled' : '' }}
                                class="inline-flex items-center gap-3 px-10 py-4 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-semibold rounded-xl shadow-lg transition-all disabled:cursor-not-allowed">
                            <i class="fas fa-arrow-up"></i>
                            <span wire:loading.remove wire:target="promoteStudents">
                                Promote {{ count($selectedStudents) }} Student{{ count($selectedStudents) !== 1 ? 's' : '' }}
                            </span>
                            <span wire:loading wire:target="promoteStudents">Promoting...</span>
                        </button>
                    </div>
                </div>
            @endif
        @endif

        {{-- ====================== HISTORY VIEW ====================== --}}
        @if($currentView === 'history')
            <div class="space-y-6">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Promotion History</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $promotions->count() }} promotion record(s)</p>
                    </div>
                </div>

                @if($promotions->count() > 0)
                    @foreach($promotions as $promotion)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg transition-shadow">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                                <div class="flex-1">
                                    <div class="flex items-center gap-6 mb-4">
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-red-600">{{ $promotion->oldClass->name }}</div>
                                            <div class="text-sm text-gray-500">From</div>
                                        </div>
                                        <i class="fas fa-arrow-right text-3xl text-gray-400"></i>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-green-600">{{ $promotion->newClass->name }}</div>
                                            <div class="text-sm text-gray-500">To</div>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                                        <span><i class="fas fa-users mr-1"></i>{{ count($promotion->students) }} students</span>
                                        <span>•</span>
                                        <span>{{ $promotion->oldSection?->name ?? 'All sections' }} → {{ $promotion->newSection?->name ?? 'No section' }}</span>
                                        <span>•</span>
                                        <span><i class="fas fa-calendar mr-1"></i>{{ $promotion->academicYear->name }}</span>
                                        <span>•</span>
                                        <span>{{ $promotion->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <button wire:click="viewPromotion({{ $promotion->id }})" class="px-5 py-2.5 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded-lg font-medium transition">
                                        <i class="fas fa-eye mr-2"></i>View
                                    </button>
                                    <button wire:click="resetPromotion({{ $promotion->id }})" onclick="return confirm('Undo this promotion?')" class="px-5 py-2.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg font-medium transition">
                                        <i class="fas fa-undo mr-2"></i>Undo
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-2xl shadow">
                        <i class="fas fa-clipboard-list text-gray-300 text-6xl mb-4"></i>
                        <h3 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-3">No Promotion History</h3>
                        <p class="text-lg text-gray-500 dark:text-gray-400">Promotions will appear here once students are promoted.</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- ====================== VIEW PROMOTION ====================== --}}
        @if($currentView === 'view' && $selectedPromotion)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
                <div class="px-8 py-6 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-t-2xl flex justify-between items-center">
                    <h2 class="text-2xl font-bold"><i class="fas fa-info-circle mr-2"></i>Promotion Details</h2>
                    <button wire:click="backToHistory" class="px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </button>
                </div>

                <div class="p-8">
                    <div class="text-center mb-8">
                        <span class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-100 text-indigo-800 rounded-full font-semibold">
                            <i class="fas fa-calendar"></i>{{ $selectedPromotion->academicYear->name }}
                        </span>
                        <p class="mt-2 text-sm text-gray-600">{{ $selectedPromotion->created_at->format('l, F j, Y \a\t g:i A') }}</p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-10 mb-10">
                        <div class="text-center p-8 bg-gradient-to-br from-red-50 to-red-100 rounded-2xl border-2 border-red-200">
                            <div class="w-16 h-16 bg-red-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-school text-red-700 text-2xl"></i>
                            </div>
                            <p class="text-sm text-gray-600 mb-2 font-medium uppercase">From</p>
                            <p class="text-3xl font-bold text-red-700 mb-2">{{ $selectedPromotion->oldClass->name }}</p>
                            <p class="text-lg text-gray-700">{{ $selectedPromotion->oldSection?->name ?? 'All Sections' }}</p>
                        </div>

                        <div class="text-center p-8 bg-gradient-to-br from-green-50 to-green-100 rounded-2xl border-2 border-green-200">
                            <div class="w-16 h-16 bg-green-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-graduation-cap text-green-700 text-2xl"></i>
                            </div>
                            <p class="text-sm text-gray-600 mb-2 font-medium uppercase">To</p>
                            <p class="text-3xl font-bold text-green-700 mb-2">{{ $selectedPromotion->newClass->name }}</p>
                            <p class="text-lg text-gray-700">{{ $selectedPromotion->newSection?->name ?? 'No Section' }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-6 mb-8">
                        <h3 class="text-xl font-semibold mb-4 text-center flex items-center justify-center gap-2">
                            <i class="fas fa-users text-indigo-600"></i>Promoted Students
                            <span class="ml-2 px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-bold">
                                {{ $promotionStudents->count() }}
                            </span>
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($promotionStudents as $student)
                            <div class="p-6 bg-white hover:bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-indigo-300 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                        {{ strtoupper(substr($student->name, 0, 2)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 truncate">{{ $student->name }}</p>
                                        <p class="text-sm text-gray-500 truncate">{{ $student->studentRecord->admission_number }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>