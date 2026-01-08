<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">
                <i class="fas fa-user-edit mr-2"></i>Edit Teacher
            </h3>
            <button wire:click="switchMode('list')" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
    </div>

    <form wire:submit="updateTeacher" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="md:col-span-2">
                <h4 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Basic Information</h4>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                <input type="text" wire:model="name" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500"
                       placeholder="Enter full name">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                <input type="email" wire:model="email" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500"
                       placeholder="Enter email address">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">New Password (Leave empty to keep current)</label>
                <input type="password" wire:model="password" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500"
                       placeholder="Enter new password">
                @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Gender *</label>
                <select wire:model="gender" class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
                @error('gender') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                <input type="text" wire:model="phone" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500"
                       placeholder="Enter phone number">
                @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Birthday</label>
                <input type="date" wire:model="birthday" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500">
                @error('birthday') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Additional Information -->
            <div class="md:col-span-2 mt-4">
                <h4 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Additional Information</h4>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Blood Group</label>
                <select wire:model="blood_group" class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Select Blood Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Religion</label>
                <input type="text" wire:model="religion" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500"
                       placeholder="Enter religion">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nationality</label>
                <input type="text" wire:model="nationality" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500"
                       placeholder="Enter nationality">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">State</label>
                <input type="text" wire:model="state" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500"
                       placeholder="Enter state">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                <input type="text" wire:model="city" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500"
                       placeholder="Enter city">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                <textarea wire:model="address" rows="3"
                          class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500"
                          placeholder="Enter full address"></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
            <button type="button" wire:click="switchMode('list')"
                    class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100">
                Cancel
            </button>
            <button type="submit" 
                    class="px-6 py-2.5 bg-gradient-to-r from-yellow-600 to-orange-600 text-white font-semibold rounded-lg hover:from-yellow-700 hover:to-orange-700 shadow-lg">
                <i class="fas fa-save mr-2"></i>Update Teacher
            </button>
        </div>
    </form>
</div>