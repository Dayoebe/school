{{-- partials/custom-items.blade.php --}}
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-slate-700 to-slate-900 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-white">
                <i class="fas fa-cubes mr-2"></i>Custom Timetable Items
            </h2>
            <button wire:click="switchMode('list')"
                    class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-lg hover:bg-white/30 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </button>
        </div>
    </div>

    <div class="p-6 space-y-6">
        @if(session('success'))
            <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                {{ $customItemId ? 'Edit Custom Item' : 'Create Custom Item' }}
            </h3>

            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Item Name</label>
                    <input type="text"
                           wire:model="customItemName"
                           placeholder="e.g. Break, Assembly, Lunch"
                           class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-slate-500 focus:border-slate-500">
                    @error('customItemName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-end gap-2">
                    <button wire:click="saveCustomItem"
                            class="px-5 py-2.5 bg-slate-800 text-white rounded-lg font-semibold hover:bg-slate-700 transition">
                        <i class="fas fa-save mr-2"></i>{{ $customItemId ? 'Update' : 'Create' }}
                    </button>

                    @if($customItemId)
                        <button wire:click="cancelCustomItemEdit"
                                class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-100 transition">
                            Cancel
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
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
                                    <button wire:click="editCustomItem({{ $item->id }})"
                                            class="px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="deleteCustomItem({{ $item->id }})"
                                            wire:confirm="Delete this custom item?"
                                            class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl text-gray-300 mb-2"></i>
                                <p>No custom items available yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
