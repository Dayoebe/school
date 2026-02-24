<div class="rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-calendar-alt mr-2"></i>Timetables
            </h2>
            @if($canCreateTimetable)
                <button wire:click="switchMode('create')"
                    class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                    <i class="fas fa-plus mr-2"></i>Create Timetable
                </button>
            @endif
        </div>
    </div>

    <div class="space-y-4 p-6">
        @if(session('success'))
            <div class="rounded border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        @if(!$activeSemesterId)
            <div class="rounded border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
                <i class="fas fa-info-circle mr-2"></i>Set an active semester to create and manage timetables.
            </div>
        @endif

        @if(!auth()->user()->hasRole('student'))
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">Filter by Class</label>
                <select wire:model.live="selectedClass"
                    class="w-full rounded-lg border border-gray-300 p-3 focus:border-indigo-500 focus:ring-indigo-500 md:w-1/2">
                    <option value="">Select a class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($timetables && $timetables->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Description</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Class</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Time Slots</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($timetables as $timetable)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $timetable->name }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600">{{ Str::limit($timetable->description, 60) }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600">{{ $timetable->myClass->name }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                        {{ $timetable->timeSlots->count() }} slots
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right text-sm">
                                    <div class="inline-flex gap-2">
                                        @if($canReadTimetable)
                                            <button wire:click="switchMode('build', {{ $timetable->id }})"
                                                class="rounded-lg bg-blue-100 px-3 py-1.5 text-blue-700 hover:bg-blue-200"
                                                title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif

                                        @if($canUpdateTimetable)
                                            <button wire:click="switchMode('edit', {{ $timetable->id }})"
                                                class="rounded-lg bg-yellow-100 px-3 py-1.5 text-yellow-700 hover:bg-yellow-200"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif

                                        @if($canDeleteTimetable)
                                            <button wire:click="deleteTimetable({{ $timetable->id }})"
                                                wire:confirm="Are you sure you want to delete this timetable?"
                                                class="rounded-lg bg-red-100 px-3 py-1.5 text-red-700 hover:bg-red-200"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="rounded-lg bg-gray-50 py-12 text-center">
                <i class="fas fa-calendar-times mb-4 text-5xl text-gray-300"></i>
                <h3 class="text-xl font-semibold text-gray-700">No Timetables Found</h3>
                <p class="mt-1 text-gray-500">Create a timetable to get started.</p>
            </div>
        @endif

        @if($canReadCustomItems || $canCreateCustomItems || $canUpdateCustomItems || $canDeleteCustomItems)
            <div class="border-t border-gray-200 pt-4">
                <button wire:click="switchMode('custom-items')"
                    class="rounded-lg border border-gray-300 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-cog mr-2"></i>Manage Custom Items
                </button>
            </div>
        @endif
    </div>
</div>
