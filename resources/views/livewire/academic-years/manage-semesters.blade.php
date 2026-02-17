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

    @if (!auth()->user()->school->academicYear)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
            <i class="fas fa-exclamation-triangle"></i> 
            <span class="block sm:inline">Please set an academic year first before managing terms.</span>
        </div>
    @else
        <!-- Set Current Term -->
        @can('set semester')
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">Set Current Term for {{ auth()->user()->school->academicYear->name }}</h4>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="setSemester">
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
                                <label for="selectedSemesterId" class="block text-sm font-medium mb-2">Select Term</label>
                                <select 
                                    wire:model="selectedSemesterId" 
                                    id="selectedSemesterId" 
                                    name="selectedSemesterId"
                                    class="form-control w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    @foreach ($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedSemesterId') 
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                @enderror
                            </div>
                            <div class="md:col-span-3">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
                                    <i class="fa fa-key mr-2"></i>
                                    <span>Set Term</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endcan

        <!-- Terms List -->
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h4 class="card-title">Terms for {{ auth()->user()->school->academicYear->name }}</h4>
                @can('create', App\Models\Semester::class)
                    <button wire:click="toggleForm" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded inline-flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        <span>{{ $showForm ? 'Cancel' : 'Create Custom Term' }}</span>
                    </button>
                @endcan
            </div>

            <div class="card-body">
                <!-- Create/Edit Form -->
                @if ($showForm)
                    <div class="mb-6 p-4 border border-gray-300 rounded bg-gray-50">
                        <h5 class="text-lg font-semibold mb-4">{{ $editMode ? 'Edit Term' : 'Create Custom Term' }}</h5>
                        
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
                            
                            <!-- Term Name -->
                            <div class="mb-4">
                                <label for="termName" class="block text-sm font-medium mb-2">Term Name <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    wire:model="termName" 
                                    id="termName"
                                    name="termName"
                                    placeholder="e.g., Mid-Term Break"
                                    class="form-control w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                                @error('termName') 
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                                @enderror
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
                        </form>
                    </div>
                @endif

                <!-- Terms Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($semesters as $semester)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <strong>{{ $semester->name }}</strong>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($semester->id == auth()->user()->school->semester_id)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Current Term
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex gap-2">
                                            @can('update', $semester)
                                                <button wire:click="edit({{ $semester->id }})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endcan

                                            @can('delete', $semester)
                                                <button 
                                                    wire:click="delete({{ $semester->id }})" 
                                                    wire:confirm="Are you sure you want to delete this term? This action cannot be undone."
                                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm {{ $semester->id == auth()->user()->school->semester_id ? 'opacity-50 cursor-not-allowed' : '' }}" 
                                                    title="{{ $semester->id == auth()->user()->school->semester_id ? 'Cannot delete current term' : 'Delete' }}"
                                                    @if($semester->id == auth()->user()->school->semester_id) disabled @endif
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                        No terms found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle"></i> Three terms are automatically created when you create an academic year. You can edit them or create additional custom terms.
                    </p>
                </div>
            </div>
        </div>
    @endif
    
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