<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-green-600 to-teal-600 px-6 py-4">
        <div class="flex justify-between items-center">
            <h3 class="text-xl font-bold text-white">
                <i class="fas fa-plus-circle mr-2"></i>Create New School
            </h3>
            <button wire:click="switchMode('list')" 
                    class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </button>
        </div>
    </div>

    <form wire:submit.prevent="createSchool" class="p-6">
        <p class="text-gray-600 mb-6">All fields marked with * are required</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">School Name *</label>
                <input type="text" 
                       wire:model="name" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-green-500"
                       placeholder="Enter school name">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Address -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">School Address *</label>
                <textarea wire:model="address" 
                          rows="3"
                          class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-green-500"
                          placeholder="Enter school address"></textarea>
                @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Initials -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">School Initials</label>
                <input type="text" 
                       wire:model="initials" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-green-500"
                       placeholder="e.g., ABC">
                @error('initials') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Phone -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                <input type="tel" 
                       wire:model="phone" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-green-500"
                       placeholder="Enter phone number">
                @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                <input type="email" 
                       wire:model="email" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-green-500"
                       placeholder="school@example.com">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Logo -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">School Logo</label>
                <input type="file" 
                       wire:model="logo" 
                       accept="image/*"
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-green-500">
                @error('logo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                
                @if ($logo)
                    <div class="mt-3">
                        <p class="text-sm text-gray-600 mb-2">Preview:</p>
                        <img src="{{ $logo->temporaryUrl() }}" class="w-24 h-24 object-cover rounded-lg">
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-4">
            <button type="button" 
                    wire:click="switchMode('list')"
                    class="px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                Cancel
            </button>
            <button type="submit" 
                    class="px-6 py-3 bg-gradient-to-r from-green-600 to-teal-600 text-white font-semibold rounded-lg hover:from-green-700 hover:to-teal-700 transition shadow-lg">
                <i class="fas fa-check mr-2"></i>Create School
            </button>
        </div>
    </form>
</div>