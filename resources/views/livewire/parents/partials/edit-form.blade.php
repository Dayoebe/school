<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-white">
                <i class="fas fa-user-edit mr-2"></i>Edit Parent
            </h2>
            <button wire:click="switchMode('list')" 
                    class="px-4 py-2 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </button>
        </div>
    </div>

    <!-- Form -->
    <form wire:submit.prevent="updateParent" class="p-6">
        <div class="space-y-6">
            <!-- Personal Information -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-user mr-2 text-purple-600"></i>Personal Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                        <input wire:model="name" type="text" 
                               class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500"
                               placeholder="Enter full name">
                        @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                        <input wire:model="email" type="email" 
                               class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500"
                               placeholder="Enter email address">
                        @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Gender *</label>
                        <select wire:model="gender" 
                                class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                        @error('gender') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Birthday</label>
                        <input wire:model="birthday" type="date" 
                               class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500">
                        @error('birthday') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                        <input wire:model="phone" type="text" 
                               class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500"
                               placeholder="Enter phone number">
                        @error('phone') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Blood Group</label>
                        <select wire:model="blood_group" 
                                class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500">
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
                        <select wire:model="religion" 
                                class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500">
                            <option value="">Select Religion</option>
                            <option value="Christianity">Christianity</option>
                            <option value="Islam">Islam</option>
                            <option value="Hinduism">Hinduism</option>
                            <option value="Buddhism">Buddhism</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nationality</label>
                        <input wire:model="nationality" type="text" 
                               class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500"
                               placeholder="Enter nationality">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">State</label>
                        <input wire:model="state" type="text" 
                               class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500"
                               placeholder="Enter state">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                        <input wire:model="city" type="text" 
                               class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500"
                               placeholder="Enter city">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                        <textarea wire:model="address" rows="3" 
                                  class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500"
                                  placeholder="Enter full address"></textarea>
                    </div>
                </div>
            </div>

            <!-- Change Password (Optional) -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-lock mr-2 text-purple-600"></i>Change Password (Optional)
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                        <input wire:model="password" type="password" 
                               class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500"
                               placeholder="Leave blank to keep current password">
                        @error('password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                        <input wire:model="password_confirmation" type="password" 
                               class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500"
                               placeholder="Confirm new password">
                        @error('password_confirmation') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" wire:click="switchMode('list')" 
                        class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-pink-700 shadow-lg">
                    <i class="fas fa-save mr-2"></i>Update Parent
                </button>
            </div>
        </div>
    </form>
</div>