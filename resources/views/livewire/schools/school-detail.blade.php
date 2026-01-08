<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-8">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                @if($school->logo_path)
                    <img src="{{ $school->logo_url }}" alt="{{ $school->name }}" 
                         class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
                @else
                    <div class="w-32 h-32 rounded-full border-4 border-white shadow-lg bg-white flex items-center justify-center">
                        <i class="fas fa-school text-5xl text-blue-600"></i>
                    </div>
                @endif
                
                <div class="flex-1 text-white text-center md:text-left">
                    <h1 class="text-3xl font-bold mb-2">{{ $school->name }}</h1>
                    <p class="text-blue-100 mb-4">{{ $school->address }}</p>
                    <div class="flex flex-wrap gap-4 justify-center md:justify-start text-sm">
                        @if($school->email)
                        <span class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>{{ $school->email }}
                        </span>
                        @endif
                        @if($school->phone)
                        <span class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>{{ $school->phone }}
                        </span>
                        @endif
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('schools.index') }}" 
                       class="px-4 py-2 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </a>
                    @can('update', $school)
                    <a href="{{ route('schools.index', ['mode' => 'edit', 'schoolId' => $school->id]) }}" 
                       class="px-4 py-2 bg-white text-blue-600 rounded-lg font-semibold shadow hover:shadow-lg transition">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Details Card -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b">
            <h3 class="text-xl font-bold text-gray-900">School Details</h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Address:</span>
                        <span class="text-gray-900 text-right">{{ $school->address }}</span>
                    </div>
                    
                    @if($school->initials)
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Initials:</span>
                        <span class="text-gray-900">{{ $school->initials }}</span>
                    </div>
                    @endif
                    
                    @if($school->email)
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Email:</span>
                        <span class="text-gray-900">{{ $school->email }}</span>
                    </div>
                    @endif
                    
                    @if($school->phone)
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Phone:</span>
                        <span class="text-gray-900">{{ $school->phone }}</span>
                    </div>
                    @endif
                </div>

                <div class="space-y-4">
                    @if($school->academicYear)
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Current Academic Year:</span>
                        <span class="text-gray-900">{{ $school->academicYear->name }}</span>
                    </div>
                    @endif
                    
                    @if($school->semester)
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Current Term:</span>
                        <span class="text-gray-900">{{ $school->semester->name }}</span>
                    </div>
                    @endif
                    
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">School Code:</span>
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-mono">
                            {{ $school->code }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>