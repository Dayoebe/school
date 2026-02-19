@php
    $palette = [
        'red' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'dot' => 'bg-red-500'],
        'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'dot' => 'bg-orange-500'],
        'amber' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'dot' => 'bg-amber-500'],
        'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'dot' => 'bg-yellow-500'],
        'lime' => ['bg' => 'bg-lime-100', 'text' => 'text-lime-700', 'dot' => 'bg-lime-500'],
        'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'dot' => 'bg-green-500'],
        'emerald' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
        'teal' => ['bg' => 'bg-teal-100', 'text' => 'text-teal-700', 'dot' => 'bg-teal-500'],
        'cyan' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-700', 'dot' => 'bg-cyan-500'],
        'sky' => ['bg' => 'bg-sky-100', 'text' => 'text-sky-700', 'dot' => 'bg-sky-500'],
        'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'dot' => 'bg-blue-500'],
        'indigo' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'dot' => 'bg-indigo-500'],
        'violet' => ['bg' => 'bg-violet-100', 'text' => 'text-violet-700', 'dot' => 'bg-violet-500'],
        'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'dot' => 'bg-purple-500'],
        'fuchsia' => ['bg' => 'bg-fuchsia-100', 'text' => 'text-fuchsia-700', 'dot' => 'bg-fuchsia-500'],
        'pink' => ['bg' => 'bg-pink-100', 'text' => 'text-pink-700', 'dot' => 'bg-pink-500'],
        'rose' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'dot' => 'bg-rose-500'],
        'slate' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'dot' => 'bg-slate-500'],
        'gray' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'dot' => 'bg-gray-500'],
        'zinc' => ['bg' => 'bg-zinc-100', 'text' => 'text-zinc-700', 'dot' => 'bg-zinc-500'],
        'neutral' => ['bg' => 'bg-neutral-100', 'text' => 'text-neutral-700', 'dot' => 'bg-neutral-500'],
        'stone' => ['bg' => 'bg-stone-100', 'text' => 'text-stone-700', 'dot' => 'bg-stone-500'],
    ];
@endphp

<div class="space-y-6" x-data="{ tab: @entangle('activeTab').live }">
    @if (session()->has('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if (!$school)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <h2 class="text-xl font-black text-amber-900">Set Default School First</h2>
            <p class="mt-2 text-sm text-amber-800">
                Gallery is school-based. Set a default school from School settings, then come back.
            </p>
            <a href="{{ route('schools.index') }}"
                class="mt-4 inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-amber-700">
                <i class="fas fa-school"></i>
                <span>Go to Schools</span>
            </a>
        </div>
    @else
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-red-700">Categories</p>
                <p class="mt-1 text-2xl font-black text-red-900">{{ $stats['categories'] }}</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-700">Items</p>
                <p class="mt-1 text-2xl font-black text-blue-900">{{ $stats['items'] }}</p>
            </div>
            <div class="rounded-xl border border-violet-200 bg-violet-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-violet-700">Featured</p>
                <p class="mt-1 text-2xl font-black text-violet-900">{{ $stats['featured'] }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Active</p>
                <p class="mt-1 text-2xl font-black text-emerald-900">{{ $stats['active'] }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Current School</p>
                    <p class="text-lg font-black text-slate-900">{{ $school->name }}</p>
                </div>

                <div class="inline-flex rounded-xl border border-slate-200 bg-slate-50 p-1">
                    <button type="button" @click="tab = 'items'"
                        class="rounded-lg px-4 py-2 text-sm font-bold transition"
                        :class="tab === 'items' ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100'">
                        Gallery Items
                    </button>
                    <button type="button" @click="tab = 'categories'"
                        class="rounded-lg px-4 py-2 text-sm font-bold transition"
                        :class="tab === 'categories' ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100'">
                        Categories
                    </button>
                </div>
            </div>
        </div>

        <div x-show="tab === 'items'" x-transition class="space-y-5">
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-1">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-base font-black text-slate-900">{{ $itemId ? 'Edit Gallery Item' : 'Add Gallery Item' }}</h3>
                        @if ($itemId)
                            <button wire:click="resetItemForm" type="button"
                                class="text-xs font-bold text-red-700 hover:underline">Cancel Edit</button>
                        @endif
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Title</label>
                            <input wire:model="itemTitle" type="text"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                            @error('itemTitle') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Upload Image (Cloudinary)</label>
                            <input wire:model="itemImage" type="file" accept="image/*"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                            <p class="mt-1 text-[11px] text-slate-500">JPG, PNG, WEBP up to 5MB.</p>
                            @error('itemImage') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div wire:loading wire:target="itemImage"
                            class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">
                            Uploading image...
                        </div>

                        @if ($itemImage)
                            <div class="rounded-xl border border-slate-200 p-2">
                                <img src="{{ $itemImage->temporaryUrl() }}" alt="Upload preview" class="h-36 w-full rounded-lg object-cover">
                                <button wire:click="clearItemImage" type="button" class="mt-2 text-xs font-bold text-red-700 hover:underline">
                                    Remove upload
                                </button>
                            </div>
                        @elseif ($itemMediaUrl !== '')
                            <div class="rounded-xl border border-slate-200 p-2">
                                <img src="{{ $this->optimizedImageUrl($itemMediaUrl, 720, 360) }}" alt="Current image preview" class="h-36 w-full rounded-lg object-cover">
                            </div>
                        @endif

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Or Image URL</label>
                            <input wire:model="itemMediaUrl" type="url" placeholder="https://..."
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                            @error('itemMediaUrl') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Category</label>
                            <select wire:model="itemCategoryId"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('itemCategoryId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Taken On</label>
                                <input wire:model="itemTakenOn" type="date"
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                                @error('itemTakenOn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Sort Order</label>
                                <input wire:model="itemSortOrder" type="number" min="0"
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                                @error('itemSortOrder') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Caption</label>
                            <textarea wire:model="itemCaption" rows="3"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"></textarea>
                            @error('itemCaption') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <input wire:model="itemIsFeatured" type="checkbox" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                <span class="text-xs font-semibold text-slate-700">Featured</span>
                            </label>
                            <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <input wire:model="itemIsActive" type="checkbox" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                <span class="text-xs font-semibold text-slate-700">Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <button wire:click="saveItem" type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-700">
                            <i class="fas fa-floppy-disk"></i>
                            <span>{{ $itemId ? 'Update Item' : 'Save Item' }}</span>
                        </button>
                        <button wire:click="resetItemForm" type="button"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                            <i class="fas fa-rotate-right"></i>
                            <span>Reset</span>
                        </button>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
                    <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Search</label>
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search title, caption, URL"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Filter Category</label>
                            <select wire:model="categoryFilter"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                                <option value="all">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @forelse($items as $item)
                            @php
                                $color = $palette[$item->category?->color ?? 'slate'] ?? $palette['slate'];
                            @endphp
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div class="relative">
                                    <img src="{{ $this->optimizedImageUrl($item->media_url, 720, 420) }}" alt="{{ $item->title }}" class="h-44 w-full object-cover">
                                    @if ($item->is_featured)
                                        <span class="absolute right-2 top-2 rounded-full bg-black/70 px-2 py-1 text-[10px] font-bold text-white">FEATURED</span>
                                    @endif
                                </div>
                                <div class="space-y-2 p-4">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold {{ $color['bg'] }} {{ $color['text'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $color['dot'] }}"></span>
                                        {{ $item->category?->name ?? 'Uncategorized' }}
                                    </span>
                                    <h4 class="line-clamp-1 text-sm font-black text-slate-900">{{ $item->title }}</h4>
                                    <p class="line-clamp-2 text-xs text-slate-600">{{ $item->caption ?: 'No caption provided.' }}</p>
                                    <div class="flex flex-wrap gap-2 pt-2">
                                        <button wire:click="editItem({{ $item->id }})"
                                            class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">Edit</button>
                                        <button wire:click="deleteItem({{ $item->id }})"
                                            class="rounded-lg bg-rose-100 px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-200">Delete</button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-600 md:col-span-2 xl:col-span-3">
                                No gallery items yet. Add your first item on the left.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4 border-t border-slate-200 pt-3">
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div x-show="tab === 'categories'" x-transition class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-1">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-black text-slate-900">{{ $categoryId ? 'Edit Category' : 'Create Category' }}</h3>
                    @if ($categoryId)
                        <button wire:click="resetCategoryForm" type="button"
                            class="text-xs font-bold text-red-700 hover:underline">Cancel Edit</button>
                    @endif
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Category Name</label>
                        <input wire:model="categoryName" type="text"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                        @error('categoryName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Description</label>
                        <textarea wire:model="categoryDescription" rows="3"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"></textarea>
                        @error('categoryDescription') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Color</label>
                            <select wire:model="categoryColor"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                                @foreach ($palette as $name => $tone)
                                    <option value="{{ $name }}">{{ ucfirst($name) }}</option>
                                @endforeach
                            </select>
                            @error('categoryColor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Sort Order</label>
                            <input wire:model="categorySortOrder" type="number" min="0"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                            @error('categorySortOrder') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <input wire:model="categoryIsActive" type="checkbox" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                        <span class="text-xs font-semibold text-slate-700">Active Category</span>
                    </label>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <button wire:click="saveCategory" type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-700">
                        <i class="fas fa-floppy-disk"></i>
                        <span>{{ $categoryId ? 'Update Category' : 'Save Category' }}</span>
                    </button>
                    <button wire:click="resetCategoryForm" type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                        <i class="fas fa-rotate-right"></i>
                        <span>Reset</span>
                    </button>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
                <h3 class="mb-4 text-base font-black text-slate-900">Category List</h3>

                <div class="space-y-3">
                    @forelse($categories as $category)
                        @php
                            $color = $palette[$category->color] ?? $palette['slate'];
                        @endphp
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold {{ $color['bg'] }} {{ $color['text'] }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $color['dot'] }}"></span>
                                            {{ ucfirst($category->color) }}
                                        </span>
                                        <h4 class="truncate text-sm font-black text-slate-900">{{ $category->name }}</h4>
                                        @if (!$category->is_active)
                                            <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-700">INACTIVE</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ $category->description ?: 'No description' }}</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button wire:click="editCategory({{ $category->id }})"
                                        class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">Edit</button>
                                    <button wire:click="deleteCategory({{ $category->id }})"
                                        class="rounded-lg bg-rose-100 px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-200">Delete</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-600">
                            No categories yet. Create one to start organizing gallery items.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
