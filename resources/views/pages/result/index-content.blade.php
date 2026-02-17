<div>
    <div x-data="{
        activeTab: 'actions',
        loading: false,
        showBulkModal: @entangle('bulkEditMode').live,
        showSubjectModal: @entangle('showSubjectModal').live,
        showSuccess: false,
        successMessage: ''
    }" x-init="Livewire.on('showBulkModal', () => { showBulkModal = true });
    Livewire.on('hideBulkModal', () => { showBulkModal = false });
    Livewire.on('showSuccess', (message) => {
        successMessage = message;
        showSuccess = true;
        setTimeout(() => { showSuccess = false }, 3000);
    });" class="space-y-6">

        <!-- Header Section -->
        <div
            class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-xl p-6 transform transition duration-500 hover:scale-[1.01]">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold text-white flex items-center animate-fade-in">
                        <i class="fas fa-chart-line mr-3 text-yellow-300"></i>
                        Results Management
                    </h1>
                    <p class="text-blue-100 mt-2 text-lg">Upload and manage student results for academic periods</p>
                </div>
                <div class="mt-4 md:mt-0 bg-white/10 backdrop-blur-sm rounded-xl px-4 py-2">
                    <div class="flex items-center text-sm text-blue-50">
                        <span class="mr-2">Current Selection:</span>
                        @if ($academicYearId && $semesterId)
                            <span class="font-medium text-yellow-300">
                                {{ \App\Models\AcademicYear::find($academicYearId)?->name }} -
                                {{ \App\Models\Semester::find($semesterId)?->name }}
                            </span>
                            @if ($selectedClass)
                                <span class="ml-2">• {{ \App\Models\MyClass::find($selectedClass)?->name }}</span>
                            @endif
                        @else
                            <span class="text-blue-200">Not selected</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Period Selector -->
        <div class="bg-white rounded-2xl shadow-xl p-6 animate-slide-up">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-calendar-alt mr-2 text-indigo-600"></i>
                Select Academic Period
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Academic Year Selector -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-calendar mr-1 text-indigo-500"></i> Academic Year
                    </label>
                    <select wire:model.live="academicYearId"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
                        <option value="">Select Year</option>
                        @foreach (\App\Models\AcademicYear::orderBy('start_year', 'desc')->get() as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Term Selector -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-calendar-week mr-1 text-indigo-500"></i> Term
                    </label>
                    <select wire:model.live="semesterId"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm"
                        @if (!$academicYearId) disabled @endif>
                        <option value="">Select Term</option>
                        @if ($academicYearId)
                            @foreach (\App\Models\Semester::where('academic_year_id', $academicYearId)->get() as $term)
                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Class Selector -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-graduation-cap mr-1 text-indigo-500"></i> Class
                    </label>
                    <select wire:model.live="selectedClass"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
                        <option value="">Select Class</option>
                        @foreach (\App\Models\MyClass::all() as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Confirmation Button -->
                <div class="flex items-end">
                    <button wire:click="goToAcademicOverview"
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white font-medium py-3 px-4 rounded-xl transition-all duration-300 transform hover:scale-[1.02] shadow-lg flex items-center justify-center">
                        <i class="fas fa-check-circle mr-2"></i> Confirm Selection
                    </button>
                </div>
            </div>
        </div>

        <!-- Action Tabs -->
        <div class="bg-white rounded-2xl shadow-xl p-6 animate-slide-up">
            <div class="flex border-b border-gray-200 mb-6">
                <button @click="activeTab = 'actions'" :class="activeTab === 'actions' ? 'border-b-2 border-indigo-500 text-indigo-600' :
                        'text-gray-500 hover:text-gray-700'" class="px-4 py-2 font-medium flex items-center">
                    <i class="fas fa-bolt mr-2"></i> Quick Actions
                </button>
                <button @click="activeTab = 'search'" :class="activeTab === 'search' ? 'border-b-2 border-indigo-500 text-indigo-600' :
                        'text-gray-500 hover:text-gray-700'" class="px-4 py-2 font-medium flex items-center">
                    <i class="fas fa-search mr-2"></i> Find Students
                </button>
            </div>

            <!-- Quick Actions Tab -->
            <div x-show="activeTab === 'actions'" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Bulk Upload -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-100 rounded-2xl p-5 transition-all duration-300 hover:shadow-lg hover:border-indigo-300 cursor-pointer"
                        @if ($academicYearId && $semesterId && $selectedClass) @click="showSubjectModal = true" @else
                            title="Please select academic year, term, and class first" class="opacity-70 cursor-not-allowed"
                        @endif>
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-3 rounded-xl mr-4">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-800">Bulk Upload</h3>
                                <p class="text-sm text-gray-600">Upload results by subject</p>
                                @if (!$academicYearId || !$semesterId)
                                    <p class="text-xs text-red-500 mt-1">Select year & term and first</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="h-1 w-full bg-blue-200 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full w-2/3"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Select subject to begin</p>
                        </div>
                    </div>

                    <!-- Individual Upload -->
                    <div class="bg-gradient-to-br from-green-50 to-teal-50 border-2 border-green-100 rounded-2xl p-5 transition-all duration-300 hover:shadow-lg hover:border-teal-300 cursor-pointer"
                        @if ($academicYearId && $semesterId && $selectedClass)
                        @click="activeTab = 'search'; $wire.showFilteredStudents()" @else
                            title="Please select academic year, term and class first" class="opacity-70 cursor-not-allowed"
                        @endif>
                        <div class="flex items-center">
                            <div class="bg-green-100 p-3 rounded-xl mr-4">
                                <i class="fas fa-user-edit text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-800">Individual Upload</h3>
                                <p class="text-sm text-gray-600">Upload results by student</p>
                                @if (!$academicYearId || !$semesterId)
                                    <p class="text-xs text-red-500 mt-1">Select year & term first</p>
                                @elseif(!$selectedClass)
                                    <p class="text-xs text-red-500 mt-1">Select class first</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="h-1 w-full bg-green-200 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full w-1/3"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Select student to begin</p>
                        </div>
                    </div>

                    <!-- View Results -->
                    <div class="bg-gradient-to-br from-purple-50 to-violet-50 border-2 border-purple-100 rounded-2xl p-5 transition-all duration-300 hover:shadow-lg hover:border-violet-300 cursor-pointer"
                        @if ($academicYearId && $semesterId && $selectedClass)
                        @click="activeTab = 'search'; $wire.showFilteredStudents()" @else
                            title="Please select academic year, term and class first" class="opacity-70 cursor-not-allowed"
                        @endif>
                        <div class="flex items-center">
                            <div class="bg-purple-100 p-3 rounded-xl mr-4">
                                <i class="fas fa-eye text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-800">View Results</h3>
                                <p class="text-sm text-gray-600">View and analyze results</p>
                                @if (!$academicYearId || !$semesterId)
                                    <p class="text-xs text-red-500 mt-1">Select year & term first</p>
                                @elseif(!$selectedClass)
                                    <p class="text-xs text-red-500 mt-1">Select class first</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="h-1 w-full bg-purple-200 rounded-full overflow-hidden">
                                <div class="h-full bg-purple-500 rounded-full w-4/5"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Select student to view</p>
                        </div>
                    </div>

                    <!-- Print Reports -->
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-100 rounded-2xl p-5 transition-all duration-300 hover:shadow-lg hover:border-orange-300 cursor-pointer"
                        @if ($academicYearId && $semesterId && $selectedClass)
                        @click="activeTab = 'search'; $wire.showFilteredStudents()" @else
                            title="Please select academic year, term and class first" class="opacity-70 cursor-not-allowed"
                        @endif>
                        <div class="flex items-center">
                            <div class="bg-amber-100 p-3 rounded-xl mr-4">
                                <i class="fas fa-print text-amber-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-800">Print Reports</h3>
                                <p class="text-sm text-gray-600">Generate printable reports</p>
                                @if (!$academicYearId || !$semesterId)
                                    <p class="text-xs text-red-500 mt-1">Select year & term first</p>
                                @elseif(!$selectedClass)
                                    <p class="text-xs text-red-500 mt-1">Select class first</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="h-1 w-full bg-amber-200 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-500 rounded-full w-1/2"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Select student to print</p>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Student Search Tab -->
            <div x-show="activeTab === 'search'" class="space-y-6 animate-fade-in">
                <div class="bg-gradient-to-r from-gray-50 to-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-search mr-2 text-indigo-600"></i>
                        Find Students
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Class Selector -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-graduation-cap mr-1 text-indigo-500"></i> Class
                            </label>
                            <select wire:model.live="selectedClass"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
                                <option value="">Select Class</option>
                                @foreach (\App\Models\MyClass::all() as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Section Selector -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-layer-group mr-1 text-indigo-500"></i> Section
                            </label>
                            <select wire:model.live="selectedSection"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
                                <option value="">Select Section</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subject Selector -->
                        @if ($selectedClass)
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    <i class="fas fa-book mr-1 text-indigo-500"></i> Subject
                                </label>
                                <select wire:model.live="selectedSubject"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
                                    <option value="">Select Subject</option>
                                    @forelse ($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @empty
                                        <option disabled>No subjects available</option>
                                    @endforelse
                                </select>
                            </div>
                        @endif

                        <!-- Search Button -->
                        <div class="flex items-end">
                            <button wire:click="showFilteredStudents" wire:loading.attr="disabled"
                                class="w-full bg-gradient-to-r from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white font-medium py-3 px-4 rounded-xl transition-all duration-300 transform hover:scale-[1.02] shadow-lg flex items-center justify-center">
                                <span wire:loading.remove wire:target="showFilteredStudents">
                                    <i class="fas fa-search mr-2"></i> Search
                                </span>
                                <span wire:loading wire:target="showFilteredStudents">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Searching...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Students Table -->
                @if ($showStudents)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden animate-slide-up">
                        @if ($filteredStudents->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gradient-to-r from-indigo-600 to-purple-700">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                                Student
                                            </th>
                                            <th
                                                class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($filteredStudents as $student)
                                                                <tr class="hover:bg-indigo-50 transition-all duration-200">
                                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                                        <div class="flex items-center">
                                                                            <div
                                                                                class="flex-shrink-0 h-12 w-12 rounded-full overflow-hidden border-2 border-indigo-200">
                                                                                <img class="h-12 w-12 object-cover"
                                                                                    src="{{ $student->user?->profile_photo_url ?? asset('images/default-avatar.png') }}"
                                                                                    alt="{{ $student->user?->name ?? 'Deleted User' }}">
                                                                            </div>
                                                                            <div class="ml-4">
                                                                                <div class="text-sm font-bold text-gray-900">
                                                                                    {{ $student->user?->name ?? 'Deleted User' }}
                                                                                </div>
                                                                                <div class="text-sm text-gray-500">
                                                                                    {{ $student->getClassForYear($academicYearId)->name ?? '' }}
                                                                                    @if($student->getSectionForYear($academicYearId))
                                                                                        • {{ $student->getSectionForYear($academicYearId)->name }}
                                                                                    @endif
                                                                                    • {{ $student->admission_number }}
                                                                                </div>

                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                                        <div class="flex justify-end space-x-2">
                                                                            <button wire:click="goToUpload({{ $student->id }})"
                                                                                class="text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 px-4 py-2 rounded-xl transition-all duration-300 transform hover:scale-105 flex items-center shadow">
                                                                                <i class="fas fa-upload mr-1"></i> Upload
                                                                            </button>
                                                                            <button wire:click="goToView({{ $student->id }})"
                                                                                class="text-white bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700 px-4 py-2 rounded-xl transition-all duration-300 transform hover:scale-105 flex items-center shadow">
                                                                                <i class="fas fa-eye mr-1"></i> View
                                                                            </button>
                                                                            <a href="{{ route('result.print', [
                                                'student' => $student->id,
                                                'academicYearId' => $academicYearId,
                                                'semesterId' => $semesterId,
                                            ]) }}" target="_blank"
                                                                                class="text-white bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 px-4 py-2 rounded-xl transition-all duration-300 transform hover:scale-105 flex items-center shadow">
                                                                                <i class="fas fa-print mr-1"></i> Print
                                                                            </a>


                                                                            <a href="{{ route('student-result-history', $student->id) }}"
                                                                                class="text-white bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 px-4 py-2 rounded-xl transition-all duration-300 transform hover:scale-105 flex items-center shadow">
                                                                                <i class="fas fa-history mr-1"></i> History
                                                                            </a>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="px-6 py-4 border-t flex flex-col sm:flex-row items-center justify-between bg-gray-50">
                                <div class="flex items-center space-x-2 mb-4 sm:mb-0">
                                    <span class="text-sm text-gray-700">Rows per page:</span>
                                    <select wire:model.live="perPage"
                                        class="text-sm border-gray-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="w-full sm:w-auto">
                                    {{ $filteredStudents->links() }}
                                </div>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <i class="fas fa-user-slash text-5xl text-indigo-200 mb-4"></i>
                                <h3 class="mt-4 text-xl font-bold text-gray-900">No students found</h3>
                                <p class="mt-2 text-gray-500">Try adjusting your search filters</p>
                                <button wire:click="clearFilters"
                                    class="mt-4 inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-700 text-white font-medium rounded-xl shadow-lg hover:from-indigo-700 hover:to-purple-800 transition-all duration-300 transform hover:scale-105">
                                    <i class="fas fa-redo mr-2"></i> Reset Filters
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Floating Action Button -->
        <div class="fixed bottom-6 right-6 z-50">
            <button @click="activeTab = 'actions'; window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="w-14 h-14 bg-gradient-to-r from-indigo-600 to-purple-700 text-white rounded-full shadow-lg flex items-center justify-center hover:from-indigo-700 hover:to-purple-800 transition-all duration-300 transform hover:scale-110 animate-bounce">
                <i class="fas fa-bolt text-sm"></i>
            </button>
        </div>

        <!-- Notification Toast -->
        <div x-show="showSuccess" x-transition
            class="fixed bottom-5 right-5 max-w-xs w-full bg-gradient-to-r from-green-600 to-teal-700 text-white px-5 py-4 rounded-xl shadow-lg z-50 flex items-center animate-fade-in-up">
            <i class="fas fa-check-circle text-xl mr-3"></i>
            <span x-text="successMessage" class="text-sm"></span>
        </div>

        <!-- Subject Selection Modal -->
        <div x-show="showSubjectModal" x-transition class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showSubjectModal = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full"
                    x-on:click.away="showSubjectModal = false">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-start">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Select Subject for Bulk Upload
                            </h3>
                            <button @click="showSubjectModal = false" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4">
                            <p class="text-sm text-gray-500 mb-4">Select a subject to upload results in bulk</p>

                            @if (count($subjects) > 0)
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    @foreach ($subjects as $subject)
                                        <button wire:click="openSubjectBulkEdit({{ $subject->id }})"
                                            class="bg-indigo-100 hover:bg-indigo-200 text-indigo-800 px-4 py-3 rounded-lg text-center transition-colors duration-200">
                                            <i class="fas fa-book text-lg mb-2"></i>
                                            <span class="text-sm font-medium">{{ $subject->name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-6 text-gray-500">
                                    <i class="fas fa-book-open text-3xl mb-3 text-gray-300"></i>
                                    <p>No subjects available for the selected class</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="showSubjectModal = false" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>








<!-- Bulk Upload Modal - FIXED VERSION -->
<div x-show="showBulkModal" x-transition class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showBulkModal = false">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal container with fixed height and flex column layout -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full h-[90vh] flex flex-col"
            x-on:click.away="showBulkModal = false">

            <!-- Modal header -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Bulk Edit - {{ \App\Models\Subject::find($selectedSubjectForBulkEdit)?->name }}
                    </h3>
                    <button @click="showBulkModal = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @if (session('success'))
                    <div
                        class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        {{ session('success') }}
                    </div>
                @endif
            </div>

            <!-- Scrollable content area -->
            <div class="flex-1 overflow-y-auto px-4 sm:px-6">
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Student
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    1st CA (10)
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    2nd CA (10)
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    3rd CA (10)
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    4th CA (10)
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Exam (60)
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Comment
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($bulkStudents as $student)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if ($student->user && $student->user->profile_photo_url)
                                                <div
                                                    class="flex-shrink-0 h-10 w-10 rounded-full overflow-hidden mr-3">
                                                    <img class="h-10 w-10 object-cover"
                                                        src="{{ $student->user->profile_photo_url }}"
                                                        alt="{{ $student->user->name }}"
                                                        onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                                                </div>
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $student->user?->name ?? 'Deleted User' }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $student->admission_number }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- CA1 Score -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input wire:model.live="bulkResults.{{ $student->id }}.ca1_score"
                                            type="number" min="0" max="10" step="1"
                                            class="w-16 border rounded px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500"
                                            x-on:input.debounce.500ms="
                                                let val = parseFloat($event.target.value);
                                                if (isNaN(val) || $event.target.value === '') {
                                                    $event.target.value = '';
                                                } else {
                                                    $event.target.value = Number.isInteger(val) ? val : val.toFixed(1);
                                                }
                                            ">
                                    </td>
                                    
                                    <!-- CA2 Score -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input wire:model.live="bulkResults.{{ $student->id }}.ca2_score"
                                            type="number" min="0" max="10" step="1"
                                            class="w-16 border rounded px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500"
                                            x-on:input.debounce.500ms="
                                                let val = parseFloat($event.target.value);
                                                if (isNaN(val) || $event.target.value === '') {
                                                    $event.target.value = '';
                                                } else {
                                                    $event.target.value = Number.isInteger(val) ? val : val.toFixed(1);
                                                }
                                            ">
                                    </td>
                                    
                                    <!-- CA3 Score -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input wire:model.live="bulkResults.{{ $student->id }}.ca3_score"
                                            type="number" min="0" max="10" step="1"
                                            class="w-16 border rounded px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500"
                                            x-on:input.debounce.500ms="
                                                let val = parseFloat($event.target.value);
                                                if (isNaN(val) || $event.target.value === '') {
                                                    $event.target.value = '';
                                                } else {
                                                    $event.target.value = Number.isInteger(val) ? val : val.toFixed(1);
                                                }
                                            ">
                                    </td>
                                    
                                    <!-- CA4 Score -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input wire:model.live="bulkResults.{{ $student->id }}.ca4_score"
                                            type="number" min="0" max="10" step="1"
                                            class="w-16 border rounded px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500"
                                            x-on:input.debounce.500ms="
                                                let val = parseFloat($event.target.value);
                                                if (isNaN(val) || $event.target.value === '') {
                                                    $event.target.value = '';
                                                } else {
                                                    $event.target.value = Number.isInteger(val) ? val : val.toFixed(1);
                                                }
                                            ">
                                    </td>
                                    
                                    <!-- Exam Score -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input wire:model.live="bulkResults.{{ $student->id }}.exam_score"
                                            type="number" min="0" max="60" step="1"
                                            class="w-20 border rounded px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500"
                                            x-on:input.debounce.500ms="
                                                let val = parseFloat($event.target.value);
                                                if (isNaN(val) || $event.target.value === '') {
                                                    $event.target.value = '';
                                                } else {
                                                    $event.target.value = Number.isInteger(val) ? val : val.toFixed(1);
                                                }
                                            ">
                                    </td>
                                    
                                    <!-- Total Score (Calculated) -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-medium">
                                        {{ (int) ($bulkResults[$student->id]['ca1_score'] ?? 0) +
                                            (int) ($bulkResults[$student->id]['ca2_score'] ?? 0) +
                                            (int) ($bulkResults[$student->id]['ca3_score'] ?? 0) +
                                            (int) ($bulkResults[$student->id]['ca4_score'] ?? 0) +
                                            (int) ($bulkResults[$student->id]['exam_score'] ?? 0) }}
                                    </td>
                                    
                                    <!-- Comment -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input
                                            wire:model.live.debounce.500ms="bulkResults.{{ $student->id }}.comment"
                                            type="text"
                                            class="w-full border rounded px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button wire:click="deleteBulkResult({{ $student->id }})"
                                            onclick="return confirm('Are you sure you want to delete this result?')"
                                            class="text-red-600 hover:text-red-800 transition-colors duration-200"
                                            title="Delete result">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-8 text-center">
                                        <div class="text-gray-500">
                                            <i class="fas fa-user-slash text-4xl text-gray-300 mb-3"></i>
                                            <h3 class="text-lg font-medium text-gray-700">No students found</h3>
                                            <p class="mt-1 text-sm">
                                                No students are assigned to this subject for
                                                {{ \App\Models\MyClass::find($selectedClass)?->name ?? 'selected class' }}
                                            </p>
                                            <p class="mt-2 text-xs text-gray-500">
                                                Check student-subject assignments in the Subjects section
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Fixed footer with action buttons -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 sticky bottom-0">
                <button wire:click="saveBulkResults" type="button" wire:loading.attr="disabled"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="saveBulkResults">
                        Save All Results
                    </span>
                    <span wire:loading wire:target="saveBulkResults">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Saving...
                    </span>
                </button>
                <button @click="showBulkModal = false" type="button" wire:loading.attr="disabled"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>






    </div>


    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
        <style>
            [x-cloak] {
                display: none !important;

                .animate-slide-up {
                    animation: slideUp 0.5s ease-out;
                }

                .animate-fade-in {
                    animation: fadeIn 0.8s ease-in;
                }

                .animate-fade-in-up {
                    animation: fadeInUp 0.5s ease-out;
                }

                @keyframes slideUp {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @keyframes fadeIn {
                    from {
                        opacity: 0;
                    }

                    to {
                        opacity: 1;
                    }
                }

                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .animate-bounce {
                    animation: bounce 2s infinite;
                }

                @keyframes bounce {

                    0%,
                    20%,
                    50%,
                    80%,
                    100% {
                        transform: translateY(0);
                    }

                    40% {
                        transform: translateY(-15px);
                    }

                    60% {
                        transform: translateY(-10px);
                    }
                }
        </style>
    @endpush
</div>