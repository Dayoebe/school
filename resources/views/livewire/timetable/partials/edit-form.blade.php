<div class="rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-edit mr-2"></i>Edit Timetable
            </h2>
            @if($canReadTimetable)
                <button wire:click="switchMode('list')"
                    class="rounded-lg bg-gray-100 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </button>
            @else
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="rounded-lg bg-gray-100 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            @endif
        </div>
    </div>

    <form wire:submit.prevent="updateTimetable" class="space-y-6 p-6">
        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Timetable Name <span class="text-red-500">*</span>
            </label>
            <input type="text" wire:model="name"
                class="w-full rounded-lg border border-gray-300 p-3 focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Enter timetable name">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">Description</label>
            <textarea wire:model="description" rows="3"
                class="w-full rounded-lg border border-gray-300 p-3 focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Enter description"></textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">Class</label>
            <select wire:model="my_class_id" disabled
                class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 p-3">
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-sm text-gray-500">Class cannot be changed after creation.</p>
        </div>

        <div class="flex justify-end gap-3">
            @if($canReadTimetable)
                <button type="button" wire:click="switchMode('list')"
                    class="rounded-lg border border-gray-300 px-6 py-2.5 font-semibold text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
            @else
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="rounded-lg border border-gray-300 px-6 py-2.5 font-semibold text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
            @endif
            <button type="submit"
                class="rounded-lg bg-indigo-600 px-6 py-2.5 font-semibold text-white hover:bg-indigo-700">
                <i class="fas fa-save mr-2"></i>Update Timetable
            </button>
        </div>
    </form>
</div>
