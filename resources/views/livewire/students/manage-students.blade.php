<div x-data="{ 
    showBulkModal: @entangle('showBulkModal')
}" class="space-y-6">

    @if($mode === 'list')
        @include('livewire.students.partials.list-view')
    @elseif($mode === 'create')
        @include('livewire.students.partials.create-form')
    @elseif($mode === 'edit')
        @include('livewire.students.partials.edit-form')
    @elseif($mode === 'graduate')
        @include('livewire.students.partials.graduate-students')
    @endif

    <!-- Bulk Action Modal -->
    <div x-show="showBulkModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75" @click="showBulkModal = false"></div>

            <div x-show="showBulkModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 class="inline-block w-full max-w-lg my-8 bg-white shadow-2xl rounded-2xl relative z-50">
                
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white">
                            <i class="fas fa-users mr-2"></i>
                            {{ $bulkAction === 'assign_section' ? 'Assign to Section' : 'Move to Class' }}
                        </h3>
                        <button @click="showBulkModal = false" wire:click="closeBulkModal" class="text-white hover:text-gray-200">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                </div>

                <form wire:submit.prevent="executeBulkAction">
                    <div class="p-6 space-y-4">
                        @if($bulkAction === 'move_class')
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Select Target Class *
                                </label>
                                <select wire:model.live="bulkClass" class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-purple-500">
                                    <option value="">Choose Class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if(count($bulkClassSections) > 0 || $bulkAction === 'assign_section')
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Select Section {{ $bulkAction === 'assign_section' ? '*' : '' }}
                                </label>
                                <select wire:model="bulkSection" class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-purple-500">
                                    <option value="">{{ $bulkAction === 'assign_section' ? 'Choose Section' : 'No Section' }}</option>
                                    @foreach(($bulkAction === 'assign_section' ? $sections : $bulkClassSections) as $section)
                                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-2xl">
                        <button type="button" @click="showBulkModal = false" wire:click="closeBulkModal"
                                class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-pink-700 shadow-lg">
                            <i class="fas fa-check mr-2"></i>Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>