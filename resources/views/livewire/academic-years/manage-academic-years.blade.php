<div>
    <!-- Loading Spinner -->
    <div wire:loading class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-gray-700 opacity-75 flex flex-col items-center justify-center">
        <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4"></div>
        <h2 class="text-center text-white text-xl font-semibold">Loading...</h2>
    </div>
    
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('danger'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('danger') }}</span>
        </div>
    @endif

    <!-- Set Current Academic Year -->
    @can('set academic year')
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title">Set Current Academic Year</h4>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="setAcademicYear">
                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-9">
                            <label for="selectedAcademicYearId" class="block text-sm font-medium mb-2">Select Academic Year</label>
                            <select 
                                wire:model="selectedAcademicYearId" 
                                id="selectedAcademicYearId" 
                                name="selectedAcademicYearId"
                                class="form-control w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedAcademicYearId') 
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                            @enderror
                        </div>
                        <div class="md:col-span-3">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
                                <i class="fa fa-key mr-2"></i>
                                <span>Set Academic Year</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    <!-- Academic Years List -->
    <div class="card">
        <div class="card-header flex justify-between items-center">
            <h4 class="card-title">Academic Years</h4>
            @can('create', App\Models\AcademicYear::class)
                <button wire:click="toggleForm" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    <span>{{ $showForm ? 'Cancel' : 'Create New' }}</span>
                </button>
            @endcan
        </div>

        <div class="card-body">
            <!-- Create/Edit Form -->
            @if ($showForm)
                <div class="mb-6 p-4 border border-gray-300 rounded bg-gray-50">
                    <h5 class="text-lg font-semibold mb-4">{{ $editMode ? 'Edit Academic Year' : 'Create New Academic Year' }}</h5>
                    
                    <form wire:submit.prevent="{{ $editMode ? 'update' : 'create' }}">
                        <!-- Validation Errors -->
                        @if ($errors->any())
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Start Year -->
                            <div>
                                <label for="startYear" class="block text-sm font-medium mb-2">Start Year <span class="text-red-500">*</span></label>
                                <input 
                                    type="number" 
                                    wire:model="startYear" 
                                    id="startYear"
                                    name="startYear"
                                    placeholder="e.g., 2024"
                                    min="1900"
                                    max="2100"
                                    class="form-control w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                                @error('startYear') 
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                @enderror
                            </div>
                            
                            <!-- Stop Year -->
                            <div>
                                <label for="stopYear" class="block text-sm font-medium mb-2">Stop Year <span class="text-red-500">*</span></label>
                                <input 
                                    type="number" 
                                    wire:model="stopYear" 
                                    id="stopYear"
                                    name="stopYear"
                                    placeholder="e.g., 2025"
                                    min="1900"
                                    max="2100"
                                    class="form-control w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                                @error('stopYear') 
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded inline-flex items-center">
                                <i class="fas fa-save mr-2"></i>
                                <span>{{ $editMode ? 'Update' : 'Create' }}</span>
                            </button>
                            <button type="button" wire:click="resetForm" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded">
                                Cancel
                            </button>
                        </div>

                        @if (!$editMode)
                            <p class="text-sm text-gray-600 mt-3">
                                <i class="fas fa-info-circle"></i> Three terms will be automatically created: First Term, Second Term, Third Term
                            </p>
                        @endif
                    </form>
                </div>
            @endif

            <!-- Academic Years Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stop Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terms Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($academicYears as $year)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <strong>{{ $year->name }}</strong>
                                    @if ($year->id == auth()->user()->school->academic_year_id)
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Current
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $year->start_year }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $year->stop_year }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $year->semesters->count() }} terms</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex gap-2">
                                        @can('update', $year)
                                            <button wire:click="edit({{ $year->id }})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endcan
                                        
                                        <a href="{{ route('academic-years.show', $year->id) }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded text-sm" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @can('delete', $year)
                                            <button 
                                                wire:click="delete({{ $year->id }})" 
                                                wire:confirm="Are you sure you want to delete this academic year? All associated terms and data will also be deleted. This action cannot be undone."
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm {{ $year->id == auth()->user()->school->academic_year_id ? 'opacity-50 cursor-not-allowed' : '' }}" 
                                                title="{{ $year->id == auth()->user()->school->academic_year_id ? 'Cannot delete current academic year' : 'Delete' }}"
                                                @if($year->id == auth()->user()->school->academic_year_id) disabled @endif
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No academic years found. Create one to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <style>
        .loader {
    border-top-color: #3498db;
    animation: spinner 1.5s linear infinite;
}

@keyframes spinner {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
</div>