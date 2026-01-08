<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Create Fee</h2>
                <p class="text-gray-600 mt-1">Add a new fee item to your school</p>
            </div>
            <a href="{{ route('fees.index') }}" 
               wire:navigate
               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form wire:submit.prevent="createFee" class="p-6">
            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name"
                           wire:model="name"
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                           placeholder="e.g., Monthly Tuition, Admission Fee">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fee Category -->
                <div>
                    <label for="fee_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Fee Category <span class="text-red-500">*</span>
                    </label>
                    <select id="fee_category_id"
                            wire:model="fee_category_id"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('fee_category_id') border-red-500 @enderror">
                        <option value="">Select a category</option>
                        @foreach($feeCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('fee_category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if($feeCategories->isEmpty())
                        <p class="mt-2 text-sm text-amber-600">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            No fee categories available. 
                            <a href="{{ route('fee-categories.create') }}" wire:navigate class="underline">Create one first</a>
                        </p>
                    @endif
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="description"
                              wire:model="description"
                              rows="4"
                              class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                              placeholder="Enter a description for this fee"></textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('fees.index') }}" 
                       wire:navigate
                       class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                            @if($feeCategories->isEmpty()) disabled @endif>
                        <i class="fas fa-save mr-2"></i>Create Fee
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>