<div class="py-4 px-2 sm:px-4 bg-themed-primary dark:bg-gray-900 min-h-screen transition-colors duration-300">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
        <div class="w-full sm:w-auto">
            <h1 class="text-2xl sm:text-3xl font-bold text-themed-primary flex items-center">
                <i class="fas fa-cog mr-2"></i>CBT Management
            </h1>
            <p class="text-sm sm:text-base text-themed-secondary">Create and manage CBT assessments and questions</p>
        </div>
        <button wire:click="$set('showCreateModal', true)"
            class="w-full sm:w-auto bg-accent-themed-primary hover:bg-accent-themed-secondary text-white px-3 sm:px-4 py-2 rounded-lg flex items-center justify-center transition-colors text-sm sm:text-base">
            <i class="fas fa-plus mr-2"></i>Create CBT Assessment
        </button>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-6 animate-pulse" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('message') }}</span>
                <button type="button" class="ml-auto text-green-700 dark:text-green-300 hover:text-green-900 dark:hover:text-green-100" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded mb-6" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="ml-auto text-red-700 dark:text-red-300 hover:text-red-900 dark:hover:text-red-100" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Assessments List -->
    <div class="bg-themed-secondary rounded-lg shadow-lg border border-themed-primary overflow-hidden">
        <div class="bg-themed-secondary py-3 sm:py-4 px-4 sm:px-6 border-b border-themed-secondary">
            <h6 class="text-base sm:text-lg font-semibold text-accent-themed-primary">CBT Assessments</h6>
        </div>
        <div class="p-3 sm:p-6">
            @if($assessments->count() > 0)
                <!-- Mobile Card View -->
                <div class="block lg:hidden space-y-4">
                    @foreach($assessments as $assessment)
                        <div class="bg-themed-tertiary rounded-lg p-4 border border-themed-secondary">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-semibold text-themed-primary text-base">{{ $assessment->title }}</h3>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-themed-secondary text-accent-themed-primary whitespace-nowrap ml-2">
                                    {{ $assessment->questions->count() }} Q
                                </span>
                            </div>
                            <p class="text-sm text-themed-secondary mb-3">{{ Str::limit($assessment->description, 50) }}</p>
                            
                            <div class="grid grid-cols-2 gap-2 mb-3 text-xs">
                                <div>
                                    <span class="text-themed-tertiary">Course:</span>
                                    <p class="text-themed-primary truncate">{{ $assessment->course?->name ?? $assessment->course?->title ?? 'Standalone' }}</p>
                                </div>
                                <div>
                                    <span class="text-themed-tertiary">Max Attempts:</span>
                                    <p class="text-themed-primary">{{ $assessment->formatted_max_attempts }}</p>
                                </div>
                                @if($assessment->shuffle_questions)
                                <div class="col-span-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                        <i class="fas fa-random mr-1"></i>Questions Shuffled
                                    </span>
                                </div>
                                @endif
                            </div>

                            <div class="flex justify-end space-x-2 pt-3 border-t border-themed-secondary">
                                <button wire:click="viewParticipants({{ $assessment->id }})"
                                    class="text-purple-600 hover:text-purple-800 p-2 rounded-lg hover:bg-themed-secondary transition-colors"
                                    title="View Participants">
                                    <i class="fas fa-users"></i>
                                </button>
                                <button wire:click="manageQuestions({{ $assessment->id }})"
                                    class="text-accent-themed-primary hover:text-accent-themed-secondary p-2 rounded-lg hover:bg-themed-secondary transition-colors"
                                    title="Manage Questions">
                                    <i class="fas fa-question-circle"></i>
                                </button>
                                <button wire:click="editAssessment({{ $assessment->id }})"
                                    class="text-themed-secondary hover:text-themed-primary p-2 rounded-lg hover:bg-themed-secondary transition-colors"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="deleteAssessment({{ $assessment->id }})"
                                    wire:confirm="Are you sure you want to delete this assessment?"
                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                                    title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-themed-tertiary">
                            <tr>
                                <th class="px-4 xl:px-6 py-3 text-left text-xs font-medium text-themed-secondary uppercase tracking-wider">Title</th>
                                <th class="px-4 xl:px-6 py-3 text-left text-xs font-medium text-themed-secondary uppercase tracking-wider">Course</th>
                                <th class="px-4 xl:px-6 py-3 text-left text-xs font-medium text-themed-secondary uppercase tracking-wider">Questions</th>
                                <th class="px-4 xl:px-6 py-3 text-left text-xs font-medium text-themed-secondary uppercase tracking-wider">Duration</th>
                                <th class="px-4 xl:px-6 py-3 text-left text-xs font-medium text-themed-secondary uppercase tracking-wider">Pass %</th>
                                <th class="px-4 xl:px-6 py-3 text-left text-xs font-medium text-themed-secondary uppercase tracking-wider">Settings</th>
                                <th class="px-4 xl:px-6 py-3 text-left text-xs font-medium text-themed-secondary uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-themed-secondary divide-y divide-themed-primary">
                            @foreach($assessments as $assessment)
                                <tr class="hover:bg-themed-tertiary transition-colors">
                                    <td class="px-4 xl:px-6 py-4">
                                        <div class="font-semibold text-themed-primary">{{ $assessment->title }}</div>
                                        <div class="text-sm text-themed-secondary">{{ Str::limit($assessment->description, 50) }}</div>
                                    </td>
                                    <td class="px-4 xl:px-6 py-4">
                                        <div class="text-sm text-themed-primary">{{ $assessment->course?->name ?? $assessment->course?->title ?? 'Standalone' }}</div>
                                    </td>
                                    <td class="px-4 xl:px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-themed-tertiary text-accent-themed-primary">
                                            {{ $assessment->questions->count() }} Questions
                                        </span>
                                    </td>
                                    <td class="px-4 xl:px-6 py-4 whitespace-nowrap text-sm text-themed-primary">{{ $assessment->formatted_duration }}</td>
                                    <td class="px-4 xl:px-6 py-4 whitespace-nowrap text-sm text-themed-primary">{{ $assessment->pass_percentage }}%</td>
                                    <td class="px-4 xl:px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $assessment->max_attempts === null ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' }}">
                                                {{ $assessment->formatted_max_attempts }}
                                            </span>
                                            @if($assessment->shuffle_questions)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300">
                                                <i class="fas fa-random mr-1"></i>Shuffled
                                            </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 xl:px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <button wire:click="viewParticipants({{ $assessment->id }})"
                                                class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 p-2 rounded-lg hover:bg-themed-tertiary transition-colors"
                                                title="View Participants">
                                                <i class="fas fa-users"></i>
                                            </button>
                                            <button wire:click="manageQuestions({{ $assessment->id }})"
                                                class="text-accent-themed-primary hover:text-accent-themed-secondary p-2 rounded-lg hover:bg-themed-tertiary transition-colors"
                                                title="Manage Questions">
                                                <i class="fas fa-question-circle"></i>
                                            </button>
                                            <button wire:click="editAssessment({{ $assessment->id }})"
                                                class="text-themed-secondary hover:text-themed-primary p-2 rounded-lg hover:bg-themed-tertiary transition-colors"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button wire:click="deleteAssessment({{ $assessment->id }})"
                                                wire:confirm="Are you sure you want to delete this assessment?"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $assessments->links() }}
                </div>
            @else
                <div class="text-center py-8 sm:py-12">
                    <i class="fas fa-clipboard-list text-5xl sm:text-6xl text-themed-tertiary mb-4"></i>
                    <h5 class="text-lg sm:text-xl font-semibold text-themed-secondary mb-2">No CBT Assessments Yet</h5>
                    <p class="text-sm sm:text-base text-themed-tertiary">Create your first CBT assessment to get started.</p>
                </div>
            @endif
        </div>
    </div>


    <!-- Create Assessment Modal -->
    <!-- Create Assessment Modal -->
    <div class="@if($showCreateModal) fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50 @else hidden @endif">
        <div class="relative top-4 sm:top-20 mx-2 sm:mx-auto p-4 sm:p-5 border w-auto sm:w-11/12 max-w-4xl shadow-lg rounded-lg bg-themed-secondary border-themed-primary @if($showCreateModal) animate-fade-in-down @endif">
            <div class="border-b border-themed-secondary pb-3 sm:pb-4 mb-3 sm:mb-4">
                <h5 class="text-lg sm:text-xl font-semibold text-themed-primary flex items-center pr-8">
                    <i class="fas fa-plus mr-2"></i>Create CBT Assessment
                </h5>
                <button type="button" class="absolute top-3 sm:top-4 right-3 sm:right-4 text-themed-tertiary hover:text-themed-secondary" wire:click="closeModals">
                    <i class="fas fa-times text-lg sm:text-xl"></i>
                </button>
            </div>
            <div>
                <form wire:submit="createAssessment">
                    <div class="mb-3 sm:mb-4">
                        <label for="course_id" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Course (Optional)</label>
                        <select wire:model="course_id" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary">
                            <option value="">Standalone CBT (No course)</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-themed-tertiary mt-1">Leave empty for standalone CBT exam</p>
                        @error('course_id') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 sm:mb-4">
                        <label for="title" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Assessment Title</label>
                        <input type="text" wire:model="title" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary" required>
                        @error('title') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 sm:mb-4">
                        <label for="description" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Description</label>
                        <textarea wire:model="description" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary" rows="3"></textarea>
                        @error('description') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
                        <div>
                            <label for="pass_percentage" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Pass %</label>
                            <input type="number" wire:model="pass_percentage" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary" min="1" max="100" required>
                            @error('pass_percentage') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="estimated_duration_minutes" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Duration (Min)</label>
                            <input type="number" wire:model="estimated_duration_minutes" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary" min="1" required>
                            @error('estimated_duration_minutes') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="max_score" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Max Score</label>
                            <input type="number" wire:model="max_score" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary" min="1" required>
                            @error('max_score') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="max_attempts" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Max Attempts</label>
                            <input type="number" wire:model="max_attempts" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary" min="1" max="100" placeholder="Unlimited">
                            <p class="text-xs text-themed-tertiary mt-1">Leave empty for unlimited</p>
                            @error('max_attempts') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- NEW: Shuffle Settings -->
                    <div class="bg-themed-tertiary rounded-lg p-4 mb-4 sm:mb-6 border border-themed-secondary">
                        <h6 class="text-sm font-semibold text-themed-primary mb-3 flex items-center">
                            <i class="fas fa-random mr-2 text-accent-themed-primary"></i>
                            Anti-Cheating Settings (UTME Style)
                        </h6>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label for="shuffle_questions" class="text-sm font-medium text-themed-primary">Shuffle Questions</label>
                                    <p class="text-xs text-themed-tertiary mt-1">Randomize question order for each student</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-3">
                                    <input type="checkbox" wire:model="shuffle_questions" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label for="shuffle_options" class="text-sm font-medium text-themed-primary">Shuffle Options</label>
                                    <p class="text-xs text-themed-tertiary mt-1">Randomize A, B, C, D order</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-3">
                                    <input type="checkbox" wire:model="shuffle_options" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 sm:pt-4 border-t border-themed-secondary">
                        <button type="button" class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-themed-tertiary text-themed-primary rounded-lg hover:bg-themed-secondary transition-colors border border-themed-secondary" wire:click="closeModals">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-accent-themed-primary text-white rounded-lg hover:bg-accent-themed-secondary transition-colors flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i>Create Assessment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Assessment Modal -->
    <div
        class="@if($showEditModal) fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50 @else hidden @endif">
        <div
            class="relative top-4 sm:top-20 mx-2 sm:mx-auto p-4 sm:p-5 border w-auto sm:w-11/12 max-w-4xl shadow-lg rounded-lg bg-themed-secondary border-themed-primary @if($showEditModal) animate-fade-in-down @endif">
            <div class="border-b border-themed-secondary pb-3 sm:pb-4 mb-3 sm:mb-4">
                <h5 class="text-lg sm:text-xl font-semibold text-themed-primary flex items-center pr-8">
                    <i class="fas fa-edit mr-2"></i>Edit CBT Assessment
                </h5>
                <button type="button"
                    class="absolute top-3 sm:top-4 right-3 sm:right-4 text-themed-tertiary hover:text-themed-secondary"
                    wire:click="closeModals">
                    <i class="fas fa-times text-lg sm:text-xl"></i>
                </button>
            </div>
            <div>
                <form wire:submit="updateAssessment">
                    <div class="mb-3 sm:mb-4">
                        <label for="course_id"
                            class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Course
                            (Optional)</label>
                        <select wire:model="course_id"
                            class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary">
                            <option value="">Standalone CBT (No course)</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                        @error('course_id') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">
                        {{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 sm:mb-4">
                        <label for="title"
                            class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Assessment
                            Title</label>
                        <input type="text" wire:model="title"
                            class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                            required>
                        @error('title') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">
                        {{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 sm:mb-4">
                        <label for="description"
                            class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Description</label>
                        <textarea wire:model="description"
                            class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                            rows="3"></textarea>
                        @error('description') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">
                        {{ $message }}</div> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
                        <div>
                            <label for="pass_percentage"
                                class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Pass %</label>
                            <input type="number" wire:model="pass_percentage"
                                class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                min="1" max="100" required>
                            @error('pass_percentage') <div
                                class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="estimated_duration_minutes"
                                class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Duration
                                (Min)</label>
                            <input type="number" wire:model="estimated_duration_minutes"
                                class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                min="1" required>
                            @error('estimated_duration_minutes') <div
                                class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="max_score"
                                class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Max Score</label>
                            <input type="number" wire:model="max_score"
                                class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                min="1" required>
                            @error('max_score') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">
                            {{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="max_attempts"
                                class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Max
                                Attempts</label>
                            <input type="number" wire:model="max_attempts"
                                class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                min="1" max="100" placeholder="Unlimited">
                            <p class="text-xs text-themed-tertiary mt-1">Leave empty for unlimited</p>
                            @error('max_attempts') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">
                            {{ $message }}</div> @enderror
                        </div>
                    </div>
                     <!-- NEW: Shuffle Settings -->
                     <div class="bg-themed-tertiary rounded-lg p-4 mb-4 sm:mb-6 border border-themed-secondary">
                        <h6 class="text-sm font-semibold text-themed-primary mb-3 flex items-center">
                            <i class="fas fa-random mr-2 text-accent-themed-primary"></i>
                            Anti-Cheating Settings (UTME Style)
                        </h6>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label for="shuffle_questions" class="text-sm font-medium text-themed-primary">Shuffle Questions</label>
                                    <p class="text-xs text-themed-tertiary mt-1">Randomize question order for each student</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-3">
                                    <input type="checkbox" wire:model="shuffle_questions" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label for="shuffle_options" class="text-sm font-medium text-themed-primary">Shuffle Options</label>
                                    <p class="text-xs text-themed-tertiary mt-1">Randomize A, B, C, D order</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-3">
                                    <input type="checkbox" wire:model="shuffle_options" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 sm:pt-4 border-t border-themed-secondary">
                        <button type="button"
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-themed-tertiary text-themed-primary rounded-lg hover:bg-themed-secondary transition-colors border border-themed-secondary"
                            wire:click="closeModals">Cancel</button>
                        <button type="submit"
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-accent-themed-primary text-white rounded-lg hover:bg-accent-themed-secondary transition-colors flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i>Update Assessment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Questions Management Modal -->
    @if($selectedAssessment && $showQuestionModal)
        <div
            class="fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50">
            <div
                class="relative top-10 mx-auto p-5 border w-11/12 max-w-7xl shadow-lg rounded-lg bg-themed-secondary border-themed-primary animate-fade-in-up">
                <div class="border-b border-themed-secondary pb-4 mb-4">
                    <h5 class="text-xl font-semibold text-themed-primary flex items-center">
                        <i class="fas fa-question-circle mr-2"></i>
                        Manage Questions - {{ $selectedAssessment->title }}
                    </h5>
                    <button type="button" class="absolute top-4 right-4 text-themed-tertiary hover:text-themed-secondary"
                        wire:click="closeModals">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Add Question Form -->
                    <div>
                        <h6 class="text-lg font-semibold text-themed-primary mb-4">Add New Question</h6>
                        <form wire:submit="addQuestion">
                            <div class="mb-4">
                                <label for="question_text" class="block text-sm font-medium text-themed-primary mb-2">
                                    Question Text
                                    <span class="text-xs text-themed-tertiary">(Use $...$ for inline math and $...$ for
                                        display math)</span>
                                </label>
                                <textarea wire:model="question_text"
                                    class="w-full px-3 py-2 border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                    rows="3" placeholder="E.g., Solve the equation: $x^2 + y^2 = 25$" required></textarea>
                                @error('question_text') <div class="text-red-500 dark:text-red-400 text-sm mt-1">
                                {{ $message }}</div> @enderror

                                <!-- Live Preview -->
                                <div class="mt-2 p-3 bg-themed-tertiary rounded-lg">
                                    <label class="text-xs text-themed-secondary mb-1 block">Preview:</label>
                                    <div id="question-preview" class="text-themed-primary min-h-6 math-content">
                                        @if($question_text)
                                            {!! $question_text !!}
                                        @else
                                            <span class="text-themed-tertiary">Preview will appear here</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div class="md:col-span-2">
                                    <label for="question_type"
                                        class="block text-sm font-medium text-themed-primary mb-2">Question Type</label>
                                    <select wire:model.live="question_type"
                                        class="w-full px-3 py-2 border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                        required>
                                        <option value="multiple_choice">Multiple Choice</option>
                                        <option value="true_false">True/False</option>
                                        <option value="short_answer">Short Answer</option>
                                        <option value="essay">Essay</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="points"
                                        class="block text-sm font-medium text-themed-primary mb-2">Points</label>
                                    <input type="number" wire:model="points"
                                        class="w-full px-3 py-2 border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                        step="0.1" min="0.1" required>
                                    @error('points') <div class="text-red-500 dark:text-red-400 text-sm mt-1">{{ $message }}
                                    </div> @enderror
                                </div>
                            </div>

                            @if($question_type === 'multiple_choice')
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-themed-primary mb-2">
                                        Options
                                        <span class="text-xs text-themed-tertiary">(Use $...$ for inline math and $...$ for
                                            display math)</span>
                                    </label>
                                    @foreach($options as $index => $option)
                                        <div class="mb-3">
                                            <div class="flex items-center mb-1">
                                                <span
                                                    class="bg-themed-tertiary text-themed-primary px-3 py-2 rounded-l-lg border border-r-0 border-themed-secondary text-sm font-medium">
                                                    {{ chr(65 + $index) }}
                                                </span>
                                                <input type="text" wire:model="options.{{ $index }}"
                                                    class="flex-1 px-3 py-2 border border-themed-secondary focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                                    placeholder="Option {{ chr(65 + $index) }} (e.g., $x^2 + 5x + 6$)">
                                                <div
                                                    class="bg-themed-tertiary border border-l-0 border-r-0 border-themed-secondary px-3 py-2">
                                                    <input type="checkbox" wire:model="correct_answers" value="{{ $index }}"
                                                        class="form-checkbox h-4 w-4 text-accent-themed-primary"
                                                        title="Correct Answer">
                                                </div>
                                                <button type="button" onclick="toggleOptionPreview({{ $index }})"
                                                    class="bg-themed-tertiary border border-l-0 border-themed-secondary rounded-r-lg px-3 py-2 hover:bg-themed-secondary transition-colors"
                                                    title="Toggle Preview">
                                                    <i class="fas fa-eye text-themed-secondary"></i>
                                                </button>
                                            </div>

                                            <!-- Preview Container -->
                                            <div id="option-preview-container-{{ $index }}"
                                                class="hidden mt-1 ml-12 option-preview-container">
                                                <div class="p-2 bg-themed-tertiary rounded-lg border border-themed-secondary">
                                                    <label class="text-xs text-themed-secondary mb-1 block">Preview:</label>
                                                    <div id="option-preview-{{ $index }}"
                                                        class="text-themed-primary min-h-6 math-content option-preview-content">
                                                        <span class="text-themed-tertiary">Preview will appear here</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @error('options') <div class="text-red-500 dark:text-red-400 text-sm mt-1">{{ $message }}
                                    </div> @enderror
                                    @error('correct_answers') <div class="text-red-500 dark:text-red-400 text-sm mt-1">
                                    {{ $message }}</div> @enderror
                                </div>
                            @elseif($question_type === 'true_false')
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-themed-primary mb-2">Correct Answer</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input type="radio" wire:model="correct_answers" value="0"
                                                class="form-radio h-4 w-4 text-accent-themed-primary" id="true_option">
                                            <label class="ml-2 text-sm text-themed-primary" for="true_option">True</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" wire:model="correct_answers" value="1"
                                                class="form-radio h-4 w-4 text-accent-themed-primary" id="false_option">
                                            <label class="ml-2 text-sm text-themed-primary" for="false_option">False</label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mb-4">
                                <label for="explanation" class="block text-sm font-medium text-themed-primary mb-2">
                                    Explanation
                                    <span class="text-xs text-themed-tertiary">(Use $...$ for inline math and $...$ for
                                        display math)</span>
                                </label>
                                <textarea wire:model="explanation"
                                    class="w-full px-3 py-2 border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                    rows="2" placeholder="E.g., Using Pythagorean theorem: $a^2 + b^2 = c^2$"></textarea>

                                <!-- Live Preview -->
                                <div class="mt-2 p-3 bg-themed-tertiary rounded-lg">
                                    <label class="text-xs text-themed-secondary mb-1 block">Preview:</label>
                                    <div id="explanation-preview" class="text-themed-primary min-h-6 math-content">
                                        @if($explanation)
                                            {!! $explanation !!}
                                        @else
                                            <span class="text-themed-tertiary">Preview will appear here</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center">
                                <i class="fas fa-plus mr-2"></i>Add Question
                            </button>
                        </form>
                    </div>

                    <!-- Questions List with Edit and Reorder -->
                    <div>
                        <h6 class="text-lg font-semibold text-themed-primary mb-4">Questions
                            ({{ $selectedAssessment->questions->count() }})</h6>
                        @if($selectedAssessment->questions->count() > 0)
                            <div id="questions-sortable" class="space-y-3 max-h-[600px] overflow-y-auto pr-2">
                                @foreach($selectedAssessment->questions->sortBy('order') as $question)
                                    <div class="question-item bg-themed-tertiary border border-themed-secondary rounded-lg p-4 hover:shadow-md transition-shadow"
                                        data-id="{{ $question->id }}">
                                        <div class="flex items-start">
                                            <!-- Drag Handle -->
                                            <div
                                                class="drag-handle cursor-move mr-3 text-themed-tertiary hover:text-themed-primary pt-1">
                                                <i class="fas fa-grip-vertical text-lg"></i>
                                            </div>

                                            <!-- Question Content -->
                                            <div class="flex-1">
                                                <h6 class="font-semibold text-themed-primary mb-1">
                                                    Q{{ $loop->iteration }}.
                                                    <span
                                                        class="math-content">{!! Str::limit($question->question_text, 100) !!}</span>
                                                </h6>
                                                @if($question->options && count($question->options) > 0)
                                                    <div class="mt-2 ml-4 space-y-1">
                                                        @foreach($question->options as $index => $option)
                                                            <div class="text-sm text-themed-secondary flex items-start">
                                                                <span class="font-medium mr-2">{{ chr(65 + $index) }}.</span>
                                                                <span class="math-content flex-1">{!! Str::limit($option, 50) !!}</span>
                                                                @if(in_array($index, $question->correct_answers ?? []))
                                                                    <i class="fas fa-check text-green-600 ml-2"></i>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if($question->explanation)
                                                    <div class="mt-2 text-sm text-themed-secondary">
                                                        <strong>Explanation:</strong>
                                                        <span class="math-content">{!! Str::limit($question->explanation, 80) !!}</span>
                                                    </div>
                                                @endif
                                                <div class="flex space-x-2 mt-2">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-themed-secondary text-accent-themed-primary">
                                                        {{ ucfirst(str_replace('_', ' ', $question->question_type)) }}
                                                    </span>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-themed-tertiary text-accent-themed-primary">
                                                        {{ $question->points }} pts
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex space-x-2 ml-3">
                                                <button wire:click="editQuestion({{ $question->id }})"
                                                    class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors"
                                                    title="Edit Question">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button wire:click="deleteQuestion({{ $question->id }})"
                                                    wire:confirm="Are you sure you want to delete this question?"
                                                    class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                                                    title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 border-2 border-dashed border-themed-secondary rounded-lg">
                                <i class="fas fa-question text-4xl text-themed-tertiary mb-3"></i>
                                <p class="text-themed-secondary">No questions added yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Question Modal -->
    @if($showEditQuestionModal && $editingQuestion)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-[60] flex items-center justify-center p-4">
            <div class="bg-themed-secondary rounded-lg max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6 border-b border-themed-secondary sticky top-0 bg-themed-secondary z-10">
                    <h3 class="text-xl font-bold text-themed-primary">Edit Question</h3>
                    <button type="button" class="absolute top-4 right-4 text-themed-tertiary hover:text-themed-secondary"
                        wire:click="closeModals">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-6">
                    <form wire:submit="updateQuestion">
                        <!-- Question Text -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-themed-primary mb-2">
                                Question Text
                                <span class="text-xs text-themed-tertiary">(Use $...$ for inline math, $...$ for
                                    display)</span>
                            </label>
                            <textarea wire:model="question_text" rows="3"
                                class="w-full px-3 py-2 border border-themed-secondary rounded-lg bg-themed-primary text-themed-primary focus:ring-2 focus:ring-accent-themed-primary"
                                required></textarea>

                            <!-- Live Preview -->
                            <div class="mt-2 p-3 bg-themed-tertiary rounded-lg">
                                <label class="text-xs text-themed-secondary mb-1 block">Preview:</label>
                                <div id="edit-question-preview" class="math-content text-themed-primary min-h-6">
                                    @if($question_text)
                                        {!! $question_text !!}
                                    @else
                                        <span class="text-themed-tertiary">Preview will appear here</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Question Type and Points -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-themed-primary mb-2">Question Type</label>
                                <select wire:model.live="question_type"
                                    class="w-full px-3 py-2 border border-themed-secondary rounded-lg bg-themed-primary focus:ring-2 focus:ring-accent-themed-primary">
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="true_false">True/False</option>
                                    <option value="short_answer">Short Answer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-themed-primary mb-2">Points</label>
                                <input type="number" wire:model="points" step="0.1" min="0.1"
                                    class="w-full px-3 py-2 border border-themed-secondary rounded-lg bg-themed-primary focus:ring-2 focus:ring-accent-themed-primary"
                                    required>
                            </div>
                        </div>

                        <!-- Options (Multiple Choice) -->
                        @if($question_type === 'multiple_choice')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-themed-primary mb-2">Options</label>
                                @foreach($options as $index => $option)
                                    <div class="flex items-center mb-2">
                                        <span
                                            class="bg-themed-tertiary px-3 py-2 rounded-l-lg border border-r-0 border-themed-secondary text-sm font-medium text-themed-primary">
                                            {{ chr(65 + $index) }}
                                        </span>
                                        <input type="text" wire:model="options.{{ $index }}"
                                            class="flex-1 px-3 py-2 border border-themed-secondary bg-themed-primary focus:ring-2 focus:ring-accent-themed-primary">
                                        <div
                                            class="bg-themed-tertiary border border-l-0 border-themed-secondary px-3 py-2 rounded-r-lg">
                                            <input type="checkbox" wire:model="correct_answers" value="{{ $index }}"
                                                class="form-checkbox h-4 w-4 text-accent-themed-primary" title="Correct Answer">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($question_type === 'true_false')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-themed-primary mb-2">Correct Answer</label>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="correct_answers" value="0"
                                            class="form-radio h-4 w-4 text-accent-themed-primary" id="edit_true">
                                        <label class="ml-2 text-sm text-themed-primary" for="edit_true">True</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="correct_answers" value="1"
                                            class="form-radio h-4 w-4 text-accent-themed-primary" id="edit_false">
                                        <label class="ml-2 text-sm text-themed-primary" for="edit_false">False</label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Explanation -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-themed-primary mb-2">Explanation (Optional)</label>
                            <textarea wire:model="explanation" rows="2"
                                class="w-full px-3 py-2 border border-themed-secondary rounded-lg bg-themed-primary focus:ring-2 focus:ring-accent-themed-primary"></textarea>

                            <!-- Live Preview -->
                            <div class="mt-2 p-3 bg-themed-tertiary rounded-lg">
                                <label class="text-xs text-themed-secondary mb-1 block">Preview:</label>
                                <div id="edit-explanation-preview" class="math-content text-themed-primary min-h-6">
                                    @if($explanation)
                                        {!! $explanation !!}
                                    @else
                                        <span class="text-themed-tertiary">Preview will appear here</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-3 pt-4 border-t border-themed-secondary">
                            <button type="button" wire:click="closeModals"
                                class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-themed-primary dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-700">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Update Question
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Participants Modal -->
    @if($showParticipantsModal && $selectedAssessment)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-themed-secondary rounded-lg max-w-6xl w-full max-h-screen overflow-hidden"
                x-data="{ expandedUser: null }">
                <div class="p-6 border-b border-themed-secondary flex justify-between items-center">
                    <h3 class="text-xl font-bold text-themed-primary">
                        <i class="fas fa-users mr-2"></i>
                        Participants - {{ $selectedAssessment->title }}
                    </h3>
                    <button type="button" wire:click="closeModals" class="text-themed-tertiary hover:text-themed-secondary">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto max-h-[70vh]">
                    @php
                        $participants = $this->getParticipantsData();
                    @endphp

                    @if($participants->count() > 0)
                        <table class="w-full border-collapse">
                            <thead class="bg-themed-tertiary sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-themed-secondary">Rank
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-themed-secondary">Student
                                        Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-themed-secondary">Email
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-themed-secondary">
                                        Attempts</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-themed-secondary">Best
                                        Score</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-themed-secondary">Status
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-themed-secondary">
                                        Details</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-themed-primary">
                                @foreach($participants as $index => $participant)
                                    <tr class="hover:bg-themed-tertiary transition-colors">
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full 
                                                {{ $index < 3 ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 font-bold' : 'bg-themed-tertiary text-themed-primary' }}">
                                                {{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-themed-primary">{{ $participant['user']->name }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-themed-secondary text-sm">
                                            {{ $participant['user']->email }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-1 bg-themed-tertiary rounded-full text-sm">
                                                {{ $participant['total_attempts'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                class="font-bold text-lg {{ $participant['best_attempt']['passed'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ $participant['best_attempt']['percentage'] }}%
                                            </span>
                                            <div class="text-xs text-themed-secondary">
                                                {{ $participant['best_attempt']['total_points'] }}/{{ $participant['best_attempt']['max_points'] }}
                                                pts
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                class="px-3 py-1 rounded-full text-sm font-medium
                                                {{ $participant['best_attempt']['passed'] ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' }}">
                                                {{ $participant['best_attempt']['passed'] ? 'PASSED' : 'FAILED' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button
                                                @click="expandedUser = expandedUser === {{ $participant['user_id'] }} ? null : {{ $participant['user_id'] }}"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 px-3 py-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                                <i class="fas"
                                                    :class="expandedUser === {{ $participant['user_id'] }} ? 'fa-eye-slash' : 'fa-eye'"></i>
                                                <span
                                                    x-text="expandedUser === {{ $participant['user_id'] }} ? 'Hide' : 'View All'"></span>
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Expandable Attempts Details -->
                                    <tr x-show="expandedUser === {{ $participant['user_id'] }}"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform scale-95"
                                        x-transition:enter-end="opacity-100 transform scale-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 transform scale-100"
                                        x-transition:leave-end="opacity-0 transform scale-95" class="bg-themed-tertiary">
                                        <td colspan="7" class="px-4 py-3">
                                            <div class="pl-12">
                                                <div class="flex justify-between items-center mb-3">
                                                    <h4 class="font-semibold text-themed-primary">All Attempts:</h4>
                                                    <button wire:click="clearAllUserAttempts({{ $participant['user_id'] }})"
                                                        wire:confirm="Are you sure you want to clear ALL attempts for {{ $participant['user']->name }}? This action cannot be undone."
                                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition-colors flex items-center">
                                                        <i class="fas fa-trash-alt mr-1"></i>Clear All Attempts
                                                    </button>
                                                </div>
                                                <div class="space-y-2">
                                                    @foreach($participant['attempts'] as $attempt)
                                                        <div
                                                            class="flex items-center justify-between p-3 bg-themed-secondary rounded-lg">
                                                            <div class="flex items-center space-x-4">
                                                                <span
                                                                    class="px-2 py-1 bg-themed-tertiary rounded font-mono text-sm text-themed-primary">
                                                                    #{{ $attempt['attempt_number'] }}
                                                                </span>
                                                                <span
                                                                    class="font-semibold {{ $attempt['passed'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                                    {{ $attempt['percentage'] }}%
                                                                </span>
                                                                <span class="text-themed-secondary text-sm">
                                                                    {{ $attempt['total_points'] }}/{{ $attempt['max_points'] }} points
                                                                </span>
                                                                <span class="text-themed-secondary text-sm">
                                                                    {{ $attempt['submitted_at']->format('M d, Y - H:i') }}
                                                                </span>
                                                            </div>
                                                            <button
                                                                wire:click="clearAttempt({{ $participant['user_id'] }}, {{ $attempt['attempt_number'] }})"
                                                                wire:confirm="Are you sure you want to clear attempt #{{ $attempt['attempt_number'] }} for {{ $participant['user']->name }}?"
                                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 p-2 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                                                                title="Clear this attempt">
                                                                <i class="fas fa-times-circle"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-users text-6xl text-themed-tertiary mb-4"></i>
                            <h5 class="text-xl text-themed-secondary mb-2">No Participants Yet</h5>
                            <p class="text-themed-tertiary">No students have taken this assessment yet.</p>
                        </div>
                    @endif
                </div>

                <div class="p-6 border-t border-themed-secondary flex justify-between">
                    <div class="text-themed-secondary text-sm">
                        Total Participants: <span
                            class="font-semibold text-themed-primary">{{ $participants->count() }}</span>
                    </div>
                    <button wire:click="closeModals" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Close
                    </button>
                </div>
            </div>
        </div>


        <script>
            function toggleAttempts(userId) {
                const el = document.getElementById('attempts-' + userId);
                el.classList.toggle('hidden');
            }
        </script>
    @endif

    <!-- Sortable.js and MathJax Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
        // Wait for MathJax to be ready
        function waitForMathJax() {
            return new Promise((resolve) => {
                if (typeof MathJax !== 'undefined' && typeof MathJax.typesetPromise !== 'undefined') {
                    resolve();
                } else {
                    document.addEventListener('mathjax-loaded', resolve);
                }
            });
        }

        // Initialize MathJax integration
        async function initMathJax() {
            await waitForMathJax();

            // Process all math content on page load
            MathJax.typesetPromise().catch(err => {
                if (err) console.error('MathJax error:', err);
            });

            // Set up live preview handlers
            setupLivePreviews();
        }

        // Set up live preview for question and explanation inputs
        function setupLivePreviews() {
            let questionTimeout, explanationTimeout;

            // Update preview function
            function updatePreview(inputValue, previewId) {
                const preview = document.getElementById(previewId);
                if (!preview) return;

                const value = String(inputValue || '');

                if (value.trim()) {
                    preview.innerHTML = value;
                    preview.querySelectorAll('mjx-container').forEach(el => el.remove());
                    MathJax.typesetPromise([preview]).catch(err => {
                        if (err) console.error('MathJax preview error:', err);
                    });
                } else {
                    preview.innerHTML = '<span class="text-themed-tertiary">Preview will appear here</span>';
                }
            }

            // Toggle option preview visibility
            window.toggleOptionPreview = function (index) {
                const previewContainer = document.getElementById('option-preview-container-' + index);
                if (previewContainer) {
                    previewContainer.classList.toggle('hidden');
                    if (!previewContainer.classList.contains('hidden')) {
                        const input = document.querySelector(`input[wire\\:model="options.${index}"]`);
                        if (input) {
                            updatePreview(input.value, 'option-preview-' + index);
                        }
                    }
                }
            };

            // Listen for Livewire updates
            Livewire.on('question-text-updated', (value) => {
                clearTimeout(questionTimeout);
                questionTimeout = setTimeout(() => {
                    updatePreview(value, 'question-preview');
                    updatePreview(value, 'edit-question-preview');
                }, 300);
            });

            Livewire.on('explanation-updated', (value) => {
                clearTimeout(explanationTimeout);
                explanationTimeout = setTimeout(() => {
                    updatePreview(value, 'explanation-preview');
                    updatePreview(value, 'edit-explanation-preview');
                }, 300);
            });

            // Handle direct input events as fallback
            document.addEventListener('input', (e) => {
                const target = e.target;

                if (target.hasAttribute('wire:model') &&
                    (target.getAttribute('wire:model') === 'question_text' ||
                        target.getAttribute('wire:model').includes('question_text'))) {
                    clearTimeout(questionTimeout);
                    questionTimeout = setTimeout(() => {
                        updatePreview(target.value, 'question-preview');
                        updatePreview(target.value, 'edit-question-preview');
                    }, 300);
                }

                if (target.hasAttribute('wire:model') &&
                    (target.getAttribute('wire:model') === 'explanation' ||
                        target.getAttribute('wire:model').includes('explanation'))) {
                    clearTimeout(explanationTimeout);
                    explanationTimeout = setTimeout(() => {
                        updatePreview(target.value, 'explanation-preview');
                        updatePreview(target.value, 'edit-explanation-preview');
                    }, 300);
                }

                if (target.hasAttribute('wire:model') &&
                    target.getAttribute('wire:model').includes('options.')) {
                    const match = target.getAttribute('wire:model').match(/options\.(\d+)/);
                    if (match) {
                        const optionIndex = match[1];
                        clearTimeout(window['optionTimeout' + optionIndex]);
                        window['optionTimeout' + optionIndex] = setTimeout(() => {
                            updatePreview(target.value, 'option-preview-' + optionIndex);
                        }, 300);
                    }
                }
            });
        }

        // Initialize Sortable for question reordering
        function initSortable() {
            const questionsList = document.getElementById('questions-sortable');
            if (questionsList && !questionsList.dataset.sortableInitialized) {
                new Sortable(questionsList, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    onEnd: function (evt) {
                        const orderedIds = Array.from(questionsList.children)
                            .filter(item => item.classList.contains('question-item'))
                            .map(item => item.dataset.id);
                        @this.reorderQuestions(orderedIds);
                    }
                });
                questionsList.dataset.sortableInitialized = 'true';
            }
        }

        // Livewire hooks
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', ({ el, component }) => {
                // Reinitialize sortable after DOM updates
                initSortable();

                // Re-render MathJax
                if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                    const mathElements = el.querySelectorAll('.math-content');
                    if (mathElements.length > 0) {
                        MathJax.typesetPromise(Array.from(mathElements)).catch(err => {
                            if (err) console.error('MathJax rendering error:', err);
                        });
                    }
                }
            });
        });

        // Initialize on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                initMathJax();
                initSortable();
            });
        } else {
            initMathJax();
            initSortable();
        }
    </script>

    <style>
        /* Animation keyframes */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-down {
            animation: fadeInDown 0.3s ease-out;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.3s ease-out;
        }

        /* MathJax specific styling */
        .math-content mjx-container {
            display: inline-block !important;
            margin: 0.2em 0;
        }

        .math-content mjx-container[display="true"] {
            display: block !important;
            margin: 1em 0;
        }

        /* Option preview containers */
        .option-preview-container {
            transition: all 0.3s ease-in-out;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
        }

        .option-preview-container:not(.hidden) {
            max-height: 200px;
            opacity: 1;
        }

        /* Sortable styles */
        .sortable-ghost {
            opacity: 0.4;
            background-color: rgb(var(--accent-primary) / 0.1);
        }

        .sortable-chosen {
            opacity: 0.8;
        }

        .sortable-drag {
            opacity: 0;
        }

        .drag-handle {
            cursor: move;
            transition: color 0.2s ease;
        }

        .drag-handle:hover {
            cursor: grab;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        /* Form elements */
        .form-checkbox:checked {
            background-color: rgb(var(--accent-primary));
            border-color: rgb(var(--accent-primary));
        }

        .form-radio:checked {
            background-color: rgb(var(--accent-primary));
            border-color: rgb(var(--accent-primary));
        }

        .dark .form-checkbox {
            background-color: #374151;
            border-color: #6b7280;
        }

        .dark .form-radio {
            background-color: #374151;
            border-color: #6b7280;
        }

        .dark .form-checkbox:checked {
            background-color: rgb(var(--accent-primary));
            border-color: rgb(var(--accent-primary));
        }

        .dark .form-radio:checked {
            background-color: rgb(var(--accent-primary));
            border-color: rgb(var(--accent-primary));
        }

        /* Prose styles for rich text content */
        .prose {
            max-width: none;
        }

        .prose img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin: 1rem 0;
        }

        .prose pre {
            background-color: #1f2937;
            color: #f3f4f6;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
        }

        .prose code {
            background-color: #e5e7eb;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-size: 0.875em;
        }

        .dark .prose code {
            background-color: #374151;
            color: #f3f4f6;
        }

        .prose blockquote {
            border-left: 4px solid rgb(var(--accent-primary));
            padding-left: 1rem;
            font-style: italic;
            color: #6b7280;
        }

        .dark .prose blockquote {
            color: #9ca3af;
        }

        .prose ul,
        .prose ol {
            padding-left: 1.5rem;
        }

        .prose li {
            margin: 0.5rem 0;
        }

        .prose a {
            color: rgb(var(--accent-primary));
            text-decoration: underline;
        }

        .prose table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        .prose th,
        .prose td {
            border: 1px solid #e5e7eb;
            padding: 0.5rem;
        }

        .dark .prose th,
        .dark .prose td {
            border-color: #374151;
        }

        .prose th {
            background-color: #f3f4f6;
            font-weight: 600;
        }

        .dark .prose th {
            background-color: #1f2937;
        }

        /* Responsive scrollbar */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }

        .overflow-y-auto::-webkit-scrollbar-track {
            background: rgb(var(--bg-tertiary));
            border-radius: 3px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: rgb(var(--accent-primary));
            border-radius: 3px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: rgb(var(--accent-secondary));
        }

        /* Mobile responsive text wrapping */
        @media (max-width: 640px) {
            .math-content {
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
        }

        /* Smooth transitions */
        * {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }

        /* Disable transitions for specific elements */
        .no-transition,
        .no-transition * {
            transition: none !important;
        }
    </style>
</div>
