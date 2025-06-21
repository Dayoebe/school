{{-- <div x-data="{ isOpen: @entangle('bulkEditMode') }" 
     x-show="isOpen"
     class="fixed inset-0 z-50 overflow-y-auto"
     x-transition>
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="isOpen = false">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal content -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full"
             x-on:click.away="isOpen = false">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Bulk Edit - {{ \App\Models\Subject::find($selectedSubjectForBulkEdit)?->name }}
                    </h3>
                    <button @click="isOpen = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @if(session('success'))
                    <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">1st CA (10)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">2nd CA (10)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">3rd CA (10)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">4th CA (10)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam (60)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($bulkStudents as $student)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $student->user->name }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input wire:model.defer="bulkResults.{{ $student->id }}.ca1_score" 
                                           type="number" 
                                           min="0" 
                                           max="10"
                                           class="w-16 border rounded px-2 py-1">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input wire:model.defer="bulkResults.{{ $student->id }}.ca2_score" 
                                           type="number" 
                                           min="0" 
                                           max="10"
                                           class="w-16 border rounded px-2 py-1">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input wire:model.defer="bulkResults.{{ $student->id }}.ca3_score" 
                                           type="number" 
                                           min="0" 
                                           max="10"
                                           class="w-16 border rounded px-2 py-1">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input wire:model.defer="bulkResults.{{ $student->id }}.ca4_score" 
                                           type="number" 
                                           min="0" 
                                           max="10"
                                           class="w-16 border rounded px-2 py-1">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input wire:model.defer="bulkResults.{{ $student->id }}.exam_score" 
                                           type="number" 
                                           min="0" 
                                           max="60"
                                           class="w-20 border rounded px-2 py-1">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    {{ 
                                        ($bulkResults[$student->id]['ca1_score'] ?? 0) + 
                                        ($bulkResults[$student->id]['ca2_score'] ?? 0) + 
                                        ($bulkResults[$student->id]['ca3_score'] ?? 0) + 
                                        ($bulkResults[$student->id]['ca4_score'] ?? 0) + 
                                        ($bulkResults[$student->id]['exam_score'] ?? 0)
                                    }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input wire:model.defer="bulkResults.{{ $student->id }}.comment" 
                                           type="text" 
                                           class="w-full border rounded px-2 py-1">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="saveBulkResults" 
                        type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Save All Results
                </button>
                <button @click="isOpen = false" 
                        type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div> --}}