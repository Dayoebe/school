@props([
    'title',
    'modelPath',
    'fields' => [],
    'description' => null,
    'emptyMessage' => 'No entries added yet.',
    'mode' => 'array',
    'keyField' => 'key',
])

<div
    class="rounded-lg border border-slate-200 bg-slate-50 p-4"
    x-data="jsonCollectionEditor(@entangle($modelPath).defer, @js($fields), @js(['mode' => $mode, 'keyField' => $keyField]))"
    x-init="init()"
>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h4 class="text-sm font-bold text-slate-800">{{ $title }}</h4>
            @if ($description)
                <p class="mt-1 text-xs text-slate-600">{{ $description }}</p>
            @endif
        </div>
        <button type="button" @click="addItem"
            class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
            <i class="fas fa-plus mr-1"></i>Add Item
        </button>
    </div>

    <div x-show="hasAdvancedFields" class="mt-3 flex flex-col gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-[11px] text-slate-500">
            Basic content fields are shown by default.
        </p>
        <button
            type="button"
            @click="showAdvanced = !showAdvanced"
            class="inline-flex items-center rounded-md border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100"
        >
            <i class="fas mr-1" :class="showAdvanced ? 'fa-eye-slash' : 'fa-sliders'"></i>
            <span x-text="showAdvanced ? 'Hide Advanced Styling' : 'Show Advanced Styling'"></span>
        </button>
    </div>

    <div class="mt-3 space-y-3" x-show="items.length > 0">
        <template x-for="(item, index) in items" :key="item._editorId">
            <div class="rounded-lg border border-slate-200 bg-white p-3">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Item <span x-text="index + 1"></span>
                    </p>
                    <button type="button" @click="removeItem(index)"
                        class="rounded-md bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                        Remove
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <template x-for="field in fields" :key="`${index}-${field.key}`">
                        <div x-show="showAdvanced || !field.advanced" :class="field.fullWidth ? 'md:col-span-2' : ''">
                            <label class="mb-1 block text-xs font-semibold text-slate-700" x-text="field.label"></label>

                            <template x-if="field.type === 'textarea' || field.type === 'list'">
                                <div>
                                    <textarea rows="3"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                        :placeholder="field.placeholder || ''"
                                        x-model="item[field.key]"
                                        @input.debounce.200ms="syncModel()"></textarea>
                                    <p x-show="field.type === 'list'" class="mt-1 text-[11px] text-slate-500">Enter one value per line.</p>
                                    <p x-show="field.help" class="mt-1 text-[11px] text-slate-500" x-text="field.help"></p>
                                </div>
                            </template>

                            <template x-if="field.type !== 'textarea' && field.type !== 'list'">
                                <div>
                                    <input type="text"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                        :placeholder="field.placeholder || ''"
                                        x-model="item[field.key]"
                                        @input.debounce.200ms="syncModel()" />
                                    <p x-show="field.help" class="mt-1 text-[11px] text-slate-500" x-text="field.help"></p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <p x-show="items.length === 0" class="mt-3 rounded-lg border border-dashed border-slate-300 bg-white px-3 py-4 text-center text-xs text-slate-500">
        {{ $emptyMessage }}
    </p>

    <details class="mt-3 rounded-lg border border-slate-200 bg-white">
        <summary class="cursor-pointer px-3 py-2 text-xs font-semibold text-slate-700">Advanced JSON (Optional)</summary>
        <div class="space-y-2 border-t border-slate-100 px-3 py-3">
            <textarea rows="8" x-model="rawJson"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="applyRawJson"
                    class="rounded-lg bg-slate-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">
                    Apply JSON
                </button>
                <button type="button" @click="formatRawJson"
                    class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">
                    Format JSON
                </button>
            </div>
            <p x-show="parseError" class="text-xs text-rose-600" x-text="parseError"></p>
        </div>
    </details>

    @error($modelPath)
        <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
