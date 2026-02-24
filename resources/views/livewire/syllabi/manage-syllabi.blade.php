<div class="space-y-6">
    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($mode === 'list')
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Syllabi</h2>
                    <p class="text-sm text-gray-600">Manage syllabi by class and subject.</p>
                </div>
                @can('create syllabus')
                    <a href="{{ route('syllabi.create') }}"
                        wire:navigate
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i>Create Syllabus
                    </a>
                @endcan
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                @if (!auth()->user()->hasRole('student'))
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Class</label>
                        <select wire:model.live="class" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Classes</option>
                            @foreach ($classes as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Search</label>
                    <input type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search name, description, subject..."
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Per Page</label>
                    <select wire:model.live="perPage" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ([10, 20, 50] as $size)
                            <option value="{{ $size }}">{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($syllabi as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $item->subject?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $item->created_at?->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <div class="inline-flex items-center gap-3">
                                        <a href="{{ route('syllabi.show', $item->id) }}" wire:navigate class="text-blue-600 hover:text-blue-800" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ asset('storage/'.$item->file) }}" class="text-emerald-600 hover:text-emerald-800" title="Download" download>
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @can('delete syllabus')
                                            <button
                                                wire:click="deleteSyllabus({{ $item->id }})"
                                                wire:confirm="Delete this syllabus?"
                                                class="text-red-600 hover:text-red-800"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">No syllabi found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($syllabi instanceof \Illuminate\Contracts\Pagination\Paginator && $syllabi->hasPages())
                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $syllabi->links() }}
                </div>
            @endif
        </div>
    @elseif ($mode === 'create')
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">Create Syllabus</h2>
                <a href="{{ route('syllabi.index') }}"
                    wire:navigate
                    class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <form wire:submit.prevent="createSyllabus" class="space-y-5">
                @if ($errors->has('semester'))
                    <div class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first('semester') }}
                    </div>
                @endif

                @if (!auth()->user()->hasRole('student'))
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Class <span class="text-red-500">*</span></label>
                        <select wire:model.live="class" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('class') border-red-500 @enderror">
                            <option value="">Select Class</option>
                            @foreach ($classes as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        @error('class')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Subject <span class="text-red-500">*</span></label>
                    <select wire:model="subject" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('subject') border-red-500 @enderror" @if($subjects->isEmpty()) disabled @endif>
                        <option value="">Select Subject</option>
                        @foreach ($subjects as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @error('subject')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.defer="name" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror" placeholder="e.g. Physics First Term Syllabus">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Description</label>
                    <textarea wire:model.defer="description" rows="4" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror" placeholder="Optional description"></textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">PDF File <span class="text-red-500">*</span></label>
                    <input type="file" wire:model="file" accept="application/pdf" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 @error('file') border-red-500 @enderror">
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('syllabi.index') }}"
                        wire:navigate
                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Cancel</a>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        <i class="fas fa-save mr-2"></i>Create Syllabus
                    </button>
                </div>
            </form>
        </div>
    @elseif ($mode === 'show' && $selectedSyllabus)
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $selectedSyllabus->name }}</h2>
                    <p class="mt-1 text-sm text-gray-600">Subject: {{ $selectedSyllabus->subject?->name ?? 'N/A' }}</p>
                    <p class="mt-1 text-sm text-gray-500">Created: {{ $selectedSyllabus->created_at?->format('M d, Y h:i A') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('syllabi.index') }}"
                        wire:navigate
                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </a>
                    <a href="{{ asset('storage/'.$selectedSyllabus->file) }}"
                        download
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        <i class="fas fa-download mr-2"></i>Download
                    </a>
                    @can('delete syllabus')
                        <button
                            wire:click="deleteSyllabus({{ $selectedSyllabus->id }})"
                            wire:confirm="Delete this syllabus?"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    @endcan
                </div>
            </div>

            <div class="mt-6 rounded-lg bg-gray-50 p-4 text-gray-700">
                {{ $selectedSyllabus->description ?: 'No description provided.' }}
            </div>
        </div>
    @endif
</div>
