<div class="rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-plus-circle mr-2"></i>Create Timetable
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

    <form wire:submit.prevent="createTimetable" class="space-y-6 p-6">
        @if(session('error'))
            <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        @if(!$activeSemesterId)
            <div class="rounded border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
                <i class="fas fa-info-circle mr-2"></i>You must set an active semester before creating a timetable.
            </div>
        @endif

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
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Select Class <span class="text-red-500">*</span>
            </label>
            <select wire:model="my_class_id"
                class="w-full rounded-lg border border-gray-300 p-3 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Choose a class</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
            @error('my_class_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="rounded-lg bg-indigo-600 px-6 py-2.5 font-semibold text-white hover:bg-indigo-700"
                @if(!$activeSemesterId) disabled @endif>
                <i class="fas fa-check mr-2"></i>Create Timetable
            </button>
        </div>
    </form>
</div>
