{{-- partials/list-view.blade.php --}}
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-white">
                <i class="fas fa-calendar-alt mr-2"></i>Timetables
            </h2>
            @can('create timetable')
                <button wire:click="switchMode('create')" 
                        class="px-4 py-2 bg-white text-purple-600 font-semibold rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-plus mr-2"></i>Create Timetable
                </button>
            @endcan
        </div>
    </div>

    <div class="p-6">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(!auth()->user()->hasRole('student'))
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Filter by Class</label>
                <select wire:model.live="selectedClass" 
                        class="w-full md:w-1/2 rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-purple-500">
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($timetables && $timetables->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
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
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                    {{ $timetable->name }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    {{ Str::limit($timetable->description, 50) }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    {{ $timetable->myClass->name }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                        {{ $timetable->timeSlots->count() }} slots
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-right">
                                    <div class="flex justify-end gap-2">
                                        @can('read timetable')
                                            <button wire:click="switchMode('build', {{ $timetable->id }})" 
                                                    class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition"
                                                    title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endcan
                                        
                                        @can('update timetable')
                                            <button wire:click="switchMode('edit', {{ $timetable->id }})" 
                                                    class="px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button wire:click="switchMode('build', {{ $timetable->id }})" 
                                                    class="px-3 py-1.5 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition"
                                                    title="Build">
                                                <i class="fas fa-hammer"></i>
                                            </button>
                                        @endcan
                                        
                                        @can('delete timetable')
                                            <button wire:click="deleteTimetable({{ $timetable->id }})" 
                                                    wire:confirm="Are you sure you want to delete this timetable?"
                                                    class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12 bg-gray-50 rounded-lg">
                <i class="fas fa-calendar-times text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Timetables Found</h3>
                <p class="text-gray-500 mb-4">Create a timetable to get started</p>
                @can('create timetable')
                    <button wire:click="switchMode('create')" 
                            class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition">
                        <i class="fas fa-plus mr-2"></i>Create First Timetable
                    </button>
                @endcan
            </div>
        @endif

        @canany(['read custom timetable items', 'read custom timetable item'])
            <div class="mt-6 pt-6 border-t border-gray-200">
                <button wire:click="switchMode('custom-items')" 
                        class="px-4 py-2 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-cog mr-2"></i>Manage Custom Items
                </button>
            </div>
        @endcanany
    </div>
</div>
