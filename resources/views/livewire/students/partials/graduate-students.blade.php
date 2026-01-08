{{-- partials/graduate-students.blade.php --}}
<div class="bg-white rounded-lg shadow-lg p-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-graduation-cap mr-2 text-green-600"></i>Graduate Students
        </h2>
        <button wire:click="switchMode('list')" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>Back to List
        </button>
    </div>

    <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-lg mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Class *</label>
                <select wire:model.live="graduateClass" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg">
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Section (Optional)</label>
                <select wire:model.live="graduateSection" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg">
                    <option value="">All Sections</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button wire:click="loadStudentsToGraduate" class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg">
                    <i class="fas fa-search mr-2"></i>Load Students
                </button>
            </div>
        </div>
    </div>

    @if(count($studentsToGraduate) > 0)
        <div class="mb-6 flex gap-3">
            <button wire:click="setAllGraduate(true)" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                <i class="fas fa-check-double mr-2"></i>Select All to Graduate
            </button>
            <button wire:click="setAllGraduate(false)" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                <i class="fas fa-times-circle mr-2"></i>Deselect All
            </button>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Student</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Admission No</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($studentsToGraduate as $student)
                        <tr class="hover:bg-green-50">
                            <td class="px-6 py-4 font-semibold">{{ $student->name }}</td>
                            <td class="px-6 py-4">{{ $student->studentRecord->admission_number }}</td>
                            <td class="px-6 py-4">
                                <select wire:model="graduationDecisions.{{ $student->id }}" class="px-4 py-2 border-2 border-gray-300 rounded-lg">
                                    <option value="1">Graduate</option>
                                    <option value="0">Don't Graduate</option>
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            <button wire:click="graduateStudents" class="px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-lg shadow-lg text-lg">
                <i class="fas fa-graduation-cap mr-2"></i>Graduate Selected Students
            </button>
        </div>
    @elseif($graduateClass)
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
            <p class="text-lg text-gray-500">No students found in selected class/section</p>
        </div>
    @endif
</div>