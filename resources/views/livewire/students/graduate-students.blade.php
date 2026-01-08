<div>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
        <!-- Page Title -->
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white">
                <i class="fas fa-graduation-cap mr-3"></i>Graduate Students
            </h1>
            <p class="mt-3 text-lg text-gray-600 dark:text-gray-400">
                Manage student graduations and alumni records
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
                <button wire:click="switchView('graduate')" class="{{ $currentView === 'graduate' ? 'bg-green-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100' }} px-8 py-3 font-medium rounded-l-lg transition-colors">
                    <i class="fas fa-graduation-cap mr-2"></i>Graduate Students
                </button>
                <button wire:click="switchView('history')" class="{{ $currentView === 'history' || $currentView === 'view' ? 'bg-green-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100' }} px-8 py-3 font-medium rounded-r-lg transition-colors">
                    <i class="fas fa-history mr-2"></i>Graduation History
                </button>
            </div>
        </div>

        {{-- ====================== GRADUATE VIEW ====================== --}}
        @if($currentView === 'graduate')
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Select Class & Load Students</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Students will be moved to Alumni upon graduation</p>
                </div>

                <div class="p-8">
                    <div class="grid md:grid-cols-4 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Class <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="graduateClass" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700">
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Section (Optional)
                            </label>
                            <select wire:model.live="graduateSection" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700">
                                <option value="">All Sections</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Academic Year <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="academicYearId" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Graduation Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model="graduationDate" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700">
                        </div>
                    </div>

                    <div class="text-center mb-8">
                        <button wire:click="loadStudentsToGraduate" wire:loading.attr="disabled" class="inline-flex items-center gap-3 px-8 py-4 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl shadow-md transition-all disabled:opacity-50">
                            <i class="fas fa-search"></i>
                            <span wire:loading.remove wire:target="loadStudentsToGraduate">Load Students</span>
                            <span wire:loading wire:target="loadStudentsToGraduate">Loading...</span>
                        </button>
                    </div>

                    @if(count($studentsToGraduate) > 0)
                        <div class="mb-6">
                            <div class="flex flex-wrap gap-3 mb-4">
                                <button wire:click="setAllGraduate(true)" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                                    <i class="fas fa-check-double mr-2"></i>Select All to Graduate
                                </button>
                                <button wire:click="setAllGraduate(false)" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                                    <i class="fas fa-times-circle mr-2"></i>Deselect All
                                </button>
                            </div>

                            <!-- Search & Filter -->
                            <div class="grid md:grid-cols-2 gap-4 mb-4">
                                <input type="text" wire:model.live.debounce.300ms="searchStudent" 
                                       placeholder="Search by name, email, or admission number..." 
                                       class="px-4 py-2 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700">
                                <select wire:model.live="filterGradStatus" class="px-4 py-2 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700">
                                    <option value="all">All Students</option>
                                    <option value="eligible">Eligible to Graduate</option>
                                    <option value="graduated">Already Graduated</option>
                                </select>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                            <table class="min-w-full">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Student</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Admission No</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Action</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Certificate No</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($studentsToGraduate as $student)
                                        <tr class="{{ $student['already_graduated'] ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'hover:bg-green-50 dark:hover:bg-green-900/10' }}">
                                            <td class="px-6 py-4">
                                                <div class="font-semibold text-gray-900 dark:text-white">{{ $student['name'] }}</div>
                                                <div class="text-xs text-gray-500">{{ $student['email'] }}</div>
                                            </td>
                                            <td class="px-6 py-4">{{ $student['admission_number'] }}</td>
                                            <td class="px-6 py-4">
                                                @if($student['already_graduated'])
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-check-circle mr-1"></i>Already Graduated
                                                    </span>
                                                @else
                                                    <select wire:model="graduationDecisions.{{ $student['id'] }}" class="px-4 py-2 border-2 border-gray-300 rounded-lg text-sm">
                                                        <option value="1">Graduate</option>
                                                        <option value="0">Don't Graduate</option>
                                                    </select>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <input type="text" wire:model="certificateNumbers.{{ $student['id'] }}" 
                                                       placeholder="Auto-generated" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm dark:bg-gray-700">
                                            </td>
                                            <td class="px-6 py-4">
                                                <input type="text" wire:model="remarks.{{ $student['id'] }}" 
                                                       placeholder="Optional remarks" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm dark:bg-gray-700">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">No students found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end">
                            <button wire:click="graduateStudents" wire:loading.attr="disabled" 
                                    class="px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-lg shadow-lg text-lg transition-all disabled:opacity-50">
                                <i class="fas fa-graduation-cap mr-2"></i>
                                <span wire:loading.remove wire:target="graduateStudents">Graduate Selected Students</span>
                                <span wire:loading wire:target="graduateStudents">Graduating...</span>
                            </button>
                        </div>
                    @elseif($graduateClass)
                        <div class="text-center py-12 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                            <p class="text-lg text-gray-500 dark:text-gray-400">No students found in selected class/section</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ====================== HISTORY VIEW ====================== --}}
        @if($currentView === 'history')
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Graduation History</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $graduations->count() }} graduation record(s)</p>
                    </div>
                </div>

                @if($graduations->count() > 0)
                    @foreach($graduations as $graduation)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg transition-shadow">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                                <div class="flex-1">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                            {{ strtoupper(substr($graduation->studentRecord->user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $graduation->studentRecord->user->name }}</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $graduation->certificate_number }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                                        <span><i class="fas fa-school mr-1"></i>{{ $graduation->graduationClass->name }}</span>
                                        @if($graduation->graduationSection)
                                            <span>• {{ $graduation->graduationSection->name }}</span>
                                        @endif
                                        <span>• <i class="fas fa-calendar mr-1"></i>{{ $graduation->academicYear->name }}</span>
                                        <span>• {{ $graduation->graduation_date->format('M d, Y') }}</span>
                                    </div>
                                    @if($graduation->remarks)
                                        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 italic">Remarks: {{ $graduation->remarks }}</p>
                                    @endif
                                </div>
                                <div class="flex gap-3">
                                    <button wire:click="reverseGraduation({{ $graduation->id }})" 
                                            onclick="return confirm('Reverse this graduation?')" 
                                            class="px-5 py-2.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg font-medium transition">
                                        <i class="fas fa-undo mr-2"></i>Reverse
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-2xl shadow">
                        <i class="fas fa-graduation-cap text-gray-300 text-6xl mb-4"></i>
                        <h3 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-3">No Graduation History</h3>
                        <p class="text-lg text-gray-500 dark:text-gray-400">Graduations will appear here once students are graduated.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>