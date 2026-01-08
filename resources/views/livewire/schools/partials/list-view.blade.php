<!-- Set School Card (Super Admin Only) -->
@can('setSchool', App\Models\School::class)
<div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
    <div class="bg-lime-600 px-6 py-4">
        <h3 class="text-xl font-bold text-white">
            <i class="fas fa-cog mr-2"></i>Set Working School
        </h3>
    </div>
    <div class="p-6">
        <form wire:submit.prevent="setSchool">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <select wire:model="selectedSchoolId" 
                            class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-purple-500">
                        <option value="">Select a school</option>
                        @foreach($allSchools as $school)
                            <option value="{{ $school->id }}">
                                {{ $school->name }} - {{ $school->address }}
                            </option>
                        @endforeach
                    </select>
                    @error('selectedSchoolId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <button type="submit" 
                        class="px-6 py-3 bg-zinc-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-check mr-2"></i>Set School
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

<!-- Schools List -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-sky-600 px-6 py-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="text-2xl font-bold text-white">
                <i class="fas fa-school mr-2"></i>All Schools
            </h2>
            @can('create', App\Models\School::class)
            <button wire:click="switchMode('create')" 
                    class="px-4 py-2 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition">
                <i class="fas fa-plus mr-2"></i>Create School
            </button>
            @endcan
        </div>
    </div>

    <div class="p-6">
        <!-- Search -->
        <div class="mb-6">
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Search schools..."
                   class="w-full md:w-96 rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Initials</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($schools as $school)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($school->logo_path)
                                        <img src="{{ $school->logo_url }}" alt="{{ $school->name }}" class="w-10 h-10 rounded-full mr-3">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                            <i class="fas fa-school text-blue-600"></i>
                                        </div>
                                    @endif
                                    <span class="font-semibold text-gray-900">{{ $school->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $school->initials ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ Str::limit($school->address, 50) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-mono">
                                    {{ $school->code }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    @can('view', $school)
                                    <a href="{{ route('schools.show', $school->id) }}" 
                                       class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition text-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('update', $school)
                                    <button wire:click="switchMode('edit', {{ $school->id }})" 
                                            class="px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition text-sm">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    @endcan
                                    
                                    @can('delete', $school)
                                    <button wire:click="deleteSchool({{ $school->id }})" 
                                            wire:confirm="Are you sure you want to delete this school?"
                                            class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-school text-4xl text-gray-300 mb-4"></i>
                                <p class="text-lg">No schools found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $schools->links() }}
        </div>
    </div>
</div>