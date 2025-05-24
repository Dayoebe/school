<div x-data="{ showYearDropdown: false, showTermDropdown: false }" class="space-y-6">
    {{-- Display Selected Academic Year and Term --}}
    <div class="flex justify-between items-center bg-blue-100 px-6 py-4 rounded-xl shadow">
        <div>
            <h2 class="text-lg font-bold text-blue-800">Academic Overview</h2>
            <p class="text-sm text-blue-700">
                Showing data for:
                <span class="font-semibold">
                    {{ \App\Models\AcademicYear::find($academicYearId)?->name ?? 'N/A' }}
                    /
                    {{ \App\Models\Semester::find($semesterId)?->name ?? 'N/A' }}
                </span>
            </p>
        </div>

        <div class="flex gap-4 items-center">
            {{-- Change Academic Year Dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="bg-white border border-blue-500 text-blue-700 px-4 py-2 rounded shadow hover:bg-blue-50 transition">
                    Change Academic Year
                </button>
                <div x-show="open" @click.outside="open = false"
                    class="absolute z-50 mt-2 bg-white border rounded shadow-lg w-64 max-h-60 overflow-auto">
                    @foreach (\App\Models\AcademicYear::orderBy('start_year', 'desc')->get() as $year)
                        <div class="hover:bg-blue-100 px-4 py-2 cursor-pointer"
                            wire:click="$set('academicYearId', {{ $year->id }}); open = false;">
                            {{ $year->name }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Change Semester (Term) Dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="bg-white border border-blue-500 text-blue-700 px-4 py-2 rounded shadow hover:bg-blue-50 transition">
                    Change Term
                </button>
                <div x-show="open" @click.outside="open = false"
                    class="absolute z-50 mt-2 bg-white border rounded shadow-lg w-64 max-h-60 overflow-auto">
                    @php
                        $semesters = $academicYearId
                            ? \App\Models\Semester::where('academic_year_id', $academicYearId)->get()
                            : collect();
                    @endphp
                    @foreach ($semesters as $term)
                        <div class="hover:bg-blue-100 px-4 py-2 cursor-pointer"
                            wire:click="$set('semesterId', {{ $term->id }}); open = false;">
                            {{ $term->name }}
                        </div>
                    @endforeach
                </div>
            </div>
            {{-- Go To Academic Overview Button --}}
            <button wire:click="goToAcademicOverview"
                class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">
                Go to Academic Overview
            </button>
        </div>
    </div>

    {{-- Filters and Student List --}}
    <div class="p-6 bg-white rounded-xl shadow-lg space-y-6">
        {{-- Filters --}}
        <div class="grid md:grid-cols-3 gap-6">
            {{-- Select Class --}}
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Select Class</label>
                <select wire:model="selectedClass"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Select Class --</option>
                    @foreach (\App\Models\MyClass::all() as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Select Section --}}
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Select Section</label>
                <select wire:model="selectedSection"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Select Section --</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Search Student --}}
            <div class="relative">
                <label class="block font-semibold text-gray-700 mb-1">Search Student</label>
                <input type="text" wire:model.debounce.300ms="studentSearch" placeholder="Enter student name"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                {{-- Dropdown Results --}}
                @if ($studentSearch && $filteredStudents->count())
                    <ul class="absolute bg-white border w-full z-10 shadow-lg max-h-60 overflow-auto mt-1 rounded">
                        @foreach ($filteredStudents as $student)
                            <li wire:click="goToUpload({{ $student->id }})"
                                class="px-4 py-2 hover:bg-blue-100 cursor-pointer transition duration-200">
                                {{ $student->user->name }}
                            </li>
                        @endforeach
                    </ul>
                @elseif($studentSearch && $filteredStudents->isEmpty())
                    <div class="absolute mt-1 text-sm text-gray-500 italic">No student found.</div>
                @endif
            </div>
        </div>


        {{-- Show Students Button --}}
        <div class="flex justify-end">
            <button wire:click="showFilteredStudents"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded shadow disabled:opacity-50 transition">
                {{-- @if (!$selectedClass || !$selectedSection || !$studentSearch) disabled @endif> --}}
                Show Students
            </button>
        </div>

        {{-- Pagination --}}
        @if ($showStudents && !$studentSearch)
            <div class="flex justify-between items-center">
                <div>
                    <label class="text-sm text-gray-600">Students per page:</label>
                    <select wire:model="perPage" class="ml-2 border rounded px-2 py-1 text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        @endif

        {{-- Student Table --}}
        @if ($showStudents && $filteredStudents->count() > 0)
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full border rounded-lg shadow-sm bg-white">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="p-3 border-b">Student Name</th>
                            <th class="p-3 border-b">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filteredStudents as $student)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-3 border-b hover:bg-blue-200 ">{{ $student->user->name ?? 'N/A' }}</td>
                                <td class="p-3 border-b space-x-4">
                                    <button wire:click="goToUpload({{ $student->id }})"
                                        class="text-blue-600 font-medium hover:underline">Upload</button>
                                    <button wire:click="goToView({{ $student->id }})"
                                        class="text-green-600 font-medium hover:underline">View</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $filteredStudents->links() }}
                </div>
            </div>
        @elseif ($showStudents && $filteredStudents->isEmpty())
            <div class="text-center text-gray-500 mt-6 font-semibold">No students found based on your selection.</div>
        @endif
    </div>

    <div x-data="{ show: false, message: '' }" x-show="show" x-transition:enter="transition ease-out duration-500 transform"
        x-transition:enter-start="opacity-0 translate-y-4 scale-90"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-500 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-90" x-init="window.addEventListener('show-overview-alert', event => {
            const emojis = ['🎉', '📘', '🕵️‍♂️', '🧠', '📚', '🔥', '🚀'];
            const randomEmoji = emojis[Math.floor(Math.random() * emojis.length)];
            message = `${randomEmoji} ` + event.detail.message;
            show = true;
            setTimeout(() => show = false, 4000);
        });"
        class="fixed top-5 right-5 max-w-xs w-full bg-gradient-to-r from-blue-500 via-purple-500 to-blue-400 text-white px-5 py-4 rounded-xl shadow-2xl ring-2 ring-white ring-opacity-40 z-50 font-semibold text-sm sm:text-base">
        <div class="flex items-center space-x-3">
            <div class="animate-bounce text-xl">🔔</div>
            <span x-html="message"></span>
        </div>
    </div>
</div>
{{--     

    @if (auth()->user()->isAdmin())
    <div>
        <label>Subject</label>
        <select wire:model="selectedSubject">
            <option value="">All Subjects</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
            @endforeach
        </select>
    </div>
@else
    <div>
        <label>Your Subject</label>
        <select wire:model="selectedSubject">
            <option value="">Select Subject</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
            @endforeach
        </select>
    </div>
@endif --}}
