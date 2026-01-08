<div class="space-y-6" x-data="{ 
    feeAmounts: @entangle('selectedFees'),
    calculateTotal(fee) {
        return (parseInt(fee.amount || 0) - parseInt(fee.waiver || 0) + parseInt(fee.fine || 0));
    }
}">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Create Fee Invoice</h2>
                <p class="text-gray-600 mt-1">Generate invoices for students</p>
            </div>
            <a href="{{ route('fee-invoices.index') }}" 
               wire:navigate
               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
        </div>
    </div>

    <form wire:submit.prevent="createFeeInvoice" class="space-y-6">
        <!-- Invoice Settings -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Invoice Settings</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="issue_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Issue Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="issue_date"
                           wire:model="issue_date"
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('issue_date') border-red-500 @enderror">
                    @error('issue_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Due Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="due_date"
                           wire:model="due_date"
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('due_date') border-red-500 @enderror">
                    @error('due_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-3">
                    <label for="note" class="block text-sm font-medium text-gray-700 mb-2">
                        Note
                    </label>
                    <textarea id="note"
                              wire:model="note"
                              rows="3"
                              class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Optional note for this invoice"></textarea>
                </div>
            </div>
        </div>

        <!-- Select Students -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Select Students</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                    <select wire:model.live="selectedClass" 
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Section</label>
                    <select wire:model.live="selectedSection" 
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            @if(!$selectedClass) disabled @endif>
                        <option value="">All Sections</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                    <select wire:model="selectedStudent" 
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            @if(!$selectedClass) disabled @endif>
                        <option value="">All Students</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="button" 
                            wire:click="addStudent"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            @if(!$selectedClass) disabled @endif>
                        <i class="fas fa-plus mr-2"></i>Add Student(s)
                    </button>
                </div>
            </div>

            @if(!empty($selectedStudents))
                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($selectedStudents as $student)
                                <tr>
                                    <td class="px-4 py-3 text-sm">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">{{ $student['name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $student['email'] }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <button type="button" 
                                                wire:click="removeStudent({{ $student['id'] }})"
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-2 text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ count($selectedStudents) }} student(s) selected
                </p>
            @else
                <div class="text-center py-8 bg-gray-50 rounded-lg">
                    <i class="fas fa-user-plus text-gray-300 text-4xl mb-2"></i>
                    <p class="text-gray-500">No students selected</p>
                    <p class="text-gray-400 text-sm">Use the filters above to add students</p>
                </div>
            @endif
        </div>

        <!-- Select Fees -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Select Fees</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fee Category</label>
                    <select wire:model.live="selectedFeeCategory" 
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Category</option>
                        @foreach($feeCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fee</label>
                    <select wire:model="selectedFee" 
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            @if(!$selectedFeeCategory) disabled @endif>
                        <option value="">All Fees</option>
                        @foreach($fees as $fee)
                            <option value="{{ $fee->id }}">{{ $fee->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="button" 
                            wire:click="addFee"
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                            @if(!$selectedFeeCategory) disabled @endif>
                        <i class="fas fa-plus mr-2"></i>Add Fee(s)
                    </button>
                </div>
            </div>

            @if(!empty($selectedFees))
                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fee Name</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Waiver</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Fine</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($selectedFees as $index => $fee)
                                <tr>
                                    <td class="px-4 py-3 text-sm">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">{{ $fee['name'] }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <input type="number" 
                                               wire:model="selectedFees.{{ $index }}.amount"
                                               class="w-24 rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-right"
                                               min="0"
                                               step="0.01">
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <input type="number" 
                                               wire:model="selectedFees.{{ $index }}.waiver"
                                               class="w-24 rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-right"
                                               min="0"
                                               step="0.01">
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <input type="number" 
                                               wire:model="selectedFees.{{ $index }}.fine"
                                               class="w-24 rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-right"
                                               min="0"
                                               step="0.01">
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-medium" x-text="calculateTotal(feeAmounts[{{ $index }}]).toLocaleString()"></td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <button type="button" 
                                                wire:click="removeFee({{ $index }})"
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-2 text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ count($selectedFees) }} fee(s) selected
                </p>
            @else
                <div class="text-center py-8 bg-gray-50 rounded-lg">
                    <i class="fas fa-receipt text-gray-300 text-4xl mb-2"></i>
                    <p class="text-gray-500">No fees selected</p>
                    <p class="text-gray-400 text-sm">Use the filters above to add fees</p>
                </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('fee-invoices.index') }}" 
                   wire:navigate
                   class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                        @if(empty($selectedStudents) || empty($selectedFees)) disabled @endif>
                    <i class="fas fa-save mr-2"></i>Create Invoices
                </button>
            </div>
        </div>
    </form>
</div>