<div x-data="modernCbtExam()"
     class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800"
     :class="{ 'exam-mode': examStarted && !examCompleted }">

    {{-- Pre-Exam Welcome Screen --}}
    @if(!$examStarted)
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-4xl w-full bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden">
            {{-- Header Banner --}}
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-8 text-white">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-graduation-cap text-4xl"></i>
                    </div>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-center mb-2">{{ $assessment->title }}</h1>
                @if($assessment->description)
                <p class="text-center text-blue-100 text-lg">{{ $assessment->description }}</p>
                @endif
            </div>

            {{-- Exam Info Cards --}}
            <div class="p-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 p-6 rounded-2xl text-center">
                        <i class="fas fa-question-circle text-3xl text-blue-600 dark:text-blue-400 mb-3"></i>
                        <div class="text-3xl font-bold text-gray-800 dark:text-white">{{ count($questions) }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Questions</div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 p-6 rounded-2xl text-center">
                        <i class="fas fa-clock text-3xl text-purple-600 dark:text-purple-400 mb-3"></i>
                        <div class="text-3xl font-bold text-gray-800 dark:text-white">{{ $assessment->formatted_duration }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Duration</div>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 p-6 rounded-2xl text-center">
                        <i class="fas fa-trophy text-3xl text-green-600 dark:text-green-400 mb-3"></i>
                        <div class="text-3xl font-bold text-gray-800 dark:text-white">{{ $assessment->pass_percentage }}%</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Pass Mark</div>
                    </div>

                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/30 dark:to-orange-800/30 p-6 rounded-2xl text-center">
                        <i class="fas fa-hashtag text-3xl text-orange-600 dark:text-orange-400 mb-3"></i>
                        <div class="text-3xl font-bold text-gray-800 dark:text-white">#{{ $attemptNumber }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Attempt</div>
                    </div>
                </div>

                {{-- Important Instructions --}}
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-6 rounded-lg mb-8">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 text-2xl mr-4 mt-1"></i>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Important Instructions</h3>
                            <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-yellow-600 mr-2 mt-1"></i>
                                    <span>Once started, the timer cannot be paused</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-yellow-600 mr-2 mt-1"></i>
                                    <span>Your answers are automatically saved</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-yellow-600 mr-2 mt-1"></i>
                                    <span>You can navigate between questions freely</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Start Button --}}
                <button wire:click="startExam" 
                        wire:loading.attr="disabled"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xl font-bold py-5 rounded-2xl transition-all shadow-lg disabled:opacity-50">
                    <div wire:loading.remove class="flex items-center justify-center">
                        <i class="fas fa-rocket mr-3"></i>
                        <span>Start Exam Now</span>
                    </div>
                    <div wire:loading class="flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-3"></i>
                        <span>Preparing Exam...</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    @elseif($examCompleted)
    {{-- Results Screen --}}
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-4xl w-full bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden">
            @if($pendingPublish)
                <div class="p-8 text-center bg-gradient-to-r from-blue-600 to-indigo-600">
                    <div class="w-32 h-32 mx-auto bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm mb-6">
                        <i class="fas fa-hourglass-half text-7xl text-white"></i>
                    </div>
                    <h1 class="text-4xl font-bold text-white mb-2">Exam Submitted</h1>
                    <p class="text-xl text-white/90">{{ $assessment->title }}</p>
                </div>
            @else
                {{-- Results Header --}}
                <div class="p-8 text-center {{ $results['passed'] ? 'bg-gradient-to-r from-green-600 to-emerald-600' : 'bg-gradient-to-r from-red-600 to-pink-600' }}">
                    <div class="w-32 h-32 mx-auto bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm mb-6">
                        <i class="fas {{ $results['passed'] ? 'fa-check-circle' : 'fa-times-circle' }} text-7xl text-white"></i>
                    </div>
                    <h1 class="text-4xl font-bold text-white mb-2">
                        {{ $results['passed'] ? 'Congratulations!' : 'Keep Trying!' }}
                    </h1>
                    <p class="text-xl text-white/90">{{ $assessment->title }}</p>
                </div>
            @endif

            <div class="p-8">
                @if($pendingPublish)
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-blue-900">
                        <p class="font-semibold">{{ $resultNotice ?: 'Your response has been captured successfully.' }}</p>
                        <p class="mt-2 text-sm">
                            Your school needs to publish this CBT result before it appears in your result dashboard.
                        </p>
                    </div>
                @else
                    {{-- Score Display --}}
                    <div class="text-center mb-8">
                        <div class="inline-block relative">
                            <svg class="transform -rotate-90 w-48 h-48">
                                <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="12" fill="transparent" 
                                        class="text-gray-200 dark:text-gray-700" />
                                <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="12" fill="transparent"
                                        class="{{ $results['passed'] ? 'text-green-500' : 'text-red-500' }}"
                                        :stroke-dasharray="`${({{ $results['percentage'] }} / 100) * 552.92} 552.92`"
                                        stroke-linecap="round" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div>
                                    <div class="text-5xl font-bold {{ $results['passed'] ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $results['percentage'] }}%
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Your Score</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-2xl text-center">
                            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $results['total_points'] }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Points Earned</div>
                            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">out of {{ $results['max_points'] }}</div>
                        </div>

                        <div class="bg-purple-50 dark:bg-purple-900/20 p-6 rounded-2xl text-center">
                            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $results['correct_answers'] }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Correct Answers</div>
                            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">out of {{ $results['total_questions'] }}</div>
                        </div>

                        <div class="bg-orange-50 dark:bg-orange-900/20 p-6 rounded-2xl text-center">
                            <div class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ $this->formatTimeSpent($results['time_spent']) }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Time Taken</div>
                            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">Attempt #{{ $results['attempt_number'] }}</div>
                        </div>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row gap-4">
                    @if(!$pendingPublish && !$results['passed'])
                    <button wire:click="retakeExam" 
                            class="flex-1 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-semibold py-4 rounded-2xl transition-all">
                        <i class="fas fa-redo mr-2"></i>Retake Exam
                    </button>
                    @endif
                    <a href="{{ route('cbt.exams') }}" 
                       class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-4 rounded-2xl text-center transition-all">
                        <i class="fas fa-home mr-2"></i>Back to Exams
                    </a>
                </div>
            </div>
        </div>
    </div>
    @else
    
	    {{-- Main Exam Interface --}}
	    <div class="min-h-screen flex flex-col bg-white dark:bg-gray-900">
            @if($resumeBanner)
                <div class="mx-4 mt-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-900">
                    {{ $resumeBanner }}
                </div>
            @endif

	        {{-- Top Navigation Bar --}}
	        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg z-20 flex-shrink-0">
            <div class="flex items-center justify-between px-4 md:px-6 py-4">
                {{-- Left: Question Counter with Mobile Dropdown --}}
                <div class="flex items-center space-x-4">
                    {{-- Mobile Question Dropdown --}}
                    <div class="lg:hidden relative" x-data="{ open: false }">
                        <button @click.stop="open = !open" 
                                class="flex items-center space-x-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl hover:bg-white/30 transition-colors">
                            <i class="fas fa-list text-xl"></i>
                            <span class="font-bold">{{ $currentQuestionIndex + 1 }}/{{ count($questions) }}</span>
                            <i class="fas fa-chevron-down text-sm transition-transform" :class="{ 'rotate-180': open }"></i>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             @click.away="open = false"
                             class="absolute left-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl z-50 max-h-96 overflow-hidden"
                             style="display: none;">
                            
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600">
                                <h3 class="font-semibold text-gray-800 dark:text-white mb-2">Jump to Question</h3>
                                <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                                    <span>Answered: {{ $this->getAnsweredQuestionsCount() }}</span>
                                    <span>Remaining: {{ count($questions) - $this->getAnsweredQuestionsCount() }}</span>
                                </div>
                            </div>

                            <div class="p-4 overflow-y-auto max-h-80">
                                {{-- Legend --}}
                                <div class="grid grid-cols-2 gap-2 mb-4 text-xs">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-5 h-5 bg-green-500 rounded"></div>
                                        <span class="text-gray-600 dark:text-gray-400">Answered</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded"></div>
                                        <span class="text-gray-600 dark:text-gray-400">Unanswered</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-5 h-5 bg-blue-600 rounded"></div>
                                        <span class="text-gray-600 dark:text-gray-400">Current</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-5 h-5 bg-yellow-500 rounded flex items-center justify-center">
                                            <i class="fas fa-flag text-xs text-white"></i>
                                        </div>
                                        <span class="text-gray-600 dark:text-gray-400">Flagged</span>
                                    </div>
                                </div>

                                {{-- Question Grid --}}
                                <div class="grid grid-cols-6 gap-2">
                                    @foreach($questions as $index => $question)
                                    <button wire:click="goToQuestion({{ $index }})"
                                            @click="open = false"
                                            class="relative aspect-square flex items-center justify-center rounded-lg font-bold text-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'bg-blue-600 text-white scale-110': {{ $currentQuestionIndex }} === {{ $index }},
                                                'bg-green-500 text-white hover:bg-green-600': {{ $currentQuestionIndex }} !== {{ $index }} && {{ isset($answers[$question['id']]) && $answers[$question['id']] !== null ? 'true' : 'false' }},
                                                'border-2 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-blue-500': {{ $currentQuestionIndex }} !== {{ $index }} && {{ !isset($answers[$question['id']]) || $answers[$question['id']] === null ? 'true' : 'false' }}
                                            }">
                                        {{ $index + 1 }}
                                        @if($this->isQuestionFlagged($index))
                                        <i class="fas fa-flag absolute -top-1 -right-1 text-yellow-500 text-xs"></i>
                                        @endif
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Desktop Question Counter --}}
                    <button @click="toggleSidebar()" class="hidden lg:flex items-center space-x-3">
                        <div class="flex items-center justify-center w-12 h-12 bg-white/20 rounded-xl backdrop-blur-sm">
                            <i class="fas fa-file-alt text-2xl"></i>
                        </div>
                        <div>
                            <div class="text-sm font-medium opacity-90">Question Progress</div>
                            <div class="text-xl font-bold">{{ $currentQuestionIndex + 1 }} / {{ count($questions) }}</div>
                        </div>
                    </button>
                </div>

                {{-- Center: Progress Bar (Hidden on mobile) --}}
                <div class="hidden md:flex flex-1 max-w-md mx-8">
                    <div class="w-full bg-white/20 rounded-full h-3 backdrop-blur-sm overflow-hidden">
                        <div class="bg-white h-full rounded-full transition-all duration-500"
                             :style="`width: ${({{ $currentQuestionIndex + 1 }} / {{ count($questions) }}) * 100}%`"></div>
                    </div>
                </div>

                {{-- Right: Timer & Actions --}}
                <div class="flex items-center space-x-2 sm:space-x-4">
                    {{-- Enhanced Timer Component --}}
                    @livewire('cbt.exam.enhanced-timer', [
                        'timeRemaining' => $timeRemaining,
                        'estimatedDuration' => $assessment->estimated_duration_minutes,
                        'questionCount' => count($questions),
                        'currentQuestionIndex' => $currentQuestionIndex
                    ])

                    <button 
                        wire:click="toggleFlag({{ $currentQuestionIndex }})"
                        class="p-2 sm:p-3 rounded-xl transition-all"
                        :class="'{{ $this->isQuestionFlagged($currentQuestionIndex) }}' ? 
                            'bg-yellow-500 text-yellow-900' : 
                            'bg-white/20 hover:bg-white/30'">
                        <i class="fas fa-flag"></i>
                    </button>
                </div>
            </div>

            {{-- Mobile Progress Bar --}}
            <div class="md:hidden px-4 pb-3">
                <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                    <div class="bg-white h-full rounded-full transition-all duration-500"
                         :style="`width: ${({{ $currentQuestionIndex + 1 }} / {{ count($questions) }}) * 100}%`"></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex overflow-hidden min-h-0">
            {{-- Question Navigation Sidebar --}}
            <div class="bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 shadow-xl lg:shadow-none z-10"
                 :class="sidebarOpen ? 'fixed inset-y-0 left-0 w-80' : 'hidden lg:block lg:w-72'">
                
                {{-- Sidebar Header --}}
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-800 dark:text-white">Questions</h3>
                        <button @click="toggleSidebar()" class="lg:hidden p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                        <span>Answered: {{ $this->getAnsweredQuestionsCount() }}</span>
                        <span>Remaining: {{ count($questions) - $this->getAnsweredQuestionsCount() }}</span>
                    </div>
                </div>

                {{-- Question Grid --}}
                <div class="p-4 overflow-y-auto h-[calc(100vh-180px)]">
                    {{-- Legend --}}
                    <div class="grid grid-cols-2 gap-2 mb-4 text-xs">
                        <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 bg-green-500 rounded-lg"></div>
                            <span class="text-gray-600 dark:text-gray-400">Answered</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 border-2 border-gray-300 dark:border-gray-600 rounded-lg"></div>
                            <span class="text-gray-600 dark:text-gray-400">Unanswered</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 bg-blue-600 rounded-lg"></div>
                            <span class="text-gray-600 dark:text-gray-400">Current</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 bg-yellow-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-flag text-xs text-white"></i>
                            </div>
                            <span class="text-gray-600 dark:text-gray-400">Flagged</span>
                        </div>
                    </div>

                    {{-- Question Numbers --}}
                    <div class="grid grid-cols-5 gap-3">
                        @foreach($questions as $index => $question)
                        <button wire:click="goToQuestion({{ $index }})"
                                @click="sidebarOpen = false"
                                class="relative aspect-square flex items-center justify-center rounded-xl font-bold text-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{
                                    'bg-blue-600 text-white shadow-lg scale-110': {{ $currentQuestionIndex }} === {{ $index }},
                                    'bg-green-500 text-white hover:bg-green-600': {{ $currentQuestionIndex }} !== {{ $index }} && {{ isset($answers[$question['id']]) && $answers[$question['id']] !== null ? 'true' : 'false' }},
                                    'border-2 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-blue-500': {{ $currentQuestionIndex }} !== {{ $index }} && {{ !isset($answers[$question['id']]) || $answers[$question['id']] === null ? 'true' : 'false' }}
                                }">
                            {{ $index + 1 }}
                            @if($this->isQuestionFlagged($index))
                            <i class="fas fa-flag absolute -top-1 -right-1 text-yellow-500 text-xs"></i>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Sidebar Overlay (Mobile) --}}
            <div x-show="sidebarOpen" 
                 @click="sidebarOpen = false"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black bg-opacity-50 lg:hidden z-0"
                 style="display: none;"></div>

            {{-- Main Content Area --}}
            <div class="flex-1 flex flex-col overflow-hidden min-h-0">
                {{-- Question Content --}}
                <div class="flex-1 overflow-y-auto p-4 md:p-8 pb-40 sm:pb-44 md:pb-8">
                    @if($this->getCurrentQuestion())
                    @php $question = $this->getCurrentQuestion(); @endphp
                    <div class="max-w-4xl mx-auto">
                        {{-- Question Header --}}
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 md:p-8 mb-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                        {{ $currentQuestionIndex + 1 }}
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Question Type</div>
                                        <div class="font-semibold text-gray-800 dark:text-white">
                                            {{ ucfirst(str_replace('_', ' ', $question['question_type'] ?? 'multiple_choice')) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Points</div>
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $question['points'] ?? 1 }}</div>
                                </div>
                            </div>

                            {{-- Question Text --}}
                            <div class="prose prose-lg max-w-none dark:prose-invert math-content text-gray-800 dark:text-gray-200"
                                 wire:key="question-{{ $question['id'] }}"
                                 x-init="renderMathInElement($el)">
                                {!! $question['question_text'] ?? '' !!}
                            </div>
                        </div>

                        {{-- Answer Options --}}
                        <div class="space-y-4 mb-6">
                            @if(($question['question_type'] ?? '') === 'multiple_choice')
                                @if(is_array($question['options']) && count($question['options']) > 0)
                                    @foreach($question['options'] as $optionIndex => $option)
                                        @if(trim(strip_tags($option)))
                                        <label class="block cursor-pointer group">
                                            <input type="radio" 
                                                   wire:click="saveAnswer({{ $question['id'] }}, {{ $optionIndex }})"
                                                   name="question_{{ $question['id'] }}" 
                                                   value="{{ $optionIndex }}"
                                                   class="hidden peer"
                                                   {{ isset($answers[$question['id']]) && $answers[$question['id']] == $optionIndex ? 'checked' : '' }}>
                                            
                                            <div class="bg-white dark:bg-gray-800 border-2 rounded-2xl p-5 transition-all duration-200 border-gray-200 dark:border-gray-700 group-hover:border-blue-300"
                                                 :class="{ 
                                                     'border-blue-500 bg-blue-50 dark:bg-blue-900/20 shadow-lg': {{ isset($answers[$question['id']]) && $answers[$question['id']] == $optionIndex ? 'true' : 'false' }}
                                                 }">
                                                <div class="flex items-start">
                                                    <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold mr-4 shadow-lg">
                                                        {{ chr(65 + $optionIndex) }}
                                                    </div>
                                                    <div class="flex-1 prose dark:prose-invert math-content text-gray-700 dark:text-gray-200"
                                                         wire:key="option-{{ $question['id'] }}-{{ $optionIndex }}"
                                                         x-init="renderMathInElement($el)">
                                                        {!! $option !!}
                                                    </div>
                                                    <div class="flex-shrink-0 ml-4">
                                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                                                             :class="{
                                                                 'border-blue-500 bg-blue-500': {{ isset($answers[$question['id']]) && $answers[$question['id']] == $optionIndex ? 'true' : 'false' }},
                                                                 'border-gray-300 dark:border-gray-600': {{ !isset($answers[$question['id']]) || $answers[$question['id']] != $optionIndex ? 'true' : 'false' }}
                                                             }">
                                                            <i class="fas fa-check text-white text-xs"
                                                               :class="{ 'opacity-100': {{ isset($answers[$question['id']]) && $answers[$question['id']] == $optionIndex ? 'true' : 'false' }}, 'opacity-0': {{ !isset($answers[$question['id']]) || $answers[$question['id']] != $optionIndex ? 'true' : 'false' }} }"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                        @endif
                                    @endforeach
                                @endif

                            @elseif(($question['question_type'] ?? '') === 'true_false')
                                <label class="block cursor-pointer group">
                                    <input type="radio" 
                                           wire:click="saveAnswer({{ $question['id'] }}, 0)"
                                           name="question_{{ $question['id'] }}" 
                                           value="0"
                                           class="hidden peer"
                                           {{ isset($answers[$question['id']]) && $answers[$question['id']] == 0 ? 'checked' : '' }}>
                                    
                                    <div class="bg-white dark:bg-gray-800 border-2 rounded-2xl p-6 transition-all duration-200 border-gray-200 dark:border-gray-700 group-hover:border-green-300"
                                         :class="{
                                             'border-green-500 bg-green-50 dark:bg-green-900/20 shadow-lg': {{ isset($answers[$question['id']]) && $answers[$question['id']] == 0 ? 'true' : 'false' }}
                                         }">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center font-bold shadow-lg">
                                                    <i class="fas fa-check text-2xl"></i>
                                                </div>
                                                <span class="text-xl font-semibold text-gray-800 dark:text-white">True</span>
                                            </div>
                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                                                 :class="{
                                                     'border-green-500 bg-green-500': {{ isset($answers[$question['id']]) && $answers[$question['id']] == 0 ? 'true' : 'false' }},
                                                     'border-gray-300 dark:border-gray-600': {{ !isset($answers[$question['id']]) || $answers[$question['id']] != 0 ? 'true' : 'false' }}
                                                 }">
                                                <i class="fas fa-check text-white text-xs"
                                                   :class="{ 'opacity-100': {{ isset($answers[$question['id']]) && $answers[$question['id']] == 0 ? 'true' : 'false' }}, 'opacity-0': {{ !isset($answers[$question['id']]) || $answers[$question['id']] != 0 ? 'true' : 'false' }} }"></i>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <label class="block cursor-pointer group">
                                    <input type="radio" 
                                           wire:click="saveAnswer({{ $question['id'] }}, 1)"
                                           name="question_{{ $question['id'] }}" 
                                           value="1"
                                           class="hidden peer"
                                           {{ isset($answers[$question['id']]) && $answers[$question['id']] == 1 ? 'checked' : '' }}>
                                    
                                    <div class="bg-white dark:bg-gray-800 border-2 rounded-2xl p-6 transition-all duration-200 border-gray-200 dark:border-gray-700 group-hover:border-red-300"
                                         :class="{
                                             'border-red-500 bg-red-50 dark:bg-red-900/20 shadow-lg': {{ isset($answers[$question['id']]) && $answers[$question['id']] == 1 ? 'true' : 'false' }}
                                         }">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-pink-600 text-white flex items-center justify-center font-bold shadow-lg">
                                                    <i class="fas fa-times text-2xl"></i>
                                                </div>
                                                <span class="text-xl font-semibold text-gray-800 dark:text-white">False</span>
                                            </div>
                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                                                 :class="{
                                                     'border-red-500 bg-red-500': {{ isset($answers[$question['id']]) && $answers[$question['id']] == 1 ? 'true' : 'false' }},
                                                     'border-gray-300 dark:border-gray-600': {{ !isset($answers[$question['id']]) || $answers[$question['id']] != 1 ? 'true' : 'false' }}
                                                 }">
                                                <i class="fas fa-check text-white text-xs"
                                                   :class="{ 'opacity-100': {{ isset($answers[$question['id']]) && $answers[$question['id']] == 1 ? 'true' : 'false' }}, 'opacity-0': {{ !isset($answers[$question['id']]) || $answers[$question['id']] != 1 ? 'true' : 'false' }} }"></i>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Bottom Navigation - FIXED TO BOTTOM OF SCREEN --}}
                <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 md:p-6 shadow-2xl z-[100]">
                    <div class="max-w-4xl mx-auto">
                        {{-- Progress Info --}}
                        <div class="flex items-center justify-between mb-3 text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check-circle text-green-500"></i>
                                <span>{{ $this->getAnsweredQuestionsCount() }} answered</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-circle-notch text-gray-400"></i>
                                <span>{{ count($questions) - $this->getAnsweredQuestionsCount() }} remaining</span>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-4 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-full rounded-full transition-all duration-500"
                                 :style="`width: ${({{ $this->getAnsweredQuestionsCount() }} / {{ count($questions) }}) * 100}%`"></div>
                        </div>

                        {{-- Navigation Buttons --}}
                        <div class="flex items-center justify-between gap-3">
                            <button wire:click="previousQuestion"
                                    class="flex items-center space-x-2 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex-1 justify-center"
                                    {{ !$this->canGoPrevious() ? 'disabled' : '' }}>
                                <i class="fas fa-arrow-left"></i>
                                <span class="hidden sm:inline">Previous</span>
                            </button>

                            <div class="flex-1 text-center min-w-0 px-2">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Progress</div>
                                <div class="text-lg font-bold text-gray-800 dark:text-white whitespace-nowrap">
                                    {{ round($this->getProgressPercentage(), 1) }}%
                                </div>
                            </div>

                            @if($this->isLastQuestion())
                            <button wire:click="showSubmitConfirmation"
                                    class="flex items-center space-x-2 px-4 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-bold hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg flex-1 justify-center">
                                <i class="fas fa-paper-plane"></i>
                                <span class="hidden sm:inline">Submit</span>
                            </button>
                            @else
                            <button wire:click="nextQuestion"
                                    class="flex items-center space-x-2 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-bold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg flex-1 justify-center">
                                <span class="hidden sm:inline">Next</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endif

    {{-- Submit Confirmation Modal --}}
    @if($showSubmitModal)
    <div class="fixed inset-0 bg-black bg-opacity-75 z-[110] flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full shadow-2xl">
            <div class="p-8">
                <div class="w-20 h-20 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-exclamation-triangle text-white text-4xl"></i>
                </div>

                <h3 class="text-2xl font-bold text-center text-gray-800 dark:text-white mb-4">
                    Submit Your Exam?
                </h3>

                <p class="text-center text-gray-600 dark:text-gray-400 mb-6">
                    Once submitted, you cannot change your answers. Make sure you've reviewed all questions.
                </p>

                {{-- Summary Stats --}}
                <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl p-6 mb-6 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Questions Answered:</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $this->getAnsweredQuestionsCount() }} / {{ count($questions) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Time Remaining:</span>
                        <span class="font-bold text-gray-800 dark:text-white" x-text="formatTime(timeRemaining)"></span>
                    </div>
                    @if($this->getAnsweredQuestionsCount() < count($questions))
                    <div class="flex items-start space-x-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <i class="fas fa-info-circle text-yellow-600 mt-0.5"></i>
                        <span class="text-sm text-yellow-800 dark:text-yellow-300">
                            You have {{ count($questions) - $this->getAnsweredQuestionsCount() }} unanswered questions
                        </span>
                    </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-4">
                    <button wire:click="cancelSubmission"
                            class="flex-1 px-6 py-4 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button wire:click="submitExam"
                            class="flex-1 px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-bold hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg">
                        <i class="fas fa-check mr-2"></i>Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- OPTIMIZED Alpine.js Components - Register BEFORE page render --}}
<script>
// Register Alpine components IMMEDIATELY before Alpine initializes
document.addEventListener('alpine:init', () => {
    // Enhanced Timer Component
    Alpine.data('enhancedTimer', () => ({
        // Props from data attributes
        timeRemaining: 0,
        totalDuration: 3600,
        questionCount: 0,
        currentQuestionIndex: 0,
        
        // Timer state
        percentage: 100,
        circumference: 2 * Math.PI * 20,
        strokeDashoffset: 0,
        
        // Warning modal
        showWarning: false,
        warningLevel: 'info',
        warningTitle: '',
        warningMessage: '',
        
        // Toasts
        toasts: [],
        toastIdCounter: 0,
        
        // Warning thresholds
        warningThresholds: {
            '50': { shown: false, percent: 50, title: 'Halfway There!', message: 'You have completed 50% of your time.' },
            '25': { shown: false, percent: 25, title: 'Quarter Time Remaining', message: '25% of time left.' },
            '10': { shown: false, percent: 10, title: 'Time Running Low', message: 'Only 10% remaining.' },
            '5': { shown: false, percent: 5, title: 'Critical: 5% Time Left', message: 'Very little time left!' }
        },
        minuteWarnings: [10, 5, 2, 1],
        minuteWarningsShown: [],
        timerInterval: null,

        init() {
            // Get initial values from data attributes
            const el = this.$el;
            this.timeRemaining = parseInt(el.getAttribute('data-time-remaining')) || 0;
            this.totalDuration = parseInt(el.getAttribute('data-total-duration')) || 3600;
            this.questionCount = parseInt(el.getAttribute('data-question-count')) || 0;
            this.currentQuestionIndex = parseInt(el.getAttribute('data-current-index')) || 0;
            
            this.calculateProgress();

            window.addEventListener('cbt-timer-sync', (event) => {
                const nextSeconds = Number(event?.detail?.seconds);
                if (!Number.isNaN(nextSeconds)) {
                    this.timeRemaining = nextSeconds;
                    this.calculateProgress();
                }
            });

            window.addEventListener('cbt-timer-stop', () => {
                this.stopInternalTimer();
            });
            
            // Start internal countdown timer
            this.startInternalTimer();
        },
        
        startInternalTimer() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            
            this.timerInterval = setInterval(() => {
                if (this.timeRemaining > 0) {
                    this.timeRemaining--;
                    this.checkWarnings();
                    this.calculateProgress();
                }
            }, 1000);
        },
        
        stopInternalTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        },

        calculateProgress() {
            this.percentage = this.totalDuration > 0 ? (this.timeRemaining / this.totalDuration) * 100 : 100;
            this.strokeDashoffset = this.circumference - (this.percentage / 100) * this.circumference;
        },

        checkWarnings() {
            const currentPercent = this.percentage;
            
            // Percentage-based warnings
            Object.keys(this.warningThresholds).forEach(key => {
                const threshold = this.warningThresholds[key];
                if (!threshold.shown && currentPercent <= threshold.percent && currentPercent > 0) {
                    threshold.shown = true;
                    this.showToast(threshold.title, this.getWarningLevel(threshold.percent));
                }
            });
            
            // Minute-based warnings
            const minutesLeft = Math.floor(this.timeRemaining / 60);
            this.minuteWarnings.forEach(minute => {
                if (minutesLeft === minute && !this.minuteWarningsShown.includes(minute)) {
                    this.minuteWarningsShown.push(minute);
                    
                    if (minute === 1) {
                        this.showTimeWarning('⚠️ 1 MINUTE LEFT!', 'Only 60 seconds remaining!', 'critical');
                        this.showToast('1 MINUTE LEFT!', 'critical');
                    } else {
                        this.showToast(`${minute} minutes remaining`, minute <= 2 ? 'urgent' : 'warning');
                    }
                }
            });
        },

        getWarningLevel(percent) {
            if (percent <= 5) return 'critical';
            if (percent <= 10) return 'warning';
            return 'info';
        },

        showTimeWarning(title, message, level) {
            this.warningTitle = title;
            this.warningMessage = message;
            this.warningLevel = level;
            this.showWarning = true;

            if (level !== 'critical') {
                setTimeout(() => {
                    this.showWarning = false;
                }, 5000);
            }
        },

        dismissWarning() {
            this.showWarning = false;
        },

        showToast(message, type = 'info') {
            const toast = {
                id: ++this.toastIdCounter,
                message: message,
                time: this.formatTime(this.timeRemaining),
                type: type,
                visible: true
            };

            this.toasts.push(toast);
            setTimeout(() => this.removeToast(toast.id), 5000);
        },

        removeToast(id) {
            const toast = this.toasts.find(t => t.id === id);
            if (toast) {
                toast.visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        },

        formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            if (hours > 0) {
                return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            }
            return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },

        get questionsRemaining() {
            return this.questionCount - this.currentQuestionIndex;
        },

        get avgTimePerQuestion() {
            if (this.questionsRemaining === 0) return '0s';
            const avgSeconds = Math.floor(this.timeRemaining / this.questionsRemaining);
            return avgSeconds >= 60 ? `${Math.floor(avgSeconds / 60)}m` : `${avgSeconds}s`;
        }
    }));

    // Main CBT Exam Component
    Alpine.data('modernCbtExam', () => ({
        timeRemaining: @js($timeRemaining ?? 0),
        timerInterval: null,
        heartbeatInterval: null,
        heartbeatInFlight: false,
        sidebarOpen: false,
        examStarted: @js($examStarted ?? false),
        examCompleted: @js($examCompleted ?? false),
        mathJaxReady: false,
        mathJaxQueue: [],
        mathJaxTimeout: null,

        init() {
            this.initializeMathJax();
            this.setupEventListeners();
            this.setupSecurityListeners();

            if (this.isExamActive()) {
                this.startTimer();
                this.syncWithServer(true);
            }
        },

        initializeMathJax() {
            if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                this.mathJaxReady = true;
                this.processMathJaxQueue();
                return;
            }

            document.addEventListener('mathjax-loaded', () => {
                this.mathJaxReady = true;
                this.processMathJaxQueue();
            });
        },

        processMathJaxQueue() {
            if (!this.mathJaxReady || !this.mathJaxQueue.length) {
                return;
            }

            if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                MathJax.typesetPromise(this.mathJaxQueue)
                    .then(() => {
                        this.mathJaxQueue = [];
                    })
                    .catch(err => console.error('MathJax error:', err));
            }
        },

        renderMathInElement(element) {
            if (!element) {
                return;
            }

            if (this.mathJaxReady && typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                MathJax.typesetPromise([element]).catch(err => {
                    console.error('MathJax element error:', err);
                });
                return;
            }

            this.mathJaxQueue.push(element);
        },

        setupEventListeners() {
            Livewire.on('startTimer', () => {
                this.examStarted = true;
                this.examCompleted = false;
                this.startTimer();
                this.syncWithServer(true);
            });

            Livewire.on('examCompleted', () => {
                this.examCompleted = true;
                this.stopTimer();
            });

            Livewire.on('questionChanged', () => {
                clearTimeout(this.mathJaxTimeout);
                this.mathJaxTimeout = setTimeout(() => {
                    this.renderMath();
                }, 100);
            });

            Livewire.hook('morph.updated', () => {
                clearTimeout(this.mathJaxTimeout);
                this.mathJaxTimeout = setTimeout(() => {
                    this.renderMath();
                }, 100);
            });
        },

        setupSecurityListeners() {
            window.addEventListener('beforeunload', (event) => {
                if (!this.isExamActive()) {
                    return;
                }

                event.preventDefault();
                event.returnValue = 'You have an active CBT exam. Leaving now may trigger a security violation.';
            });

            document.addEventListener('visibilitychange', () => {
                if (!this.isExamActive()) {
                    return;
                }

                if (document.hidden) {
                    this.$wire.call('handleSecurityViolation', 'visibility_change', 'tab_or_window_hidden');
                }
            });

            window.addEventListener('blur', () => {
                if (!this.isExamActive()) {
                    return;
                }

                this.$wire.call('handleSecurityViolation', 'app_switch', 'window_blur');
            });

            window.addEventListener('keydown', (event) => {
                if (!this.isExamActive()) {
                    return;
                }

                const key = event.key.toLowerCase();
                const isRefreshAttempt = key === 'f5' || ((event.ctrlKey || event.metaKey) && key === 'r');

                if (isRefreshAttempt) {
                    event.preventDefault();
                    this.$wire.call('handleSecurityViolation', 'refresh_attempt', 'keyboard_shortcut');
                }
            });
        },

        isExamActive() {
            return this.examStarted && !this.examCompleted;
        },

        startTimer() {
            this.stopTimer();

            this.timerInterval = setInterval(() => {
                if (!this.isExamActive()) {
                    return;
                }

                if (this.timeRemaining > 0) {
                    this.timeRemaining--;
                }

                this.broadcastTimerSync();

                if (this.timeRemaining <= 30 || this.timeRemaining % 15 === 0) {
                    this.syncWithServer();
                }
            }, 1000);

            this.heartbeatInterval = setInterval(() => {
                this.syncWithServer();
            }, 15000);
        },

        stopTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }

            if (this.heartbeatInterval) {
                clearInterval(this.heartbeatInterval);
                this.heartbeatInterval = null;
            }

            window.dispatchEvent(new CustomEvent('cbt-timer-stop'));
        },

        async syncWithServer(force = false) {
            if (!this.isExamActive()) {
                return;
            }

            if (this.heartbeatInFlight && !force) {
                return;
            }

            this.heartbeatInFlight = true;
            try {
                const payload = await this.$wire.call('heartbeat', this.timeRemaining);
                if (payload && typeof payload.time_remaining !== 'undefined') {
                    this.timeRemaining = Number(payload.time_remaining) || 0;
                    this.broadcastTimerSync();
                }

                if (payload?.reload) {
                    window.location.reload();
                    return;
                }

                if (payload?.exam_completed || payload?.auto_submitted) {
                    this.examCompleted = true;
                    this.stopTimer();
                }
            } catch (error) {
                console.error('Timer sync failed', error);
            } finally {
                this.heartbeatInFlight = false;
            }
        },

        broadcastTimerSync() {
            window.dispatchEvent(new CustomEvent('cbt-timer-sync', {
                detail: {
                    seconds: this.timeRemaining,
                },
            }));
        },

        formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            if (hours > 0) {
                return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            }
            return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },

        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },

        renderMath() {
            if (!this.mathJaxReady) {
                return;
            }

            const mathElements = document.querySelectorAll('.math-content');
            if (mathElements.length > 0 && typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                MathJax.typesetPromise(Array.from(mathElements))
                    .catch(err => console.error('MathJax error:', err));
            }
        }
    }));
});

// Global helper for rendering math in specific elements
window.renderMathInElement = function(element) {
    if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
        MathJax.typesetPromise([element]).catch(err => {
            console.error('MathJax error:', err);
        });
    }
};
</script>
