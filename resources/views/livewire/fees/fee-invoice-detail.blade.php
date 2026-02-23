<div x-data="{ activeTab: @entangle('activeTab') }" class="space-y-6">
    @php
        $studentRecord = $feeInvoice->user->studentRecord;
    @endphp
    
    <!-- Header Card -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-8">
            <div class="flex flex-col md:flex-row items-start gap-6">
                <div class="flex-1 text-white">
                    <h1 class="text-3xl font-bold mb-2">{{ $feeInvoice->name }}</h1>
                    <div class="grid grid-cols-2 gap-4 text-sm mt-4">
                        <div>
                            <p class="opacity-75">Student</p>
                            <p class="font-semibold text-lg">{{ $feeInvoice->user->name }}</p>
                        </div>
                        <div>
                            <p class="opacity-75">Class</p>
                            <p class="font-semibold">
                                {{ $studentRecord?->myClass?->name ?? 'N/A' }}
                                @if($studentRecord?->section?->name)
                                    - {{ $studentRecord->section->name }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="opacity-75">Issue Date</p>
                            <p class="font-semibold">{{ $feeInvoice->issue_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="opacity-75">Due Date</p>
                            <p class="font-semibold">{{ $feeInvoice->due_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Status Badge -->
                <div class="self-start">
                    @if($feeInvoice->balance->isLessThanOrEqualTo(0))
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-green-500 text-white">
                            <i class="fas fa-check-circle mr-2"></i>PAID
                        </span>
                    @elseif($feeInvoice->paid->isGreaterThan(0))
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-yellow-500 text-white">
                            <i class="fas fa-exclamation-circle mr-2"></i>PARTIAL
                        </span>
                    @else
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-red-500 text-white">
                            <i class="fas fa-times-circle mr-2"></i>UNPAID
                        </span>
                    @endif
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                    <p class="text-white/75 text-sm">Total Amount</p>
                    <p class="text-white text-2xl font-bold">{{ $feeInvoice->amount->formatTo(app()->getLocale()) }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                    <p class="text-white/75 text-sm">Waiver</p>
                    <p class="text-white text-2xl font-bold">{{ $feeInvoice->waiver->formatTo(app()->getLocale()) }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                    <p class="text-white/75 text-sm">Paid</p>
                    <p class="text-green-300 text-2xl font-bold">{{ $feeInvoice->paid->formatTo(app()->getLocale()) }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                    <p class="text-white/75 text-sm">Balance</p>
                    <p class="text-red-300 text-2xl font-bold">{{ $feeInvoice->balance->formatTo(app()->getLocale()) }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 mt-6">
                <a href="{{ route('fee-invoices.index') }}" 
                   wire:navigate
                   class="px-4 py-2 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
                @if($canManage)
                    <a href="{{ route('fee-invoices.edit', $feeInvoice->id) }}" 
                       wire:navigate
                       class="px-4 py-2 bg-white text-indigo-600 rounded-lg font-semibold shadow hover:shadow-lg transition">
                        <i class="fas fa-edit mr-2"></i>Edit Invoice
                    </a>
                @endif
                <a href="{{ route('fee-invoices.print', $feeInvoice->id) }}" 
                   target="_blank"
                   class="px-4 py-2 bg-white text-indigo-600 rounded-lg font-semibold shadow hover:shadow-lg transition">
                    <i class="fas fa-print mr-2"></i>Print
                </a>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="flex border-b overflow-x-auto">
            <button wire:click="changeTab('details')"
                    @click="activeTab = 'details'" 
                    :class="activeTab === 'details' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:text-indigo-600'"
                    class="px-6 py-4 font-semibold transition whitespace-nowrap">
                <i class="fas fa-file-invoice mr-2"></i>Details
            </button>
            @if($canManage)
                <button wire:click="changeTab('payments')"
                        @click="activeTab = 'payments'" 
                        :class="activeTab === 'payments' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:text-indigo-600'"
                        class="px-6 py-4 font-semibold transition whitespace-nowrap">
                    <i class="fas fa-money-bill-wave mr-2"></i>Payments
                </button>
                <button wire:click="changeTab('manage')"
                        @click="activeTab = 'manage'" 
                        :class="activeTab === 'manage' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:text-indigo-600'"
                        class="px-6 py-4 font-semibold transition whitespace-nowrap">
                    <i class="fas fa-cog mr-2"></i>Manage Fees
                </button>
            @endif
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Details Tab -->
            <div x-show="activeTab === 'details'" x-transition>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Fee Items</h3>
                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fee Name</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Waiver</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Fine</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($feeInvoice->feeInvoiceRecords as $record)
                                @php
                                    $balance = $record->amount->plus($record->fine)->minus($record->waiver)->minus($record->paid);
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $record->fee->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">{{ $record->amount->formatTo(app()->getLocale()) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-orange-600">{{ $record->waiver->formatTo(app()->getLocale()) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">{{ $record->fine->formatTo(app()->getLocale()) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">{{ $record->paid->formatTo(app()->getLocale()) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                                        <span class="{{ $balance->isLessThanOrEqualTo(0) ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $balance->formatTo(app()->getLocale()) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($feeInvoice->note)
                    <div class="mt-6 bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-2">Note:</h4>
                        <p class="text-gray-700">{{ $feeInvoice->note }}</p>
                    </div>
                @endif
            </div>

            @if($canManage)
            <!-- Payments Tab -->
            <div x-show="activeTab === 'payments'" x-transition>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Add Payments</h3>
                
                @foreach($feeInvoice->feeInvoiceRecords as $record)
                    @php
                        $balance = $record->amount->plus($record->fine)->minus($record->waiver)->minus($record->paid);
                    @endphp
                    <div class="border rounded-lg p-4 mb-4 {{ $balance->isLessThanOrEqualTo(0) ? 'bg-green-50 border-green-200' : '' }}">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $record->fee->name }}</h4>
                                <div class="text-sm text-gray-600 mt-1 space-y-1">
                                    <p>Amount: <span class="font-medium">{{ $record->amount->formatTo(app()->getLocale()) }}</span></p>
                                    <p>Waiver: <span class="font-medium text-orange-600">{{ $record->waiver->formatTo(app()->getLocale()) }}</span></p>
                                    <p>Fine: <span class="font-medium text-red-600">{{ $record->fine->formatTo(app()->getLocale()) }}</span></p>
                                    <p>Paid: <span class="font-medium text-green-600">{{ $record->paid->formatTo(app()->getLocale()) }}</span></p>
                                    <p>Balance: <span class="font-bold {{ $balance->isLessThanOrEqualTo(0) ? 'text-green-600' : 'text-red-600' }}">{{ $balance->formatTo(app()->getLocale()) }}</span></p>
                                </div>
                            </div>
                            @if($balance->isGreaterThan(0))
                                @if($payingRecordId === $record->id)
                                    <div class="flex gap-2">
                                        <input type="number" 
                                               wire:model="paymentAmount"
                                               step="0.01"
                                               min="0.01"
                                               max="{{ $balance->getAmount()->toFloat() }}"
                                               placeholder="Amount"
                                               class="w-32 rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        <button wire:click="addPayment" 
                                                class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button wire:click="cancelPayment" 
                                                class="px-3 py-1 bg-gray-400 text-white text-sm rounded hover:bg-gray-500">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @else
                                    <button wire:click="startPayment({{ $record->id }})" 
                                            class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                                        <i class="fas fa-plus mr-1"></i>Add Payment
                                    </button>
                                @endif
                            @else
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>Paid
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            @if($canManage)
            <!-- Manage Tab -->
            <div x-show="activeTab === 'manage'" x-transition>
                <div class="space-y-6">
                    <!-- Edit Existing Fees -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Edit Fee Items</h3>
                        
                        @foreach($feeInvoice->feeInvoiceRecords as $record)
                            <div class="border rounded-lg p-4 mb-4">
                                @if($editingRecordId === $record->id)
                                    <form wire:submit.prevent="updateRecord" class="space-y-3">
                                        <h4 class="font-bold text-gray-900">{{ $record->fee->name }}</h4>
                                        <div class="grid grid-cols-3 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                                                <input type="number" 
                                                       wire:model="editAmount"
                                                       step="0.01"
                                                       min="0"
                                                       class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Waiver</label>
                                                <input type="number" 
                                                       wire:model="editWaiver"
                                                       step="0.01"
                                                       min="0"
                                                       class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Fine</label>
                                                <input type="number" 
                                                       wire:model="editFine"
                                                       step="0.01"
                                                       min="0"
                                                       class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                                <i class="fas fa-save mr-1"></i>Save
                                            </button>
                                            <button type="button" wire:click="cancelEditingRecord" class="px-4 py-2 bg-gray-400 text-white text-sm rounded hover:bg-gray-500">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h4 class="font-bold text-gray-900">{{ $record->fee->name }}</h4>
                                            <p class="text-sm text-gray-600 mt-1">
                                                Amount: {{ $record->amount->formatTo(app()->getLocale()) }} | 
                                                Waiver: {{ $record->waiver->formatTo(app()->getLocale()) }} | 
                                                Fine: {{ $record->fine->formatTo(app()->getLocale()) }}
                                            </p>
                                        </div>
                                        <div class="flex gap-2">
                                            <button wire:click="startEditingRecord({{ $record->id }})" 
                                                    class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button wire:click="deleteRecord({{ $record->id }})" 
                                                    wire:confirm="Are you sure you want to delete this fee record?"
                                                    class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Add New Fee -->
                    <div class="border-t pt-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Add New Fee</h3>
                        <form wire:submit.prevent="addNewFee" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                        <option value="">Select Fee</option>
                                        @foreach($fees as $fee)
                                            <option value="{{ $fee->id }}">{{ $fee->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedFee')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                                    <input type="number" 
                                           wire:model="newFeeAmount"
                                           step="0.01"
                                           min="0"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('newFeeAmount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Waiver</label>
                                    <input type="number" 
                                           wire:model="newFeeWaiver"
                                           step="0.01"
                                           min="0"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fine</label>
                                    <input type="number" 
                                           wire:model="newFeeFine"
                                           step="0.01"
                                           min="0"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>

                            <button type="submit" 
                                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                                    @if(!$selectedFee) disabled @endif>
                                <i class="fas fa-plus mr-2"></i>Add Fee to Invoice
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
