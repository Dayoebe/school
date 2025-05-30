<div x-data="{ showYearDropdown: false, showTermDropdown: false }" class="space-y-6">
    <!-- Simplified Academic Period Selector -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg p-6 text-white">
        <div class="flex flex-col space-y-4">
            <h2 class="text-2xl font-bold flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Academic Period
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Academic Year - Big Card Style -->
                <div class="bg-white/10 rounded-lg p-4 cursor-pointer" @click="showYearDropdown = !showYearDropdown">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm">Academic Year</p>
                            <p class="font-bold text-lg">
                                {{ \App\Models\AcademicYear::find($academicYearId)?->name ?? 'Not Selected' }}</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition"
                            :class="{ 'rotate-180': showYearDropdown }" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <!-- Dropdown -->
                    <div x-show="showYearDropdown" @click.outside="showYearDropdown = false" x-transition
                        class="mt-2 bg-white rounded-lg shadow-xl overflow-hidden">
                        @foreach (\App\Models\AcademicYear::orderBy('start_year', 'desc')->get() as $year)
                            <button wire:click="$set('academicYearId', {{ $year->id }})"
                                @click="showYearDropdown = false"
                                class="block w-full text-left px-4 py-3 hover:bg-blue-50 text-gray-700 transition flex items-center">
                                <span class="flex-1">{{ $year->name }}</span>
                                @if ($academicYearId == $year->id)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Term - Big Card Style -->
                <div class="bg-white/10 rounded-lg p-4 cursor-pointer" @click="showTermDropdown = !showTermDropdown">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm">Term</p>
                            <p class="font-bold text-lg">
                                {{ \App\Models\Semester::find($semesterId)?->name ?? 'Not Selected' }}</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition"
                            :class="{ 'rotate-180': showTermDropdown }" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <!-- Dropdown -->
                    <div x-show="showTermDropdown" @click.outside="showTermDropdown = false" x-transition
                        class="mt-2 bg-white rounded-lg shadow-xl overflow-hidden">
                        @php
                            $semesters = $academicYearId
                                ? \App\Models\Semester::where('academic_year_id', $academicYearId)->get()
                                : collect();
                        @endphp
                        @foreach ($semesters as $term)
                            <button wire:click="$set('semesterId', {{ $term->id }})"
                                @click="showTermDropdown = false"
                                class="block w-full text-left px-4 py-3 hover:bg-blue-50 text-gray-700 transition flex items-center">
                                <span class="flex-1">{{ $term->name }}</span>
                                @if ($semesterId == $term->id)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Confirmation Button -->
                <button wire:click="goToAcademicOverview"
                    class="bg-white text-blue-700 hover:bg-gray-100 rounded-lg transition-all font-bold flex items-center justify-center p-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                    Confirm Selection
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Card - Simplified with Icons -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Find Students
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Class Selector with Icon -->
            <div class="space-y-2">
                <label class="block font-semibold text-gray-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Class
                </label>
                <select wire:model="selectedClass"
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg">
                    <option value="">Select Class</option>
                    @foreach (\App\Models\MyClass::all() as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Section Selector with Icon -->
            <div class="space-y-2">
                <label class="block font-semibold text-gray-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Section
                </label>
                <select wire:model="selectedSection"
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg">
                    <option value="">Select Section</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Subject Selector with Icon -->
            @if ($selectedClass)
                <div class="space-y-2">
                    <label class="block font-semibold text-gray-700 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Subject
                    </label>
                    <select wire:model="selectedSubject"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg">
                        <option value="">Select Subject</option>
                        @forelse ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @empty
                            <option disabled>No subjects available</option>
                        @endforelse
                    </select>
                </div>
            @endif

            <!-- Student Search with Big Input -->
            <div class="space-y-2">
                <label class="block font-semibold text-gray-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Search Student
                </label>
                <div class="relative">
                    <input type="text" wire:model.debounce.300ms="studentSearch"
                        placeholder="Type student name..."
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 pl-12 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg">
                    <div class="absolute left-3 top-3.5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                @if ($studentSearch && $filteredStudents->count())
                    <ul
                        class="absolute bg-white border-2 border-gray-200 w-full z-10 shadow-lg max-h-60 overflow-auto mt-1 rounded-xl">
                        @foreach ($filteredStudents as $student)
                            <li wire:click="goToUpload({{ $student->id }})"
                                class="px-4 py-3 hover:bg-blue-100 cursor-pointer transition duration-200 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ $student->user->name }}
                            </li>
                        @endforeach
                    </ul>
                @elseif($studentSearch && $filteredStudents->isEmpty())
                    <div class="absolute mt-1 text-sm text-gray-500 italic p-2">No matching students found</div>
                @endif
            </div>
        </div>

        @if ($selectedSubject)
            <div class="bg-blue-100 text-blue-800 p-4 rounded-xl mt-6 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
                Showing students for: <strong
                    class="ml-1">{{ $subjects->firstWhere('id', $selectedSubject)?->name ?? 'N/A' }}</strong>
            </div>
        @endif

        <!-- Big Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-between items-center mt-8 space-y-4 sm:space-y-0 sm:space-x-4">
            <button wire:click="showFilteredStudents"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-4 rounded-xl shadow-lg transition flex-1 w-full sm:w-auto flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                Show Students List
            </button>

            @if ($selectedClass)
                <button wire:click="clearFilters"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold px-8 py-4 rounded-xl shadow-lg transition flex-1 w-full sm:w-auto flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset Filters
                </button>
            @endif
        </div>

        <!-- Quick Subject Access Toggle -->
        <div x-data="{ open: false }" class="mt-8">
            <h4 @click="open = !open"
                class="hover:animate-bounce text-lg font-bold text-gray-800 mb-4 flex items-center cursor-pointer select-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Quick Subject Access
                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 text-gray-500"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 text-gray-500"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
            </h4>

            <div x-show="open" x-transition>
                @if ($selectedClass && $subjects->count())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach ($subjects as $subject)
                            <div class="bg-gray-50 hover:bg-gray-100 rounded-xl p-4 transition cursor-pointer flex justify-between items-center"
                                wire:click="openBulkEdit({{ $subject->id }})">
                                <div class="flex items-center">
                                    <div class="bg-blue-100 p-3 rounded-lg mr-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <span class="font-semibold text-lg">{{ $subject->name }}</span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Students Table with Enhanced Readability -->
    @if ($showStudents)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            @if ($filteredStudents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-600">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-sm font-semibold text-white uppercase tracking-wider">
                                    Student Name
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-sm font-semibold text-white uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($filteredStudents as $student)
                                <tr class="hover:bg-blue-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-lg font-medium text-gray-900">
                                                    {{ $student->user->name ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ $student->myClass->name ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        <div class="flex justify-end space-x-2">

                                            <button wire:click="goToUpload({{ $student->id }})"
                                                class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 px-4 py-2 rounded-lg font-bold flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                Upload
                                            </button>
                                            <button wire:click="goToView({{ $student->id }})"
                                                class="text-green-600 hover:text-green-900 bg-green-100 hover:bg-green-200 px-4 py-2 rounded-lg font-bold flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                View
                                            </button>
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
                        <select wire:model="perPage"
                            class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1">
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No students found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or search term</p>
                    <button wire:click="clearFilters"
                        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Reset Filters
                    </button>
                </div>
            @endif
        </div>
    @endif

    <!-- Bulk Edit Modal -->
    @include('pages.result.subject-upload')

    <!-- Notification Toast -->
    <div x-data="{ show: false, message: '' }" x-show="show" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-4 scale-90"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-300 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-90" x-init="window.addEventListener('show-overview-alert', event => {
            const emojis = ['🎉', '📘', '🕵️‍♂️', '🧠', '📚', '🔥', '🚀'];
            const randomEmoji = emojis[Math.floor(Math.random() * emojis.length)];
            message = `${randomEmoji} ` + event.detail.message;
            show = true;
            setTimeout(() => show = false, 4000);
        });"
        class="fixed bottom-5 right-5 max-w-xs w-full bg-gradient-to-r from-blue-500 via-purple-500 to-blue-400 text-white px-5 py-4 rounded-xl shadow-2xl ring-2 ring-white ring-opacity-40 z-50 font-semibold text-sm sm:text-base">
        <div class="flex items-center space-x-3">
            <div class="animate-bounce text-xl">🔔</div>
            <span x-html="message"></span>
        </div>
    </div>
</div>
