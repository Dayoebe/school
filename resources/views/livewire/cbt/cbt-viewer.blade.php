<div class="min-h-screen bg-themed-primary px-3 sm:px-4 lg:px-6 py-4 sm:py-6 transition-colors duration-300" 
     x-data="{ showDetails: @entangle('viewDetails'), selectedAttempt: @entangle('selectedAttempt') }"
     x-init="
        // Initialize MathJax observer when component mounts
        $nextTick(() => {
            window.cbtViewerMathJax = {
                typeset: function() {
                    if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                        const elements = document.querySelectorAll('.math-content');
                        if (elements.length > 0) {
                            MathJax.typesetClear(Array.from(elements));
                            MathJax.typesetPromise(Array.from(elements)).catch(err => console.error('MathJax error:', err));
                        }
                    }
                }
            };
        });
     ">
    
    <!-- Mobile Header -->
    <div class="mb-4 sm:mb-6">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-themed-primary truncate flex items-center">
                    <i class="fas fa-chart-bar mr-2 text-accent-themed-primary"></i>
                    <span class="hidden sm:inline">My CBT Results</span>
                    <span class="sm:hidden">Results</span>
                </h1>
                <p class="text-xs sm:text-sm text-themed-secondary mt-1">View your assessment performance</p>
            </div>
            <button x-show="showDetails" 
                    wire:click="closeDetails" 
                    class="ml-2 sm:ml-4 bg-themed-secondary hover:bg-themed-tertiary text-themed-primary px-3 sm:px-4 py-2 rounded-lg transition-all duration-200 border border-themed-primary shadow-sm flex items-center text-sm"
                    x-transition:enter="animate__animated animate__fadeIn">
                <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>
                <span class="hidden sm:inline">Back</span>
            </button>
        </div>
    </div>

    <!-- Results List View -->
    <div x-show="!showDetails" 
         x-transition:enter="animate__animated animate__fadeIn"
         x-transition:leave="animate__animated animate__fadeOut">
        
        @if($userAssessments->count() > 0)
            <!-- Mobile: Card View -->
            <div class="grid grid-cols-1 gap-3 sm:gap-4 lg:hidden">
                @foreach($userAssessments as $assessment)
                    @php
                        $attempts = $this->getAttemptsForAssessment($assessment);
                        $bestAttempt = $attempts->sortByDesc('percentage')->first();
                        $latestAttempt = $attempts->first();
                    @endphp
                    <div class="bg-themed-secondary rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 {{ $bestAttempt['passed'] ? 'border-green-500' : 'border-red-500' }} animate__animated animate__fadeInUp"
                         style="animation-delay: {{ $loop->index * 0.1 }}s">
                        
                        <!-- Card Header -->
                        <div class="p-4 border-b border-themed-primary {{ $bestAttempt['passed'] ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                            <div class="flex items-start justify-between">
                                <h3 class="font-semibold text-themed-primary text-base line-clamp-2 flex-1 pr-2">
                                    {{ $assessment->title }}
                                </h3>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $bestAttempt['passed'] ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }} whitespace-nowrap flex-shrink-0 shadow-sm">
                                    <i class="fas {{ $bestAttempt['passed'] ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                    {{ $bestAttempt['passed'] ? 'PASSED' : 'FAILED' }}
                                </span>
                            </div>
                            @if($assessment->description)
                                <p class="text-xs text-themed-secondary mt-2 line-clamp-2">{{ $assessment->description }}</p>
                            @endif
                        </div>
                        
                        <!-- Stats Grid -->
                        <div class="grid grid-cols-3 gap-3 p-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold {{ $bestAttempt['passed'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $bestAttempt['percentage'] }}%
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Best Score</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ $attempts->count() }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Attempts</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                    {{ $bestAttempt['correct_answers'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Correct</div>
                            </div>
                        </div>

                        <!-- Last Attempt Info -->
                        <div class="px-4 pb-3 text-xs text-themed-tertiary flex items-center justify-between">
                            <span class="flex items-center">
                                <i class="far fa-clock mr-1"></i>
                                {{ $latestAttempt['submitted_at']->diffForHumans() }}
                            </span>
                            <span class="flex items-center">
                                <i class="fas fa-calendar mr-1"></i>
                                {{ $latestAttempt['submitted_at']->format('M d, Y') }}
                            </span>
                        </div>
                        
                        <!-- View Details Button -->
                        <div class="p-4 pt-0">
                            <button wire:click="viewAssessmentDetails({{ $assessment->id }})" 
                                    class="w-full bg-accent-themed-primary hover:bg-accent-themed-secondary text-white font-medium py-2.5 rounded-lg transition-all duration-300 transform hover:scale-[1.02] shadow-md flex items-center justify-center text-sm">
                                <i class="fas fa-eye mr-2"></i>View Details
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop: Table View -->
            <div class="hidden lg:block bg-themed-secondary rounded-xl shadow-md overflow-hidden animate__animated animate__fadeIn">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-accent-themed-primary text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Assessment</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Best Score</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Attempts</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Last Attempt</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-themed-primary">
                            @foreach($userAssessments as $assessment)
                                @php
                                    $attempts = $this->getAttemptsForAssessment($assessment);
                                    $bestAttempt = $attempts->sortByDesc('percentage')->first();
                                    $latestAttempt = $attempts->first();
                                @endphp
                                <tr class="hover:bg-themed-tertiary transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-themed-primary">{{ $assessment->title }}</div>
                                        <div class="text-sm text-themed-secondary mt-1">{{ Str::limit($assessment->description, 60) }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="text-2xl font-bold {{ $bestAttempt['passed'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $bestAttempt['percentage'] }}%
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 bg-themed-tertiary text-accent-themed-primary rounded-full font-semibold">
                                            {{ $attempts->count() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-4 py-2 rounded-full text-sm font-bold {{ $bestAttempt['passed'] ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' }} inline-flex items-center">
                                            <i class="fas {{ $bestAttempt['passed'] ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                                            {{ $bestAttempt['passed'] ? 'PASSED' : 'FAILED' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-themed-secondary">
                                        {{ $latestAttempt['submitted_at']->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button wire:click="viewAssessmentDetails({{ $assessment->id }})" 
                                                class="bg-accent-themed-primary hover:bg-accent-themed-secondary text-white px-4 py-2 rounded-lg transition-all transform hover:scale-105 inline-flex items-center">
                                            <i class="fas fa-eye mr-2"></i>View Details
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4 sm:mt-6">
                {{ $userAssessments->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-12 sm:py-16 bg-themed-secondary rounded-xl shadow-md animate__animated animate__fadeIn">
                <div class="w-20 h-20 sm:w-24 sm:h-24 bg-themed-tertiary rounded-full flex items-center justify-center mb-4 sm:mb-6 animate__animated animate__bounceIn">
                    <i class="fas fa-clipboard-list text-4xl sm:text-5xl text-accent-themed-primary"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-semibold text-themed-primary mb-2">No Results Yet</h3>
                <p class="text-sm sm:text-base text-themed-secondary mb-6 text-center px-4">You haven't taken any CBT assessments yet.</p>
                <a href="{{ route('cbt.exams') }}" 
                   class="bg-accent-themed-primary hover:bg-accent-themed-secondary text-white px-6 py-3 rounded-lg transition-all transform hover:scale-105 inline-flex items-center shadow-md text-sm sm:text-base">
                    <i class="fas fa-pencil-alt mr-2"></i>Take CBT Exam
                </a>
            </div>
        @endif
    </div>

    <!-- Detailed View -->
    <div x-show="showDetails" 
         x-transition:enter="animate__animated animate__fadeIn"
         x-transition:leave="animate__animated animate__fadeOut"
         class="space-y-4 sm:space-y-6">
        
        @if($selectedAssessment)
            <!-- Assessment Header Card -->
            <div class="bg-accent-themed-primary rounded-xl shadow-lg p-4 sm:p-6 text-white animate__animated animate__fadeInDown">
                <h2 class="text-xl sm:text-2xl font-bold mb-2">{{ $selectedAssessment->title }}</h2>
                @if($selectedAssessment->description)
                    <p class="opacity-90 text-sm sm:text-base">{{ $selectedAssessment->description }}</p>
                @endif
            </div>

            @php
                $attempts = $this->getAttemptsForAssessment($selectedAssessment);
                $bestAttempt = $attempts->sortByDesc('percentage')->first();
            @endphp

            <!-- Summary Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-themed-secondary rounded-xl shadow-md p-4 text-center transform hover:scale-105 transition-transform animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                    <div class="text-2xl sm:text-3xl font-bold {{ $bestAttempt['passed'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $bestAttempt['percentage'] }}%
                    </div>
                    <div class="text-xs sm:text-sm text-themed-secondary mt-1">Best Score</div>
                </div>
                <div class="bg-themed-secondary rounded-xl shadow-md p-4 text-center transform hover:scale-105 transition-transform animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                    <div class="text-2xl sm:text-3xl font-bold text-accent-themed-primary">
                        {{ $selectedAssessment->questions->count() }}
                    </div>
                    <div class="text-xs sm:text-sm text-themed-secondary mt-1">Questions</div>
                </div>
                <div class="bg-themed-secondary rounded-xl shadow-md p-4 text-center transform hover:scale-105 transition-transform animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                    <div class="text-2xl sm:text-3xl font-bold text-accent-themed-primary">
                        {{ $selectedAssessment->pass_percentage }}%
                    </div>
                    <div class="text-xs sm:text-sm text-themed-secondary mt-1">Pass Mark</div>
                </div>
                <div class="bg-themed-secondary rounded-xl shadow-md p-4 text-center transform hover:scale-105 transition-transform animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                    <div class="text-2xl sm:text-3xl font-bold text-accent-themed-primary">
                        {{ $attempts->count() }}
                    </div>
                    <div class="text-xs sm:text-sm text-themed-secondary mt-1">Attempts</div>
                </div>
            </div>

            <!-- Attempts History -->
            <div class="bg-themed-secondary rounded-xl shadow-md overflow-hidden animate__animated animate__fadeInUp">
                <div class="bg-themed-tertiary px-4 sm:px-6 py-4 border-b border-themed-primary">
                    <h3 class="font-semibold text-themed-primary text-base sm:text-lg flex items-center">
                        <i class="fas fa-history mr-2 text-accent-themed-primary"></i>
                        Attempt History
                    </h3>
                </div>
                
                <!-- Mobile: Card View -->
                <div class="lg:hidden divide-y divide-themed-primary">
                    @foreach($attempts as $attempt)
                        <div class="p-4 hover:bg-themed-tertiary transition-colors">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2.5 py-1 bg-themed-tertiary text-themed-primary rounded-lg font-semibold text-sm">
                                        #{{ $attempt['attempt_number'] }}
                                    </span>
                                    @if($attempt === $bestAttempt)
                                        <span class="px-2.5 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 rounded-lg font-semibold text-xs flex items-center">
                                            <i class="fas fa-star mr-1"></i>Best
                                        </span>
                                    @endif
                                </div>
                                <span class="text-2xl font-bold {{ $attempt['passed'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $attempt['percentage'] }}%
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div class="bg-themed-tertiary rounded-lg p-2">
                                    <div class="text-xs text-themed-tertiary">Points</div>
                                    <div class="font-semibold text-themed-primary">{{ $attempt['total_points'] }} / {{ $attempt['max_points'] }}</div>
                                </div>
                                <div class="bg-themed-tertiary rounded-lg p-2">
                                    <div class="text-xs text-themed-tertiary">Status</div>
                                    <div class="font-semibold {{ $attempt['passed'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $attempt['passed'] ? 'PASSED' : 'FAILED' }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between text-xs text-themed-tertiary mb-3">
                                <span class="flex items-center">
                                    <i class="far fa-calendar mr-1"></i>
                                    {{ $attempt['submitted_at']->format('M d, Y') }}
                                </span>
                                <span class="flex items-center">
                                    <i class="far fa-clock mr-1"></i>
                                    {{ $attempt['submitted_at']->format('H:i') }}
                                </span>
                            </div>
                            
                            <button wire:click="viewAttemptDetails({{ $attempt['attempt_number'] }})" 
                                    class="w-full bg-accent-themed-primary hover:bg-accent-themed-secondary text-white py-2 rounded-lg transition-colors text-sm font-medium flex items-center justify-center">
                                <i class="fas fa-eye mr-2"></i>View Details
                            </button>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop: Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-themed-tertiary">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-themed-secondary uppercase tracking-wider">Attempt</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-themed-secondary uppercase tracking-wider">Score</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-themed-secondary uppercase tracking-wider">Points</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-themed-secondary uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-themed-secondary uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-themed-secondary uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-themed-primary">
                            @foreach($attempts as $attempt)
                                <tr class="hover:bg-themed-tertiary transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <span class="px-3 py-1 bg-themed-tertiary text-themed-primary rounded-lg font-semibold">
                                                #{{ $attempt['attempt_number'] }}
                                            </span>
                                            @if($attempt === $bestAttempt)
                                                <span class="px-2.5 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 rounded-lg font-semibold text-xs flex items-center">
                                                    <i class="fas fa-star mr-1"></i>Best
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-2xl font-bold {{ $attempt['passed'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $attempt['percentage'] }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-themed-primary font-semibold">
                                        {{ $attempt['total_points'] }} / {{ $attempt['max_points'] }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-4 py-2 rounded-full text-sm font-bold {{ $attempt['passed'] ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' }} inline-flex items-center">
                                            <i class="fas {{ $attempt['passed'] ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                                            {{ $attempt['passed'] ? 'PASSED' : 'FAILED' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-themed-secondary">
                                        {{ $attempt['submitted_at']->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button wire:click="viewAttemptDetails({{ $attempt['attempt_number'] }})" 
                                                class="bg-accent-themed-primary hover:bg-accent-themed-secondary text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center">
                                            <i class="fas fa-eye mr-2"></i>View
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Attempt Details Modal -->
    <div x-show="selectedAttempt !== null" 
         x-transition:enter="animate__animated animate__fadeIn"
         x-transition:leave="animate__animated animate__fadeOut"
         @open-modal.window="$nextTick(() => window.cbtViewerMathJax.typeset())"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
         @click.self="$wire.set('selectedAttempt', null)"
         style="display: none;">
        
        @if($selectedAttempt)
        <div class="bg-themed-secondary w-full sm:max-w-6xl sm:rounded-2xl rounded-t-2xl max-h-screen sm:max-h-[90vh] overflow-hidden flex flex-col animate__animated animate__slideInUp sm:animate__zoomIn"
             x-init="$nextTick(() => { setTimeout(() => window.cbtViewerMathJax.typeset(), 100); })">
            <!-- Modal Header -->
            <div class="sticky top-0 z-10 bg-accent-themed-primary px-4 sm:px-6 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-3 flex-1 min-w-0">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm flex-shrink-0">
                        <i class="fas fa-file-alt text-white"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg sm:text-xl font-bold text-white truncate">
                            Attempt #{{ $selectedAttempt['attempt_number'] }}
                        </h3>
                        <p class="text-white/80 text-xs sm:text-sm">Detailed Results</p>
                    </div>
                </div>
                <button @click="$wire.set('selectedAttempt', null)" 
                        class="text-white hover:bg-white/20 rounded-lg p-2 transition-colors flex-shrink-0 ml-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 sm:space-y-6">
                <!-- Summary Stats -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-xl p-4 text-center border border-blue-200 dark:border-blue-800">
                        <div class="text-2xl sm:text-3xl font-bold {{ $selectedAttempt['passed'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $selectedAttempt['percentage'] }}%
                        </div>
                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">Final Score</div>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 rounded-xl p-4 text-center border border-green-200 dark:border-green-800">
                        <div class="text-2xl sm:text-3xl font-bold text-green-600 dark:text-green-400">
                            {{ $selectedAttempt['correct_answers'] }}
                        </div>
                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">Correct</div>
                    </div>
                    <div class="bg-gradient-to-br from-red-50 to-pink-50 dark:from-red-900/30 dark:to-pink-900/30 rounded-xl p-4 text-center border border-red-200 dark:border-red-800">
                        <div class="text-2xl sm:text-3xl font-bold text-red-600 dark:text-red-400">
                            {{ $selectedAttempt['total_questions'] - $selectedAttempt['correct_answers'] }}
                        </div>
                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">Incorrect</div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/30 dark:to-indigo-900/30 rounded-xl p-4 text-center border border-purple-200 dark:border-purple-800">
                        <div class="text-2xl sm:text-3xl font-bold text-purple-600 dark:text-purple-400">
                            {{ $selectedAttempt['total_points'] }}
                        </div>
                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">Points</div>
                    </div>
                </div>

                <!-- Question Review -->
                <div class="space-y-3 sm:space-y-4">
                    <h4 class="font-semibold text-gray-900 dark:text-white text-base sm:text-lg flex items-center">
                        <i class="fas fa-list-check mr-2 text-blue-600 dark:text-blue-400"></i>
                        Question Review
                    </h4>
                    
                    @foreach($selectedAttempt['answers'] as $questionId => $answer)
                        @php $question = $answer->question; @endphp
                        <div class="border-2 rounded-xl overflow-hidden {{ $answer->is_correct ? 'border-green-300 dark:border-green-600' : 'border-red-300 dark:border-red-600' }} animate__animated animate__fadeInUp"
                             style="animation-delay: {{ $loop->index * 0.05 }}s">
                            
                            <!-- Question Header -->
                            <div class="flex items-center justify-between px-3 sm:px-4 py-3 {{ $answer->is_correct ? 'bg-green-50 dark:bg-green-900/30 border-b border-green-200 dark:border-green-700' : 'bg-red-50 dark:bg-red-900/30 border-b border-red-200 dark:border-red-700' }}">
                                <span class="font-semibold text-gray-900 dark:text-white text-sm sm:text-base">Question {{ $loop->iteration }}</span>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $answer->is_correct ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' }} flex items-center">
                                        <i class="fas {{ $answer->is_correct ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                        {{ $answer->is_correct ? 'Correct' : 'Incorrect' }}
                                    </span>
                                    <span class="px-2.5 py-1 text-xs font-bold bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-full">
                                        {{ $answer->points_earned }}/{{ $question->points }} pts
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Question Content -->
                            <div class="p-3 sm:p-4 bg-white dark:bg-gray-800">
                                <!-- Question Text -->
                                <div class="font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4 prose prose-sm sm:prose max-w-none dark:prose-invert math-content text-sm sm:text-base">
                                    {!! $question->question_text !!}
                                </div>
                                
                                <!-- Options -->
                                @if($question->question_type === 'multiple_choice')
                                    <div class="space-y-2">
                                        @foreach($question->options as $index => $option)
                                            @php
                                                $isUserAnswer = $answer->answer == $index;
                                                $isCorrectAnswer = in_array($index, $question->correct_answers);
                                            @endphp
                                            <div class="flex items-start p-2.5 sm:p-3 rounded-lg {{ $isUserAnswer ? ($answer->is_correct ? 'bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-600' : 'bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-600') : ($isCorrectAnswer ? 'bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-600' : 'bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700') }}">
                                                <span class="flex-shrink-0 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-xs sm:text-sm mr-2 sm:mr-3 mt-0.5">
                                                    {{ chr(65 + $index) }}
                                                </span>
                                                <div class="flex-1 prose prose-sm sm:prose max-w-none dark:prose-invert math-content text-sm sm:text-base">
                                                    <span class="{{ $isUserAnswer ? 'font-semibold' : '' }} {{ ($isUserAnswer && !$answer->is_correct) ? 'text-red-700 dark:text-red-400' : ($isCorrectAnswer ? 'text-green-700 dark:text-green-400' : 'text-gray-700 dark:text-gray-300') }}">{!! $option !!}</span>
                                                </div>
                                                <div class="flex-shrink-0 ml-2 space-x-1">
                                                    @if($isUserAnswer)
                                                        <i class="fas fa-arrow-left {{ $answer->is_correct ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} text-xs sm:text-sm" title="Your answer"></i>
                                                    @endif
                                                    @if($isCorrectAnswer)
                                                        <i class="fas fa-check text-green-600 dark:text-green-400 text-xs sm:text-sm" title="Correct answer"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                @elseif($question->question_type === 'true_false')
                                    <div class="space-y-2">
                                        @foreach([0 => 'True', 1 => 'False'] as $value => $label)
                                            @php
                                                $isUserAnswer = $answer->answer == $value;
                                                $isCorrectAnswer = in_array($value, $question->correct_answers);
                                            @endphp
                                            <div class="flex items-center justify-between p-2.5 sm:p-3 rounded-lg {{ $isUserAnswer ? ($answer->is_correct ? 'bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-600' : 'bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-600') : ($isCorrectAnswer ? 'bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-600' : 'bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700') }}">
                                                <div class="flex items-center">
                                                    <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gradient-to-br {{ $value === 0 ? 'from-green-500 to-emerald-600' : 'from-red-500 to-pink-600' }} text-white flex items-center justify-center mr-2 sm:mr-3">
                                                        <i class="fas {{ $value === 0 ? 'fa-check' : 'fa-times' }} text-xs sm:text-sm"></i>
                                                    </span>
                                                    <span class="font-semibold text-sm sm:text-base {{ $isUserAnswer ? ($answer->is_correct ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400') : ($isCorrectAnswer ? 'text-green-700 dark:text-green-400' : 'text-gray-700 dark:text-gray-300') }}">
                                                        {{ $label }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    @if($isUserAnswer)
                                                        <i class="fas fa-arrow-left {{ $answer->is_correct ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} text-xs sm:text-sm"></i>
                                                    @endif
                                                    @if($isCorrectAnswer)
                                                        <i class="fas fa-check text-green-600 dark:text-green-400 text-xs sm:text-sm"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                @else
                                    <div class="bg-gray-50 dark:bg-gray-750 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-700">
                                        <div class="font-semibold text-gray-700 dark:text-gray-300 mb-2 text-xs sm:text-sm">Your Answer:</div>
                                        <div class="text-gray-900 dark:text-white text-sm sm:text-base">{{ $answer->formatted_answer }}</div>
                                    </div>
                                @endif

                                <!-- Explanation -->
                                @if($question->explanation)
                                    <div class="mt-3 sm:mt-4 p-3 sm:p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                                        <div class="flex items-center mb-2">
                                            <i class="fas fa-lightbulb text-blue-600 dark:text-blue-400 mr-2"></i>
                                            <span class="text-xs sm:text-sm font-semibold text-blue-800 dark:text-blue-300">Explanation:</span>
                                        </div>
                                        <div class="text-xs sm:text-sm text-blue-900 dark:text-blue-200 prose prose-sm max-w-none dark:prose-invert math-content">
                                            {!! $question->explanation !!}
                                        </div>
                                    </div>
                                @endif

                                <!-- Feedback -->
                                @if($answer->feedback)
                                    <div class="mt-3 sm:mt-4 p-3 sm:p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-700">
                                        <div class="flex items-center mb-2">
                                            <i class="fas fa-comment-dots text-yellow-600 dark:text-yellow-400 mr-2"></i>
                                            <span class="text-xs sm:text-sm font-semibold text-yellow-800 dark:text-yellow-300">Instructor Feedback:</span>
                                        </div>
                                        <div class="text-xs sm:text-sm text-yellow-900 dark:text-yellow-200">{{ $answer->feedback }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="sticky bottom-0 bg-themed-tertiary px-4 sm:px-6 py-4 border-t border-themed-primary">
                <button @click="$wire.set('selectedAttempt', null)" 
                        class="w-full sm:w-auto bg-themed-secondary hover:bg-accent-themed-primary hover:text-white text-themed-primary border border-themed-secondary px-6 py-3 rounded-lg transition-colors font-medium flex items-center justify-center">
                    <i class="fas fa-times mr-2"></i>Close
                </button>
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
    // Enhanced MathJax configuration for CBT Viewer
    document.addEventListener('livewire:init', function() {
        console.log('CBT Viewer: Livewire initialized');
        
        // Global MathJax rendering function
        window.renderCbtMath = function() {
            if (typeof MathJax === 'undefined' || !MathJax.typesetPromise) {
                console.warn('MathJax not ready, retrying...');
                setTimeout(window.renderCbtMath, 500);
                return;
            }

            const mathElements = document.querySelectorAll('.math-content');
            if (mathElements.length > 0) {
                console.log('Rendering', mathElements.length, 'math elements');
                
                // Clear previous MathJax rendering
                MathJax.typesetClear(Array.from(mathElements));
                
                // Render new content
                MathJax.typesetPromise(Array.from(mathElements))
                    .then(() => {
                        console.log('MathJax rendering completed');
                    })
                    .catch(err => {
                        console.error('MathJax rendering error:', err);
                    });
            }
        };

        // Initial render after page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', window.renderCbtMath);
        } else {
            setTimeout(window.renderCbtMath, 100);
        }

        // Re-render on Livewire updates
        Livewire.hook('morph.updated', function({ el, component }) {
            setTimeout(window.renderCbtMath, 150);
        });

        // Listen for MathJax loaded event from dashboard
        document.addEventListener('mathjax-loaded', function() {
            console.log('MathJax loaded event received');
            window.renderCbtMath();
        });

        // Observe DOM changes for dynamically loaded content (modals)
        const observer = new MutationObserver(function(mutations) {
            let hasMathContent = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) {
                            if (node.classList && node.classList.contains('math-content')) {
                                hasMathContent = true;
                            }
                            if (node.querySelector && node.querySelector('.math-content')) {
                                hasMathContent = true;
                            }
                        }
                    });
                }
            });
            
            if (hasMathContent) {
                console.log('Math content detected in DOM changes');
                setTimeout(window.renderCbtMath, 200);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Force render periodically for the first 10 seconds (handles late-loading modals)
        let renderCount = 0;
        const renderInterval = setInterval(() => {
            renderCount++;
            window.renderCbtMath();
            if (renderCount >= 5) {
                clearInterval(renderInterval);
            }
        }, 2000);
    });
    </script>
    @endpush

    <style>
        /* Line clamp utilities */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Smooth transitions */
        * {
            transition-property: background-color, border-color, color, transform, opacity;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 200ms;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #3b82f6, #6366f1);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #2563eb, #4f46e5);
        }

        .dark ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        /* MathJax responsive styling */
        .math-content mjx-container {
            display: inline-block !important;
            line-height: 1.2;
            max-width: 100%;
            overflow-x: auto;
        }

        .math-content mjx-container[display="true"] {
            display: block !important;
            margin: 1em 0;
            text-align: center;
        }

        @media (max-width: 640px) {
            .math-content mjx-container {
                font-size: 0.9em;
            }
            
            .math-content mjx-container[display="true"] {
                margin: 0.5em 0;
            }
        }

        /* Prose styling */
        .prose img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin: 0.5rem 0;
        }

        .prose pre {
            background-color: #1f2937;
            color: #f3f4f6;
            padding: 0.75rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            font-size: 0.875rem;
        }

        .prose code {
            background-color: #e5e7eb;
            padding: 0.15rem 0.3rem;
            border-radius: 0.25rem;
            font-size: 0.875em;
        }

        .dark .prose code {
            background-color: #374151;
            color: #f3f4f6;
        }

        /* Modal mobile optimization */
        @media (max-width: 640px) {
            .animate__slideInUp {
                animation-name: slideInUpMobile;
            }
        }

        @keyframes slideInUpMobile {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Focus styles */
        button:focus,
        a:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* Safe area for mobile devices */
        @@supports (padding: max(0px)) {
            .safe-area-inset-bottom {
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }
        }

        /* Animation delays */
        .animate__animated {
            animation-duration: 0.5s;
            animation-fill-mode: both;
        }

        @media (prefers-reduced-motion: reduce) {
            .animate__animated {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</div>