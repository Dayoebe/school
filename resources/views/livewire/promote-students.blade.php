<div>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
    
            <!-- Page Title -->
            <div class="mb-10 text-center">
                <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white">
                    Promote Students
                </h1>
                <p class="mt-3 text-lg text-gray-600 dark:text-gray-400">
                    Move students to the next academic level while preserving historical records
                </p>
            </div>
    
            <!-- Flash Messages -->
            @if (session()->has('success'))
                <div class="mb-6 max-w-2xl mx-auto bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-6 py-4 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button wire:click="$set('flash.success', null)" class="text-green-600 hover:text-green-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
    
            @if (session()->has('error'))
                <div class="mb-6 max-w-2xl mx-auto bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-6 py-4 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if (session()->has('info'))
                <div class="mb-6 max-w-2xl mx-auto bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 px-6 py-4 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-medium">{{ session('info') }}</span>
                    </div>
                </div>
            @endif
    
            <!-- Tabs -->
            <div class="flex justify-center mb-10">
                <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                    <button wire:click="switchView('promote')"
                            class="{{ $currentView === 'promote' ? 'bg-indigo-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} 
                                   px-8 py-3 font-medium rounded-l-lg transition-colors">
                        Promote Students
                    </button>
                    <button wire:click="switchView('history')"
                            class="{{ $currentView === 'history' || $currentView === 'view' ? 'bg-indigo-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} 
                                   px-8 py-3 font-medium rounded-r-lg transition-colors">
                        Promotion History
                    </button>
                </div>
            </div>
    
            {{-- ====================== PROMOTE VIEW ====================== --}}
            @if($currentView === 'promote')
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Select Classes & Load Students</h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Previous academic year records will be preserved</p>
                    </div>
    
                    <div class="p-8">
                        <!-- Academic Year Selection -->
                        <div class="mb-10 max-w-4xl mx-auto">
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-6 border-2 border-indigo-200 dark:border-indigo-800">
                                <h3 class="text-lg font-semibold text-indigo-900 dark:text-indigo-100 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Academic Year Selection
                                </h3>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            From Academic Year <span class="text-red-500">*</span>
                                        </label>
                                        <select wire:model.live="fromAcademicYear" 
                                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700">
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            To Academic Year <span class="text-red-500">*</span>
                                        </label>
                                        <select wire:model.live="toAcademicYear" 
                                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700">
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-10 max-w-4xl mx-auto">
                            <!-- From Class -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-5 flex items-center gap-3">
                                    <span class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center text-red-600 font-bold">
                                        1
                                    </span>
                                    Current Class
                                </h3>
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Class <span class="text-red-500">*</span>
                                        </label>
                                        <select wire:model.live="oldClass" 
                                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700">
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
    
                            <!-- To Class -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-5 flex items-center gap-3">
                                    <span class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center text-green-600 font-bold">
                                        2
                                    </span>
                                    Promote To
                                </h3>
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            New Class <span class="text-red-500">*</span>
                                        </label>
                                        <select wire:model.live="newClass" 
                                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700">
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            New Section (Optional)
                                        </label>
                                        <select wire:model.live="newSection" 
                                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700">
                                            <option value="none">No Section</option>
                                            @foreach($newSections as $section)
                                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <div class="text-center mt-10">
                            <button wire:click="loadStudents" 
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-3 px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span wire:loading.remove wire:target="loadStudents">
                                    Load Students
                                </span>
                                <span wire:loading wire:target="loadStudents" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Loading...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
    
                <!-- Students Table -->
                <div class="mt-10">
                    @if(empty($students))
                        <!-- Empty State -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
                            <div class="p-16 text-center">
                                <div class="mx-auto w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-6">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                    No Students Loaded
                                </h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                                    Click "Load Students" to view students from the selected class and academic year.
                                </p>
                            </div>
                        </div>
                    @else
                        <!-- Students Table -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                    <div>
                                        <h3 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                            Students ({{ count($students) }})
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            Showing {{ count($filteredStudents) }} student(s)
                                        </p>
                                    </div>
                                    
                                    <div class="flex flex-wrap items-center gap-4">
                                        <!-- Section Filter -->
                                        @if($oldSections->count() > 0)
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Filter by Section</label>
                                                <select wire:model.live="sectionFilter" 
                                                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 text-sm">
                                                    <option value="all">All Sections ({{ count($students) }})</option>
                                                    @foreach($oldSections as $section)
                                                        @php
                                                            $count = count(array_filter($students, fn($s) => $s['section_id'] == $section->id));
                                                        @endphp
                                                        <option value="{{ $section->id }}">{{ $section->name }} ({{ $count }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <div class="flex items-center gap-4">
                                            <span class="text-lg font-medium text-gray-600 dark:text-gray-400">
                                                Selected: <span class="font-bold text-indigo-600">{{ count($selectedStudents) }}</span>
                                            </span>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" wire:model.live="selectAll" 
                                                       class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select All</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                <input type="checkbox" wire:model.live="selectAll" class="rounded text-indigo-600 focus:ring-indigo-500">
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Adm. No</th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Class</th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Section</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @forelse($filteredStudents as $student)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                <td class="px-6 py-4 text-center">
                                                    <input type="checkbox" wire:model.live="selectedStudents" value="{{ $student['id'] }}" 
                                                           class="rounded text-indigo-600 focus:ring-indigo-500">
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $student['admission_number'] }}</td>
                                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $student['name'] }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-300 rounded-full text-xs font-medium">
                                                        {{ $student['class'] }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $student['section'] ?? '—' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                                    No students in this section
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="px-8 py-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 text-center">
                                <button wire:click="promoteStudents"
                                        wire:loading.attr="disabled"
                                        {{ empty($selectedStudents) ? 'disabled' : '' }}
                                        class="inline-flex items-center gap-3 px-10 py-4 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-semibold rounded-xl shadow transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span wire:loading.remove wire:target="promoteStudents">
                                        Promote {{ count($selectedStudents) }} Student{{ count($selectedStudents) != 1 ? 's' : '' }}
                                    </span>
                                    <span wire:loading wire:target="promoteStudents">
                                        Promoting...
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
    
            {{-- ====================== HISTORY VIEW ====================== --}}
            @if($currentView === 'history')
                <div class="space-y-6">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Promotion History</h2>
    
                    @if($promotions->count() > 0)
                        @foreach($promotions as $promotion)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6">
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-6 mb-4">
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-red-600">{{ $promotion->oldClass->name }}</div>
                                                <div class="text-sm text-gray-500">From</div>
                                            </div>
                                            <div class="text-3xl text-gray-400">→</div>
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-green-600">{{ $promotion->newClass->name }}</div>
                                                <div class="text-sm text-gray-500">To</div>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                                {{ count($promotion->students) }} students
                                            </span>
                                            <span>•</span>
                                            <span>{{ $promotion->oldSection?->name ?? 'All sections' }} → {{ $promotion->newSection?->name ?? 'No section' }}</span>
                                            <span>•</span>
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $promotion->academicYear->name }}
                                            </span>
                                            <span>•</span>
                                            <span>{{ $promotion->created_at->format('d M Y, h:i A') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-3">
                                        <button wire:click="viewPromotion({{ $promotion->id }})"
                                                class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">
                                            View Details
                                        </button>
                                        <button wire:click="resetPromotion({{ $promotion->id }})"
                                                onclick="return confirm('Are you sure you want to undo this promotion? This will remove students from the new academic year.')"
                                                class="px-5 py-2.5 bg-red-100 hover:bg-red-200 dark:bg-red-900/50 dark:hover:bg-red-900/70 text-red-700 dark:text-red-400 rounded-lg font-medium transition-colors">
                                            Undo Promotion
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700">
                            <div class="mx-auto w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-6">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                No Promotion History
                            </h3>
                            <p class="text-lg text-gray-500 dark:text-gray-400">
                                Promotions will appear here once students are promoted.
                            </p>
                        </div>
                    @endif
                </div>
            @endif
    
            {{-- ====================== VIEW SINGLE PROMOTION ====================== --}}
            @if($currentView === 'view' && $selectedPromotion)
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-8 py-6 bg-gradient-to-r from-indigo-500 to-purple-600 text-white border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h2 class="text-2xl font-bold">Promotion Details</h2>
                        <button wire:click="backToHistory" class="px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg font-medium transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to History
                        </button>
                    </div>
                    
                    <div class="p-8">
                        <!-- Academic Year Badge -->
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-300 rounded-full">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="font-semibold text-lg">{{ $selectedPromotion->academicYear->name }}</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Promoted on {{ $selectedPromotion->created_at->format('l, F j, Y \a\t g:i A') }}
                            </p>
                        </div>

                        <div class="grid md:grid-cols-2 gap-10 mb-10">
                            <div class="text-center p-8 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/10 rounded-2xl border-2 border-red-200 dark:border-red-800">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-200 dark:bg-red-800 rounded-full mb-4">
                                    <svg class="w-8 h-8 text-red-700 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 font-medium uppercase tracking-wide">From</p>
                                <p class="text-3xl font-bold text-red-700 dark:text-red-400 mb-2">{{ $selectedPromotion->oldClass->name }}</p>
                                <p class="text-lg text-gray-700 dark:text-gray-300">
                                    {{ $selectedPromotion->oldSection?->name ?? 'All Sections' }}
                                </p>
                            </div>
                            
                            <div class="text-center p-8 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/10 rounded-2xl border-2 border-green-200 dark:border-green-800">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-200 dark:bg-green-800 rounded-full mb-4">
                                    <svg class="w-8 h-8 text-green-700 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 font-medium uppercase tracking-wide">To</p>
                                <p class="text-3xl font-bold text-green-700 dark:text-green-400 mb-2">{{ $selectedPromotion->newClass->name }}</p>
                                <p class="text-lg text-gray-700 dark:text-gray-300">
                                    {{ $selectedPromotion->newSection?->name ?? 'No Section Assigned' }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-6 mb-8">
                            <h3 class="text-xl font-semibold mb-4 text-center flex items-center justify-center gap-2">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Promoted Students
                                <span class="ml-2 px-3 py-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-300 rounded-full text-sm font-bold">
                                    {{ $promotionStudents->count() }}
                                </span>
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($promotionStudents as $student)
                                <div class="group p-6 bg-white dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-900/70 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 transition-all duration-200 shadow-sm hover:shadow-md">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                            {{ strtoupper(substr($student->name, 0, 2)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                {{ $student->name }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                                {{ $student->studentRecord->admission_number }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($promotionStudents->isEmpty())
                            <div class="text-center py-12">
                                <p class="text-gray-500 dark:text-gray-400">No students found for this promotion</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
    
        </div>
    </div>
</div>