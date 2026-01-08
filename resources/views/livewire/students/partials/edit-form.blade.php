{{-- partials/edit-form.blade.php --}}
<div class="bg-white rounded-lg shadow-lg p-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-user-edit mr-2 text-amber-600"></i>Edit Student
        </h2>
        <button wire:click="switchMode('list')" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>Back to List
        </button>
    </div>

    <form wire:submit.prevent="updateStudent" class="space-y-6">
        <!-- Personal Information -->
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Personal Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-3 border-2 @error('name') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-amber-500">
                    @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                    <input type="email" wire:model="email" class="w-full px-4 py-3 border-2 @error('email') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-amber-500">
                    @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">New Password (leave empty to keep current)</label>
                    <input type="password" wire:model="password" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    @error('password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Gender *</label>
                    <select wire:model="gender" class="w-full px-4 py-3 border-2 @error('gender') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-amber-500">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                    @error('gender') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Birthday</label>
                    <input type="date" wire:model="birthday" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    @error('birthday') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                    <input type="tel" wire:model="phone" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    @error('phone') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Blood Group</label>
                    <select wire:model="blood_group" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
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
                    @error('blood_group') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Religion</label>
                    <input type="text" wire:model="religion" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nationality</label>
                    <input type="text" wire:model="nationality" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">State</label>
                    <input type="text" wire:model="state" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                    <input type="text" wire:model="city" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                    <textarea wire:model="address" rows="3" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"></textarea>
                </div>
            </div>
        </div>

        <!-- Academic Information -->
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Academic Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Class *</label>
                    <select wire:model.live="my_class_id" class="w-full px-4 py-3 border-2 @error('my_class_id') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-amber-500">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('my_class_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Section</label>
                    <select wire:model="section_id" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                        <option value="">Select Section</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Admission Number *</label>
                    <input type="text" wire:model="admission_number" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500" readonly>
                    <p class="text-xs text-gray-500 mt-1">Admission number cannot be changed</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Admission Date</label>
                    <input type="date" wire:model="admission_date" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" wire:click="switchMode('list')" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg">
                Cancel
            </button>
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-semibold rounded-lg shadow-lg">
                <i class="fas fa-save mr-2"></i>Update Student
            </button>
        </div>
    </form>
</div>