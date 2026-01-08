<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-4">
        <div class="flex justify-between items-center">
            <h3 class="text-xl font-bold text-white">
                <i class="fas fa-user-edit mr-2"></i>Edit Administrator
            </h3>
            <button wire:click="switchMode('list')" 
                    class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </button>
        </div>
    </div>

    <form wire:submit.prevent="updateAdmin" class="p-6">
        <p class="text-gray-600 mb-6">All fields marked with * are required</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                <input type="text" 
                       wire:model="name" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                       placeholder="Enter full name">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address *</label>
                <input type="email" 
                       wire:model="email" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                       placeholder="admin@example.com">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Gender -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Gender *</label>
                <select wire:model="gender" 
                        class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500">
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
                @error('gender') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    New Password <span class="text-xs text-gray-500">(leave blank to keep current)</span>
                </label>
                <input type="password" 
                       wire:model="password" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                       placeholder="Enter new password">
                @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                <input type="password" 
                       wire:model="password_confirmation" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                       placeholder="Confirm new password">
            </div>

            <!-- Birthday -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Birthday</label>
                <input type="date" 
                       wire:model="birthday" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500">
                @error('birthday') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Phone -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                <input type="tel" 
                       wire:model="phone" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                       placeholder="Enter phone number">
                @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Blood Group -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Blood Group</label>
                <input type="text" 
                       wire:model="blood_group" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                       placeholder="e.g., A+">
            </div>

            <!-- Religion -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Religion</label>
                <input type="text" 
                       wire:model="religion" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                       placeholder="Enter religion">
            </div>

            <!-- Nationality -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nationality</label>
                <input type="text" 
                       wire:model="nationality" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                       placeholder="Enter nationality">
            </div>

            <!-- State -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">State</label>
                <input type="text" 
                       wire:model="state" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                       placeholder="Enter state">
            </div>

            <!-- City -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                <input type="text" 
                       wire:model="city" 
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                       placeholder="Enter city">
            </div>

            <!-- Address -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                <textarea wire:model="address" 
                          rows="3"
                          class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                          placeholder="Enter full address"></textarea>
            </div>

            <!-- Profile Photo -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Profile Photo</label>
                <input type="file" 
                       wire:model="profile_photo" 
                       accept="image/*"
                       class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500">
                @error('profile_photo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                
                @if ($profile_photo)
                    <div class="mt-3">
                        <p class="text-sm text-gray-600 mb-2">New Preview:</p>
                        <img src="{{ $profile_photo->temporaryUrl() }}" class="w-24 h-24 object-cover rounded-lg">
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
                    class="px-6 py-3 bg-gradient-to-r from-yellow-600 to-orange-600 text-white font-semibold rounded-lg hover:from-yellow-700 hover:to-orange-700 transition shadow-lg">
                <i class="fas fa-save mr-2"></i>Update Administrator
            </button>
        </div>
    </form>
</div>