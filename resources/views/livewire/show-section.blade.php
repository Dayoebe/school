<div x-data="{ showSubjectManager: false }">
    <div class="card">
        <div class="card-header flex justify-between items-center">
            <div>
                <h2 class="card-title">{{ $section->name }}</h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $section->myClass->name }} • 
                    {{ $section->studentRecords->count() }} students
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('sections.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Sections
                </a>
                <button @click="showSubjectManager = true" class="btn btn-primary">
                    <i class="fas fa-book mr-1"></i> Manage Subjects
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Students Panel -->
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-users mr-2 text-blue-500"></i>
                            Students in Section
                        </h3>
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">
                            {{ $section->studentRecords->count() }} students
                        </span>
                    </div>
                    <livewire:datatable :model="App\Models\User::class" uniqueId="students-list-table" 
                        :filters="[
                            ['name' => 'where' , 'arguments' => ['school_id' , auth()->user()->school_id]], 
                            ['name' => 'whereRelation' , 'arguments' => ['studentRecord','section_id' , $section->id]],
                        ]"
                        :columns="[
                            ['property' => 'name', ] , 
                            ['property' => 'email', ] , 
                            ['type' => 'dropdown', 'name' => 'actions','links' => [
                                ['href' => 'students.edit', 'text' => 'Edit', 'icon' => 'fas fa-pen', ],
                                ['href' => 'students.show', 'text' => 'View', 'icon' => 'fas fa-eye',  ],
                            ]]
                        ]"
                    />
                </div>
                
                <!-- Subjects Panel -->
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-book mr-2 text-green-500"></i>
                            Section Subjects
                        </h3>
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm">
                            {{ $section->subjects->count() }} subjects
                        </span>
                    </div>
                    
                    <div class="space-y-3">
                        @forelse($section->subjects as $subject)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div>
                                    <h4 class="font-medium text-gray-800">{{ $subject->name }}</h4>
                                    <p class="text-sm text-gray-600">
                                        Teachers: 
                                        @foreach($subject->teachers as $teacher)
                                            {{ $teacher->name }}@if(!$loop->last), @endif
                                        @endforeach
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <form action="{{ route('sections.subjects.detach', [$section, $subject]) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 bg-yellow-50 rounded-lg">
                                <i class="fas fa-book-open text-3xl text-yellow-400 mb-3"></i>
                                <p class="text-gray-600">No subjects assigned to this section</p>
                                <button @click="showSubjectManager = true" class="mt-3 text-blue-600 hover:underline">
                                    Add subjects now
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subject Manager Modal -->
    <div x-show="showSubjectManager" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" @click="showSubjectManager = false">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-start">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            <i class="fas fa-book mr-2 text-purple-500"></i>
                            Manage Subjects for {{ $section->name }}
                        </h3>
                        <button @click="showSubjectManager = false" class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Current Subjects -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-list mr-2 text-blue-500"></i>
                                Current Subjects ({{ $section->subjects->count() }})
                            </h4>
                            <ul class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                                @forelse($section->subjects as $subject)
                                    <li class="py-3 flex justify-between items-center">
                                        <span>{{ $subject->name }}</span>
                                        <form action="{{ route('sections.subjects.detach', [$section, $subject]) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </li>
                                @empty
                                    <li class="py-4 text-center text-gray-500">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        No subjects assigned
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                        
                        <!-- Add Subjects Panel -->
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-plus-circle mr-2 text-green-500"></i>
                                Add Subjects
                            </h4>
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <form action="{{ route('sections.subjects.attach', $section) }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label class="block text-gray-700 mb-2 font-medium">
                                            Available Subjects
                                        </label>
                                        <div class="flex mb-2">
                                            <input type="text" placeholder="Search subjects..." 
                                                   class="w-full border rounded-lg px-3 py-2"
                                                   x-model="subjectSearch">
                                        </div>
                                        <select name="subject_ids[]" multiple 
                                                class="w-full border rounded-lg p-2 h-64"
                                                x-ref="subjectSelect">
                                            @foreach($availableSubjects as $subject)
                                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex justify-between">
                                        <button type="button" @click="showSubjectManager = false" 
                                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100">
                                            <i class="fas fa-times mr-1"></i> Cancel
                                        </button>
                                        <button type="submit" 
                                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                            <i class="fas fa-save mr-1"></i> Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('subjectManager', () => ({
        subjectSearch: '',
        
        init() {
            // Add search functionality
            this.$watch('subjectSearch', (value) => {
                const options = this.$refs.subjectSelect.options;
                const search = value.toLowerCase();
                
                for (let i = 0; i < options.length; i++) {
                    const text = options[i].text.toLowerCase();
                    options[i].style.display = text.includes(search) ? '' : 'none';
                }
            });
        }
    }));
});
</script>
@endpush