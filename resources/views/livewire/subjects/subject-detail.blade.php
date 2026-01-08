<div x-data="{ activeTab: @entangle('activeTab') }" class="space-y-6">

    <!-- Header Card -->

<div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-lg shadow-lg overflow-hidden">
    <div class="px-6 py-8">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            <div class="w-32 h-32 rounded-full bg-gradient-to-r from-white/20 to-white/10 border-4 border-white/30 flex items-center justify-center shadow-lg">
                <i class="fas fa-book text-white text-5xl"></i>
            </div>
            
            <div class="flex-1 text-white text-center md:text-left">
                <h1 class="text-3xl font-bold mb-2">{{ $subject->name }}</h1>
                <div class="flex flex-wrap gap-4 justify-center md:justify-start text-sm">
                    <span class="flex items-center">
                        <i class="fas fa-code mr-2"></i>Code: {{ $subject->short_name }}
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-school mr-2"></i>
                        Class: {{ $subject->myClass->name ?? 'N/A' }}
                    </span>
                    @if($subject->myClass && $subject->myClass->classGroup)
                        <span class="flex items-center">
                            <i class="fas fa-layer-group mr-2"></i>
                            Group: {{ $subject->myClass->classGroup->name }}
                        </span>
                    @endif
                    <span class="flex items-center">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>
                        {{ $subject->teachers->count() }} Teacher{{ $subject->teachers->count() !== 1 ? 's' : '' }}
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        {{ $subject->studentRecords->count() }} Student{{ $subject->studentRecords->count() !== 1 ? 's' : '' }}
                    </span>
                </div>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('subjects.index') }}" 
                   class="px-4 py-2 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
                <a href="{{ route('subjects.edit', $subject->id) }}" 
                   class="px-4 py-2 bg-white text-green-600 rounded-lg font-semibold shadow hover:shadow-lg transition">
                    <i class="fas fa-edit mr-2"></i>Edit Subject
                </a>
            </div>
        </div>
    </div>
</div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="flex border-b overflow-x-auto">
            <button @click="activeTab = 'details'"
                :class="activeTab === 'details' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-600 hover:text-green-600'"
                class="px-6 py-4 font-semibold transition whitespace-nowrap">
                <i class="fas fa-info-circle mr-2"></i>Details
            </button>
            <button @click="activeTab = 'teachers'"
                :class="activeTab === 'teachers' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-600 hover:text-green-600'"
                class="px-6 py-4 font-semibold transition whitespace-nowrap">
                <i class="fas fa-chalkboard-teacher mr-2"></i>Teachers ({{ $subject->teachers->count() }})
            </button>
            <button @click="activeTab = 'students'"
                :class="activeTab === 'students' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-600 hover:text-green-600'"
                class="px-6 py-4 font-semibold transition whitespace-nowrap">
                <i class="fas fa-users mr-2"></i>Students ({{ $subject->studentRecords->count() }})
            </button>
            <button @click="activeTab = 'results'"
                :class="activeTab === 'results' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-600 hover:text-green-600'"
                class="px-6 py-4 font-semibold transition whitespace-nowrap">
                <i class="fas fa-chart-bar mr-2"></i>Results
            </button>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Details Tab -->
            <div x-show="activeTab === 'details'" x-transition>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Basic Information -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Subject Information</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <div class="flex items-center">
                                        <i class="fas fa-font text-blue-600 text-2xl mr-4"></i>
                                        <div>
                                            <p class="text-sm text-gray-600">Subject Name</p>
                                            <p class="text-lg font-bold text-gray-900">{{ $subject->name }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <div class="flex items-center">
                                        <i class="fas fa-code text-green-600 text-2xl mr-4"></i>
                                        <div>
                                            <p class="text-sm text-gray-600">Subject Code</p>
                                            <p class="text-lg font-bold text-gray-900">{{ $subject->short_name }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <div class="flex items-center">
                                        <i class="fas fa-school text-purple-600 text-2xl mr-4"></i>
                                        <div>
                                            <p class="text-sm text-gray-600">Class</p>
                                            <p class="text-lg font-bold text-gray-900">
                                                {{ $subject->myClass->name ?? 'Not Assigned' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-alt text-orange-600 text-2xl mr-4"></i>
                                        <div>
                                            <p class="text-sm text-gray-600">Created</p>
                                            <p class="text-lg font-bold text-gray-900">
                                                {{ $subject->created_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Class Information -->
                        @if($subject->myClass)
                            <div class="bg-gradient-to-r from-teal-50 to-green-50 rounded-lg p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-4">Class Information</h3>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="bg-white p-4 rounded-lg shadow-sm">
                                        <div class="flex items-center">
                                            <i class="fas fa-users-class text-teal-600 text-2xl mr-4"></i>
                                            <div>
                                                <p class="text-sm text-gray-600">Class Name</p>
                                                <p class="text-lg font-bold text-gray-900">{{ $subject->myClass->name }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-white p-4 rounded-lg shadow-sm">
                                        <div class="flex items-center">
                                            <i class="fas fa-layer-group text-blue-600 text-2xl mr-4"></i>
                                            <div>
                                                <p class="text-sm text-gray-600">Class Group</p>
                                                <p class="text-lg font-bold text-gray-900">
                                                    {{ $subject->myClass->classGroup->name ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-white p-4 rounded-lg shadow-sm">
                                        <div class="flex items-center">
                                            <i class="fas fa-users text-green-600 text-2xl mr-4"></i>
                                            <div>
                                                <p class="text-sm text-gray-600">Total Students</p>
                                                <p class="text-lg font-bold text-gray-900">
                                                    {{ $subject->myClass->studentsForAcademicYear()->count() }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Quick Stats -->

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                            <div class="flex items-center">
                                <i class="fas fa-chalkboard-teacher text-purple-600 text-xl mr-3"></i>
                                <span class="text-gray-700">Teachers</span>
                            </div>
                            <span class="text-2xl font-bold text-purple-600">{{ $subject->teachers->count() }}</span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                            <div class="flex items-center">
                                <i class="fas fa-users text-green-600 text-xl mr-3"></i>
                                <span class="text-gray-700">Students</span>
                            </div>
                            <span
                                class="text-2xl font-bold text-green-600">{{ $subject->studentRecords->count() }}</span>
                        </div>

                        <!-- Remove or replace the Syllabus stat -->
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                            <div class="flex items-center">
                                <i class="fas fa-book text-blue-600 text-xl mr-3"></i>
                                <span class="text-gray-700">Subjects</span>
                            </div>
                            <span class="text-2xl font-bold text-blue-600">
                                1
                            </span>
                        </div>

                        <!-- Keep Results stat -->
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                            <div class="flex items-center">
                                <i class="fas fa-chart-bar text-orange-600 text-xl mr-3"></i>
                                <span class="text-gray-700">Results</span>
                            </div>
                            <span class="text-2xl font-bold text-orange-600">
                                {{ $subject->results()->count() }}
                            </span>
                        </div>
                    </div>





                </div>
            </div>

            <!-- Teachers Tab -->
            <div x-show="activeTab === 'teachers'" x-transition>
                <div class="space-y-6">
                    <!-- Assign New Teacher -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Assign Teachers</h3>

                        <div class="mb-4">
                            <input type="text" wire:model.live="teacherSearch"
                                placeholder="Search teachers to assign..."
                                class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        @if($teacherSearch)
                            <div class="bg-white rounded-lg border p-4 max-h-64 overflow-y-auto">
                                @forelse($availableTeachers as $teacher)
                                    <div
                                        class="flex items-center justify-between p-3 border-b last:border-b-0 hover:bg-gray-50">
                                        <div>
                                            <span class="font-medium">{{ $teacher->name }}</span>
                                            <span class="text-sm text-gray-600 ml-2">({{ $teacher->email }})</span>
                                        </div>
                                        <button wire:click="assignTeacher({{ $teacher->id }})"
                                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm">
                                            <i class="fas fa-plus mr-1"></i>Assign
                                        </button>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No teachers found</p>
                                @endforelse
                            </div>
                        @endif
                    </div>

                    <!-- Current Teachers -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Assigned Teachers
                            ({{ $subject->teachers->count() }})</h3>

                        @if($subject->teachers->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($subject->teachers as $teacher)
                                    <div class="bg-white border rounded-lg p-4 shadow-sm hover:shadow-md transition">
                                        <div class="flex items-center gap-3 mb-3">
                                            <img src="{{ $teacher->profile_photo_url }}" alt="{{ $teacher->name }}"
                                                class="w-12 h-12 rounded-full object-cover">
                                            <div>
                                                <h4 class="font-bold text-gray-900">{{ $teacher->name }}</h4>
                                                <p class="text-sm text-gray-600">{{ $teacher->email }}</p>
                                            </div>
                                        </div>

                                        <div class="text-sm text-gray-600 mb-3">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-phone"></i>
                                                <span>{{ $teacher->phone ?? 'N/A' }}</span>
                                            </div>
                                        </div>

                                        <div class="flex justify-between items-center pt-3 border-t">
                                            <a href="{{ route('teachers.show', $teacher->id) }}"
                                                class="text-sm text-indigo-600 hover:text-indigo-800">
                                                <i class="fas fa-external-link-alt mr-1"></i>View Profile
                                            </a>
                                            <button wire:click="removeTeacher({{ $teacher->id }})"
                                                wire:confirm="Are you sure you want to remove this teacher from this subject?"
                                                class="text-sm text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash mr-1"></i>Remove
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 rounded-lg">
                                <i class="fas fa-chalkboard-teacher text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-500">No teachers assigned to this subject</p>
                                <p class="text-gray-400 text-sm mt-1">Use the search above to assign teachers</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Students Tab -->
            <div x-show="activeTab === 'students'" x-transition>
                @if($subject->studentRecords->count() > 0)
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-6">Enrolled Students
                            ({{ $subject->studentRecords->count() }})</h3>

                        <div class="overflow-x-auto bg-white rounded-lg shadow">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                            Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                            Admission No.</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Class
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                            Section</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($subject->studentRecords as $record)
                                        @if($record->user)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <img src="{{ $record->user->profile_photo_url }}"
                                                            alt="{{ $record->user->name }}"
                                                            class="w-8 h-8 rounded-full object-cover">
                                                        <div>
                                                            <span class="font-medium text-gray-900">{{ $record->user->name }}</span>
                                                            <p class="text-sm text-gray-500">{{ $record->user->email }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-gray-700">
                                                    {{ $record->admission_number ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                                                        {{ $record->myClass->name ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">
                                                        {{ $record->section->name ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <a href="{{ route('students.show', $record->user->id) }}"
                                                        class="text-indigo-600 hover:text-indigo-800 text-sm">
                                                        <i class="fas fa-external-link-alt mr-1"></i>View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg">
                        <i class="fas fa-users text-gray-300 text-5xl mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">No Students Enrolled</h3>
                        <p class="text-gray-500 max-w-md mx-auto">
                            Students will be automatically enrolled in this subject when they are assigned to the class.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Results Tab -->
            <div x-show="activeTab === 'results'" x-transition>
                <div class="text-center py-12 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg">
                    <i class="fas fa-chart-bar text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">Subject Results</h3>
                    <p class="text-gray-500 max-w-md mx-auto">
                        View and manage results for this subject. Upload exam scores and generate reports.
                    </p>
                    <!-- Find this section in your subject-detail.blade.php -->
                    <div class="flex flex-wrap gap-3 justify-center mt-6">
                        <a href="{{ route('result.upload.individual') }}"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-upload mr-2"></i>Upload Results
                        </a>
                        <a href="{{ route('result.view.subject') }}"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-chart-line mr-2"></i>View Analytics
                        </a>

                        <a href="{{ route('subjects.edit', $subject->id) }}"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-edit mr-2"></i>Edit Subject
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>