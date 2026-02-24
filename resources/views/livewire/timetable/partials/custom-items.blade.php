<div class="rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-cubes mr-2"></i>Custom Timetable Items
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

    <div class="space-y-6 p-6">
        @if(session('success'))
            <div class="rounded border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if($canCreateCustomItems || $canUpdateCustomItems)
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">
                    {{ $customItemId ? 'Edit Custom Item' : 'Create Custom Item' }}
                </h3>

                <div class="flex flex-col gap-4 md:flex-row">
                    <div class="flex-1">
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Item Name</label>
                        <input type="text"
                            wire:model="customItemName"
                            placeholder="e.g. Break, Assembly, Lunch"
                            class="w-full rounded-lg border border-gray-300 p-3 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('customItemName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-end gap-2">
                        <button wire:click="saveCustomItem"
                            class="rounded-lg bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-700">
                            <i class="fas fa-save mr-2"></i>{{ $customItemId ? 'Update' : 'Create' }}
                        </button>

                        @if($customItemId)
                            <button wire:click="cancelCustomItemEdit"
                                class="rounded-lg border border-gray-300 px-5 py-2.5 font-semibold text-gray-700 hover:bg-gray-100">
                                Cancel
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-gray-200 bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($customItems as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $item->name }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    @if($canUpdateCustomItems)
                                        <button wire:click="editCustomItem({{ $item->id }})"
                                            class="rounded-lg bg-yellow-100 px-3 py-1.5 text-yellow-700 hover:bg-yellow-200">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endif

                                    @if($canDeleteCustomItems)
                                        <button wire:click="deleteCustomItem({{ $item->id }})"
                                            wire:confirm="Delete this custom item?"
                                            class="rounded-lg bg-red-100 px-3 py-1.5 text-red-700 hover:bg-red-200">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox mb-2 text-3xl text-gray-300"></i>
                                <p>No custom items available yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
