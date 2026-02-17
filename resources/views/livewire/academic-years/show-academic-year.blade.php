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

    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('academic-years.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Academic Years
        </a>
    </div>

    <!-- Academic Year Overview Card -->
    <div class="card mb-4">
        <div class="card-header flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-bold">{{ $academicYear->name }}</h3>
                <p class="text-sm text-gray-600 mt-1">
                    School: {{ $academicYear->school->name }}
                    @if ($academicYear->id == auth()->user()->school->academic_year_id)
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Current Academic Year
                        </span>
                    @endif
                </p>
            </div>
            <div class="flex gap-2">
                @if ($academicYear->id != auth()->user()->school->academic_year_id)
                    @can('setAcademicYear', App\Models\AcademicYear::class)
                        <button 
                            wire:click="setAsCurrentAcademicYear" 
                            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded inline-flex items-center"
                        >
                            <i class="fas fa-check-circle mr-2"></i>
                            Set as Current
                        </button>
                    @endcan
                @endif
                @can('update', $academicYear)
                    <button 
                        wire:click="toggleEditAcademicYear" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded inline-flex items-center"
                    >
                        <i class="fas fa-edit mr-2"></i>
                        {{ $editingAcademicYear ? 'Cancel Edit' : 'Edit Details' }}
                    </button>
                @endcan
            </div>
        </div>

        <div class="card-body">
            @if ($editingAcademicYear)
                <!-- Edit Academic Year Form -->
                <form wire:submit.prevent="updateAcademicYear" class="bg-gray-50 p-4 rounded border border-gray-300">
                    <h4 class="text-lg font-semibold mb-4">Edit Academic Year</h4>
                    
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
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="startYear" class="block text-sm font-medium mb-2">Start Year</label>
                            <input 
                                type="number" 
                                wire:model="startYear" 
                                id="startYear"
                                min="1900"
                                max="2100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            @error('startYear') 
                                <span class="text-red-500 text-sm">{{ $message }}</span> 
                            @enderror
                        </div>
                        
                        <div>
                            <label for="stopYear" class="block text-sm font-medium mb-2">Stop Year</label>
                            <input 
                                type="number" 
                                wire:model="stopYear" 
                                id="stopYear"
                                min="1900"
                                max="2100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            @error('stopYear') 
                                <span class="text-red-500 text-sm">{{ $message }}</span> 
                            @enderror
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                            <i class="fas fa-save mr-2"></i>
                            Save Changes
                        </button>
                        <button type="button" wire:click="toggleEditAcademicYear" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded">
                            Cancel
                        </button>
                    </div>
                </form>
            @else
                <!-- Academic Year Details -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded">
                        <div class="text-sm text-gray-600 mb-1">Start Year</div>
                        <div class="text-2xl font-bold text-blue-700">{{ $academicYear->start_year }}</div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded">
                        <div class="text-sm text-gray-600 mb-1">Stop Year</div>
                        <div class="text-2xl font-bold text-blue-700">{{ $academicYear->stop_year }}</div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded">
                        <div class="text-sm text-gray-600 mb-1">Total Terms</div>
                        <div class="text-2xl font-bold text-blue-700">{{ $semesters->count() }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Terms/Semesters Card -->
    <div class="card mb-4">
        <div class="card-header flex justify-between items-center">
            <h4 class="card-title">Terms in {{ $academicYear->name }}</h4>
            @can('create', App\Models\Semester::class)
                <button 
                    wire:click="toggleSemesterForm" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded inline-flex items-center"
                >
                    <i class="fas fa-plus mr-2"></i>
                    {{ $showSemesterForm && !$editingSemesterId ? 'Cancel' : 'Add Term' }}
                </button>
            @endcan
        </div>

        <div class="card-body">
            <!-- Create/Edit Term Form -->
            @if ($showSemesterForm)
                <div class="mb-6 p-4 border border-gray-300 rounded bg-gray-50">
                    <h5 class="text-lg font-semibold mb-4">{{ $editingSemesterId ? 'Edit Term' : 'Create New Term' }}</h5>
                    
                    <form wire:submit.prevent="{{ $editingSemesterId ? 'updateSemester' : 'createSemester' }}">
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
                        
                        <div class="mb-4">
                            <label for="semesterName" class="block text-sm font-medium mb-2">Term Name</label>
                            <input 
                                type="text" 
                                wire:model="semesterName" 
                                id="semesterName"
                                placeholder="e.g., Fourth Term"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            @error('semesterName') 
                                <span class="text-red-500 text-sm">{{ $message }}</span> 
                            @enderror
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                                <i class="fas fa-save mr-2"></i>
                                {{ $editingSemesterId ? 'Update Term' : 'Create Term' }}
                            </button>
                            <button type="button" wire:click="resetSemesterForm" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Terms Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($semesters as $semester)
                    <div class="border border-gray-300 rounded-lg p-4 hover:shadow-lg transition-shadow {{ $semester->id == auth()->user()->school->semester_id ? 'bg-green-50 border-green-500' : 'bg-white' }}">
                        <div class="flex justify-between items-start mb-3">
                            <h5 class="text-lg font-semibold text-gray-800">{{ $semester->name }}</h5>
                            @if ($semester->id == auth()->user()->school->semester_id)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Current
                                </span>
                            @endif
                        </div>

                        {{-- <div class="text-sm text-gray-600 mb-3">
                            <div class="mb-1">
                                <i class="fas fa-calendar mr-2"></i>
                                <strong>Exams:</strong> {{ $semester->exams->count() }}
                            </div>
                        </div> --}}

                        <div class="flex flex-wrap gap-2">
                            @if ($semester->id != auth()->user()->school->semester_id)
                                @can('setSemester', App\Models\Semester::class)
                                    <button 
                                        wire:click="setSemesterAsCurrent({{ $semester->id }})" 
                                        class="text-xs bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded"
                                        title="Set as Current"
                                    >
                                        <i class="fas fa-check"></i> Set Current
                                    </button>
                                @endcan
                            @endif

                            @can('update', $semester)
                                <button 
                                    wire:click="editSemester({{ $semester->id }})" 
                                    class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded"
                                    title="Edit"
                                >
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            @endcan

                            @can('delete', $semester)
                                <button 
                                    wire:click="deleteSemester({{ $semester->id }})" 
                                    wire:confirm="Are you sure you want to delete this term?"
                                    class="text-xs bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded {{ $semester->id == auth()->user()->school->semester_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    title="{{ $semester->id == auth()->user()->school->semester_id ? 'Cannot delete current term' : 'Delete' }}"
                                    @if($semester->id == auth()->user()->school->semester_id) disabled @endif
                                >
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-gray-500 py-8">
                        No terms found for this academic year.
                    </div>
                @endforelse
            </div>
        </div>
    </div>



    <!-- Statistics Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-4 rounded-lg shadow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm opacity-90">Total Terms</div>
                    <div class="text-3xl font-bold">{{ $semesters->count() }}</div>
                </div>
                <i class="fas fa-calendar-alt text-4xl opacity-50"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-4 rounded-lg shadow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm opacity-90">Duration</div>
                    <div class="text-2xl font-bold">{{ $academicYear->stop_year - $academicYear->start_year }} Year{{ $academicYear->stop_year - $academicYear->start_year > 1 ? 's' : '' }}</div>
                </div>
                <i class="fas fa-clock text-4xl opacity-50"></i>
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