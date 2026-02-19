{{-- partials/edit-form.blade.php --}}
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-white">
                <i class="fas fa-edit mr-2"></i>Edit Timetable
            </h2>
            <button wire:click="switchMode('list')"
                    class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-lg hover:bg-white/30 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </button>
        </div>
    </div>

    <form wire:submit.prevent="updateTimetable" class="p-6 space-y-6">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Timetable Name <span class="text-red-500">*</span>
            </label>
            <input type="text" wire:model="name"
                   class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                   placeholder="Enter timetable name">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
            <textarea wire:model="description" rows="3"
                      class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                      placeholder="Enter description"></textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Class</label>
            <select wire:model="my_class_id" disabled
                    class="w-full rounded-lg border-2 border-gray-200 bg-gray-100 p-3 cursor-not-allowed">
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-sm text-gray-500">Class cannot be changed after creation</p>
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" wire:click="switchMode('list')"
                    class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                Cancel
            </button>
            <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-yellow-600 to-orange-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition">
                <i class="fas fa-save mr-2"></i>Update Timetable
            </button>
        </div>
    </form>
</div>
