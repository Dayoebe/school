<div class="animate__animated animate__fadeIn">
    @if(session('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg animate__animated animate__fadeInDown">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('message') }}</span>
            </div>
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">My Grades</h2>
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-500">Semester: {{ $semester->name ?? 'N/A' }}</span>
        </div>
    </div>

    @if($loading)
        <div class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-500 mb-3 animate__animated animate__pulse"></i>
            <p class="text-gray-600">Loading grades...</p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CA1</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CA2</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CA3</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CA4</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($subjects as $subject)
                            @php
                                $grade = $grades[$subject->id] ?? null;
                                $total = $grade ? ($grade['ca1_score'] ?? 0) + ($grade['ca2_score'] ?? 0) + 
                                            ($grade['ca3_score'] ?? 0) + ($grade['ca4_score'] ?? 0) + 
                                            ($grade['exam_score'] ?? 0) : 0;
                                $gradeLetter = $this->calculateGrade($total);
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-book text-blue-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $subject->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $subject->code ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $grade && $grade['ca1_score'] >= 7 ? 'text-green-600 font-semibold' : 'text-gray-500' }}">
                                    {{ $grade['ca1_score'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $grade && $grade['ca2_score'] >= 7 ? 'text-green-600 font-semibold' : 'text-gray-500' }}">
                                    {{ $grade['ca2_score'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $grade && $grade['ca3_score'] >= 7 ? 'text-green-600 font-semibold' : 'text-gray-500' }}">
                                    {{ $grade['ca3_score'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $grade && $grade['ca4_score'] >= 7 ? 'text-green-600 font-semibold' : 'text-gray-500' }}">
                                    {{ $grade['ca4_score'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $grade && $grade['exam_score'] >= 36 ? 'text-green-600 font-semibold' : 'text-gray-500' }}">
                                    {{ $grade['exam_score'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $total >= 60 ? 'text-green-600' : ($total >= 40 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $total }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $gradeLetter === 'A1' || $gradeLetter === 'B2' || $gradeLetter === 'B3' ? 'bg-green-100 text-green-800' : 
                                           ($gradeLetter === 'C4' || $gradeLetter === 'C5' || $gradeLetter === 'C6' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($gradeLetter === 'D7' || $gradeLetter === 'E8' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')) }}">
                                        {{ $gradeLetter }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="editGrade({{ $subject->id }})" 
                                            class="text-blue-600 hover:text-blue-900 flex items-center">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($isEditing)
        <div x-show="isEditing" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 animate__animated animate__fadeIn">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl animate__animated animate__fadeInUp">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800">Edit Grades for {{ $selectedSubject->name }}</h3>
                        <button @click="isEditing = false" class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form wire:submit.prevent="saveGrade">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CA1 (10)</label>
                                <input type="number" wire:model="grades.{{ $selectedSubject->id }}.ca1_score" 
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition"
                                       min="0" max="10" step="0.1" placeholder="0-10">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CA2 (10)</label>
                                <input type="number" wire:model="grades.{{ $selectedSubject->id }}.ca2_score" 
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition"
                                       min="0" max="10" step="0.1" placeholder="0-10">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CA3 (10)</label>
                                <input type="number" wire:model="grades.{{ $selectedSubject->id }}.ca3_score" 
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition"
                                       min="0" max="10" step="0.1" placeholder="0-10">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CA4 (10)</label>
                                <input type="number" wire:model="grades.{{ $selectedSubject->id }}.ca4_score" 
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition"
                                       min="0" max="10" step="0.1" placeholder="0-10">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Exam (60)</label>
                                <input type="number" wire:model="grades.{{ $selectedSubject->id }}.exam_score" 
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition"
                                       min="0" max="60" step="0.1" placeholder="0-60">
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="isEditing = false" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
                                <i class="fas fa-save mr-2"></i> Save Grades
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>