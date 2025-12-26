<div class="min-h-screen bg-gray-50">

    {{-- Flash Messages --}}
    @if (session()->has('success') || session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" x-init="setTimeout(() => show = false, 5000)"
            class="fixed top-4 right-4 z-50 max-w-md">
            @if (session()->has('success'))
                <div class="bg-white border-l-4 border-green-500 rounded-lg shadow-lg p-4 flex items-start space-x-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600 text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-gray-900">Success!</p>
                        <p class="text-sm text-gray-600 mt-1">{{ session('success') }}</p>
                    </div>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-white border-l-4 border-red-500 rounded-lg shadow-lg p-4 flex items-start space-x-3">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-600 text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-gray-900">Error!</p>
                        <p class="text-sm text-gray-600 mt-1">{{ session('error') }}</p>
                    </div>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
        </div>
    @endif

    <div class="px-4 sm:px-6 lg:px-8 py-8">

        {{-- LIST VIEW --}}
        @if ($view === 'list')
            {{-- Header --}}
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900">
                            Class Groups
                        </h1>
                        <p class="text-gray-600 mt-2 flex items-center">
                            <i class="fas fa-layer-group mr-2 text-indigo-600"></i>
                            Organize your classes into groups
                        </p>
                    </div>
                    @can('create', App\Models\ClassGroup::class)
                        <button wire:click="showCreate"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 hover:shadow-lg transition-all duration-200 flex items-center space-x-2 font-semibold">
                            <i class="fas fa-plus"></i>
                            <span>New Group</span>
                        </button>
                    @endcan
                </div>
            </div>

            {{-- Search Bar --}}
            <div x-data="{ focused: false }" class="mb-6">
                <div class="relative max-w-xl">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 transition-colors"
                            :class="{ 'text-indigo-600': focused }"></i>
                    </div>
                    <input wire:model.live.debounce.300ms="search" @focus="focused = true" @blur="focused = false"
                        type="text" placeholder="Search class groups..."
                        class="w-full pl-12 pr-4 py-3 bg-white border border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200">
                </div>
            </div>

            {{-- Groups Grid --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse ($classGroups as $group)
                    <div class="bg-white rounded-lg shadow hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200">

                        {{-- Content --}}
                        <div class="p-6">
                            {{-- Icon --}}
                            <div class="w-14 h-14 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                                <i class="fas fa-layer-group text-2xl text-indigo-600"></i>
                            </div>

                            {{-- Title --}}
                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                {{ $group->name }}
                            </h3>

                            {{-- Stats --}}
                            <div class="flex items-center space-x-2 text-sm text-gray-600 mb-4">
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full font-medium">
                                    <i class="fas fa-chalkboard mr-1"></i>
                                    {{ $group->classes_count }} {{ Str::plural('class', $group->classes_count) }}
                                </span>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center space-x-2">
                                @can('view', $group)
                                    <button wire:click="showView({{ $group->id }})"
                                        class="flex-1 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-600 hover:text-white transition-all duration-200 text-sm font-medium">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </button>
                                @endcan
                                @can('update', $group)
                                    <button wire:click="showEdit({{ $group->id }})"
                                        class="px-3 py-2 bg-amber-50 text-amber-700 rounded-lg hover:bg-amber-600 hover:text-white transition-all duration-200">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endcan
                                @can('delete', $group)
                                    <button wire:click="delete({{ $group->id }})"
                                        wire:confirm="Are you sure you want to delete this class group?"
                                        class="px-3 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-600 hover:text-white transition-all duration-200">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="text-center py-16 bg-white rounded-lg border border-gray-200">
                            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-layer-group text-gray-400 text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">No class groups yet</h3>
                            <p class="text-gray-600 mb-6">Create your first class group to get started</p>
                            @can('create', App\Models\ClassGroup::class)
                                <button wire:click="showCreate"
                                    class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all duration-200 font-semibold">
                                    <i class="fas fa-plus mr-2"></i> Create Class Group
                                </button>
                            @endcan
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $classGroups->links() }}
            </div>
        @endif

        {{-- CREATE VIEW --}}
        @if ($view === 'create')
            <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                <div class="bg-indigo-600 p-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        Create Class Group
                    </h2>
                    <p class="text-indigo-100 mt-1 ml-13">Add a new group to organize your classes</p>
                </div>

                <form wire:submit="create" class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Class Group Name <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="name" type="text" placeholder="e.g., Junior Secondary, Senior Secondary"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 hover:shadow-lg transition-all duration-200 font-bold flex items-center justify-center space-x-2">
                            <i class="fas fa-save"></i>
                            <span>Create Group</span>
                        </button>
                        <button type="button" wire:click="showList"
                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all duration-200 font-semibold">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- EDIT VIEW --}}
        @if ($view === 'edit')
            <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                <div class="bg-amber-600 p-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-edit"></i>
                        </div>
                        Edit Class Group
                    </h2>
                    <p class="text-amber-100 mt-1 ml-13">Update class group information</p>
                </div>

                <form wire:submit="update" class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Class Group Name <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="name" type="text"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all duration-200">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-amber-600 text-white rounded-lg hover:bg-amber-700 hover:shadow-lg transition-all duration-200 font-bold flex items-center justify-center space-x-2">
                            <i class="fas fa-save"></i>
                            <span>Update Group</span>
                        </button>
                        <button type="button" wire:click="showList"
                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all duration-200 font-semibold">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- VIEW DETAIL --}}
        @if ($view === 'view')
            <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                <div class="bg-indigo-600 p-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-white">{{ $selectedClassGroup->name }}</h2>
                            <p class="text-indigo-100 mt-1 flex items-center">
                                <i class="fas fa-chalkboard mr-2"></i>
                                {{ $selectedClassGroup->classes->count() }}
                                {{ Str::plural('class', $selectedClassGroup->classes->count()) }}
                            </p>
                        </div>
                        <button wire:click="showList"
                            class="px-5 py-2.5 bg-white bg-opacity-20 text-white rounded-lg hover:bg-opacity-30 transition-all duration-200 font-semibold flex items-center space-x-2">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back</span>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse ($selectedClassGroup->classes as $class)
                            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 hover:border-indigo-300 hover:shadow-md transition-all duration-200">
                                <div class="w-12 h-12 bg-indigo-600 rounded-lg flex items-center justify-center mb-4">
                                    <i class="fas fa-chalkboard text-white text-xl"></i>
                                </div>
                                <h4 class="text-lg font-bold text-gray-900 mb-2">{{ $class->name }}</h4>
                                <p class="text-sm text-gray-600 mb-4">
                                    <i class="fas fa-door-open mr-1"></i>
                                    {{ $class->sections->count() }} {{ Str::plural('section', $class->sections->count()) }}
                                </p>
                                <a href="{{ route('classes.index') }}?view={{ $class->id }}"
                                    class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-semibold">
                                    View Details <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-chalkboard text-gray-400 text-3xl"></i>
                                </div>
                                <p class="text-gray-600 font-medium">No classes in this group yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>