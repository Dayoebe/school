{{-- partials/create-form.blade.php --}}
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-green-600 to-teal-600 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-white">
                <i class="fas fa-plus-circle mr-2"></i>Create Timetable
            </h2>
            <button wire:click="switchMode('list')" 
                    class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-lg hover:bg-white/30 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </button>
        </div>
    </div>

    <form wire:submit.prevent="createTimetable" class="p-6 space-y-6">
        @if(session('error'))
            <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Timetable Name <span class="text-red-500">*</span>
            </label>
            <input type="text" wire:model="name" 
                   class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                   placeholder="Enter timetable name">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
            <textarea wire:model="description" rows="3"
                      class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      placeholder="Enter description"></textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Select Class <span class="text-red-500">*</span>
            </label>
            <select wire:model="my_class_id" 
                    class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Choose a class</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
            @error('my_class_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" 
                    class="px-6 py-2.5 bg-gradient-to-r from-green-600 to-teal-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition">
                <i class="fas fa-check mr-2"></i>Create Timetable
            </button>
        </div>
    </form>
</div>
