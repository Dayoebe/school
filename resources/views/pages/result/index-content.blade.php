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
                    <option value="{{ $section->name }}">{{ $section->name }}</option>
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
                            <td class="p-3 border-b">{{ $student->user->name ?? 'N/A' }}</td>
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
