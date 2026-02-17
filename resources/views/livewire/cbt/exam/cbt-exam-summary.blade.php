<div x-data="summaryReview()" x-init="init()"
    class="summary-review-component min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-900 dark:to-gray-800 p-4 md:p-6">

    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 md:p-8 mb-6 animate__animated animate__fadeInDown">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-2 flex items-center">
                        <i class="fas fa-clipboard-check text-blue-600 dark:text-blue-400 mr-3"></i>
                        Review Your Answers
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Please review your answers before final submission. You can jump to any question to make
                        changes.
                    </p>
                </div>

                @if($timeRemaining)
                    <div class="hidden md:flex items-center space-x-2 bg-blue-50 dark:bg-blue-900/20 px-4 py-3 rounded-xl">
                        <i class="fas fa-clock text-blue-600 dark:text-blue-400"></i>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Time Left</div>
                            <div class="text-xl font-bold text-blue-600 dark:text-blue-400"
                                x-text="formatTime({{ $timeRemaining }})"></div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Progress Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div
                    class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ count($questions) }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Total Questions</div>
                </div>

                <div
                    class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400" x-text="answeredCount"></div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Answered</div>
                </div>

                <div
                    class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-red-600 dark:text-red-400" x-text="unansweredCount"></div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Unanswered</div>
                </div>

                <div
                    class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/30 dark:to-yellow-800/30 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ count($flaggedQuestions) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Flagged</div>
                </div>
            </div>

            {{-- Overall Progress Bar --}}
            <div class="mt-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Overall Progress</span>
                    <span class="text-sm font-bold text-blue-600 dark:text-blue-400"
                        x-text="`${completionPercentage}%`"></span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-full rounded-full transition-all duration-500"
                        :style="`width: ${completionPercentage}%`"></div>
                </div>
            </div>
        </div>

        {{-- Quick Action Buttons --}}
        <div class="flex flex-wrap gap-3 mb-6 animate__animated animate__fadeIn" style="animation-delay: 0.1s">
            <button @click="filterView = 'all'" class="filter-btn" :class="{ 'active': filterView === 'all' }">
                <i class="fas fa-th mr-2"></i>All Questions
                <span class="badge" x-text="{{ count($questions) }}"></span>
            </button>

            <button @click="filterView = 'unanswered'" class="filter-btn"
                :class="{ 'active': filterView === 'unanswered' }">
                <i class="fas fa-circle text-red-500 mr-2"></i>Unanswered
                <span class="badge bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400"
                    x-text="unansweredCount"></span>
            </button>

            <button @click="filterView = 'flagged'" class="filter-btn" :class="{ 'active': filterView === 'flagged' }">
                <i class="fas fa-flag text-yellow-500 mr-2"></i>Flagged
                <span
                    class="badge bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400">{{ count($flaggedQuestions) }}</span>
            </button>

            <button @click="filterView = 'answered'" class="filter-btn"
                :class="{ 'active': filterView === 'answered' }">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>Answered
                <span class="badge bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400"
                    x-text="answeredCount"></span>
            </button>
        </div>

        {{-- Smart Recommendations --}}
        <div x-show="unansweredCount > 0 || flaggedQuestions.length > 0"
            class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-6 rounded-lg mb-6 animate__animated animate__fadeIn"
            style="animation-delay: 0.2s">
            <div class="flex items-start">
                <i class="fas fa-lightbulb text-yellow-600 dark:text-yellow-400 text-2xl mr-4 mt-1"></i>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Recommendations</h3>
                    <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                        <li x-show="unansweredCount > 0" class="flex items-start">
                            <i class="fas fa-arrow-right text-yellow-600 mr-2 mt-1"></i>
                            <span>You have <strong x-text="unansweredCount"></strong> unanswered question<span
                                    x-show="unansweredCount > 1">s</span>. Consider answering them before
                                submitting.</span>
                        </li>
                        <li x-show="flaggedQuestions.length > 0" class="flex items-start">
                            <i class="fas fa-arrow-right text-yellow-600 mr-2 mt-1"></i>
                            <span>You flagged <strong>{{ count($flaggedQuestions) }}</strong> question<span
                                    x-show="{{ count($flaggedQuestions) }} > 1">s</span> for review. Make sure to check
                                them.</span>
                        </li>
                        <li x-show="completionPercentage === 100"
                            class="flex items-start text-green-700 dark:text-green-400">
                            <i class="fas fa-check-circle text-green-600 mr-2 mt-1"></i>
                            <span>Great job! You've answered all questions. Review and submit when ready.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Question Grid --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 md:p-8 mb-6 animate__animated animate__fadeInUp"
            style="animation-delay: 0.3s">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center">
                    <i class="fas fa-grid-2 text-blue-600 dark:text-blue-400 mr-2"></i>
                    Question Overview
                    <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400"
                        x-text="`(Showing ${filteredQuestions.length} of {{ count($questions) }})`"></span>
                </h2>

                <div class="flex items-center space-x-4">
                    {{-- View Toggle --}}
                    <div class="hidden sm:flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                        <button @click="viewMode = 'grid'" class="view-toggle-btn"
                            :class="{ 'active': viewMode === 'grid' }" title="Grid View">
                            <i class="fas fa-th"></i>
                        </button>
                        <button @click="viewMode = 'list'" class="view-toggle-btn"
                            :class="{ 'active': viewMode === 'list' }" title="List View">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Legend --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6 text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center text-white font-bold">
                        <i class="fas fa-check text-xs"></i>
                    </div>
                    <span class="text-gray-600 dark:text-gray-400">Answered</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div
                        class="w-8 h-8 border-2 border-red-300 dark:border-red-600 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 font-bold">
                        1
                    </div>
                    <span class="text-gray-600 dark:text-gray-400">Unanswered</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center text-white">
                        <i class="fas fa-flag text-xs"></i>
                    </div>
                    <span class="text-gray-600 dark:text-gray-400">Flagged</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div
                        class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold shadow-lg">
                        1
                    </div>
                    <span class="text-gray-600 dark:text-gray-400">Current</span>
                </div>
            </div>

            {{-- Grid View --}}
            <div x-show="viewMode === 'grid'"
                class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12 gap-3">
                <template x-for="(question, index) in filteredQuestions" :key="question.id">
                    <button @click="goToQuestion(question.index)"
                        class="question-card relative aspect-square flex items-center justify-center rounded-xl font-bold text-sm transition-all transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="getQuestionClass(question)" :title="getQuestionTooltip(question)">
                        <span x-text="question.index + 1"></span>
                        <i x-show="question.isFlagged"
                            class="fas fa-flag absolute -top-1 -right-1 text-yellow-500 dark:text-yellow-400 text-xs"></i>
                    </button>
                </template>
            </div>

            {{-- List View --}}
            <div x-show="viewMode === 'list'" class="space-y-3">
                <template x-for="(question, index) in filteredQuestions" :key="question.id">
                    <div @click="goToQuestion(question.index)"
                        class="question-list-item flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer transition-all hover:shadow-md"
                        :class="{
                             'bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-700': question.isAnswered,
                             'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-700': !question.isAnswered,
                             'ring-2 ring-blue-500': question.index === {{ $currentQuestionIndex }}
                         }">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-white"
                                :class="{
                                     'bg-green-500': question.isAnswered,
                                     'bg-red-500': !question.isAnswered,
                                     'bg-blue-600': question.index === {{ $currentQuestionIndex }}
                                 }">
                                <span x-text="question.index + 1"></span>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-800 dark:text-white">
                                    Question <span x-text="question.index + 1"></span>
                                </div>
                                <div class="flex items-center space-x-3 text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <span x-text="question.type"></span>
                                    <span>•</span>
                                    <span x-text="`${question.points} points`"></span>
                                    <span x-show="question.isFlagged" class="text-yellow-600 dark:text-yellow-400">
                                        • <i class="fas fa-flag"></i> Flagged
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span x-show="question.isAnswered"
                                class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs font-semibold">
                                <i class="fas fa-check mr-1"></i>Answered
                            </span>
                            <span x-show="!question.isAnswered"
                                class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-xs font-semibold">
                                <i class="fas fa-circle mr-1"></i>Not Answered
                            </span>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </div>
                    </div>
                </template>
            </div>

            {{-- No Results --}}
            <div x-show="filteredQuestions.length === 0" class="text-center py-12">
                <i class="fas fa-search text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400 text-lg">No questions match this filter</p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 sticky bottom-0 z-10 animate__animated animate__fadeInUp"
            style="animation-delay: 0.4s">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                    <i class="fas fa-info-circle text-blue-600 dark:text-blue-400"></i>
                    <span>Click on any question to review or change your answer</span>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <button @click="closeReview()"
                        class="flex items-center justify-center px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Continue Exam
                    </button>

                    <button @click="confirmSubmit()"
                        class="flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-bold hover:from-green-700 hover:to-emerald-700 transition-all transform hover:scale-[1.02] shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Submit Exam
                        <span x-show="unansweredCount > 0" class="ml-2 px-2 py-0.5 bg-red-500 rounded-full text-xs">
                            <span x-text="unansweredCount"></span> unanswered
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('summaryReview', () => ({
                // Data from props
                questions: @js($questions ?? []),
                answers: @js($answers ?? []),
                currentQuestionIndex: @js($currentQuestionIndex ?? 0),
                flaggedQuestions: @js($flaggedQuestions ?? []),
                timeRemaining: @js($timeRemaining ?? 0),

                // UI State
                filterView: 'all', // all, answered, unanswered, flagged
                viewMode: 'grid', // grid, list

                init() {
                    // Save settings to localStorage
                    const savedViewMode = localStorage.getItem('summaryViewMode');
                    if (savedViewMode) {
                        this.viewMode = savedViewMode;
                    }

                    this.$watch('viewMode', (value) => {
                        localStorage.setItem('summaryViewMode', value);
                    });
                },

                get processedQuestions() {
                    return this.questions.map((q, index) => ({
                        id: q.id,
                        index: index,
                        type: this.formatQuestionType(q.question_type || 'multiple_choice'),
                        points: q.points || 1,
                        isAnswered: this.answers[q.id] !== null && this.answers[q.id] !== undefined && this.answers[q.id] !== '',
                        isFlagged: this.flaggedQuestions.includes(q.id)
                    }));
                },

                get filteredQuestions() {
                    let filtered = this.processedQuestions;

                    switch (this.filterView) {
                        case 'answered':
                            return filtered.filter(q => q.isAnswered);
                        case 'unanswered':
                            return filtered.filter(q => !q.isAnswered);
                        case 'flagged':
                            return filtered.filter(q => q.isFlagged);
                        default:
                            return filtered;
                    }
                },

                get answeredCount() {
                    return this.processedQuestions.filter(q => q.isAnswered).length;
                },

                get unansweredCount() {
                    return this.processedQuestions.filter(q => !q.isAnswered).length;
                },

                get completionPercentage() {
                    return this.questions.length > 0
                        ? Math.round((this.answeredCount / this.questions.length) * 100)
                        : 0;
                },

                formatQuestionType(type) {
                    const types = {
                        'multiple_choice': 'Multiple Choice',
                        'true_false': 'True/False',
                        'short_answer': 'Short Answer',
                        'essay': 'Essay'
                    };
                    return types[type] || type;
                },

                getQuestionClass(question) {
                    let classes = [];

                    if (question.index === this.currentQuestionIndex) {
                        classes.push('bg-blue-600 text-white shadow-lg scale-110');
                    } else if (question.isAnswered) {
                        classes.push('bg-green-500 text-white hover:bg-green-600');
                    } else {
                        classes.push('border-2 border-red-300 dark:border-red-600 text-gray-600 dark:text-gray-400 hover:border-red-500');
                    }

                    return classes.join(' ');
                },

                getQuestionTooltip(question) {
                    let tooltip = `Question ${question.index + 1}`;
                    if (question.isAnswered) {
                        tooltip += ' - Answered';
                    } else {
                        tooltip += ' - Not Answered';
                    }
                    if (question.isFlagged) {
                        tooltip += ' (Flagged)';
                    }
                    return tooltip;
                },

                goToQuestion(index) {
                    // Dispatch event to parent component
                    this.$dispatch('go-to-question', { index: index });
                    this.closeReview();
                },

                closeReview() {
                    this.$dispatch('close-summary-review');
                },

                confirmSubmit() {
                    if (this.unansweredCount > 0) {
                        const confirmed = confirm(
                            `You have ${this.unansweredCount} unanswered question${this.unansweredCount > 1 ? 's' : ''}. ` +
                            `Are you sure you want to submit?`
                        );
                        if (!confirmed) return;
                    }

                    this.$dispatch('confirm-submit-exam');
                },

                formatTime(seconds) {
                    const hours = Math.floor(seconds / 3600);
                    const minutes = Math.floor((seconds % 3600) / 60);
                    const secs = seconds % 60;

                    if (hours > 0) {
                        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                    }
                    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                }
            }));
        });
    </script>
@endpush

@push('styles')
    <style>
        .summary-review-component {
            min-height: 100vh;
        }

        /* Filter Buttons */
        .filter-btn {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            font-weight: 600;
            color: #4b5563;
            transition: all 0.2s;
            cursor: pointer;
        }

        .dark .filter-btn {
            background: #1f2937;
            border-color: #374151;
            color: #9ca3af;
        }

        .filter-btn:hover {
            border-color: #3b82f6;
            background: #eff6ff;
            color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .dark .filter-btn:hover {
            background: #1e3a8a;
            border-color: #60a5fa;
            color: #93c5fd;
        }

        .filter-btn.active {
            border-color: #3b82f6;
            background: linear-gradient(to right, #3b82f6, #6366f1);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .filter-btn .badge {
            margin-left: 0.5rem;
            padding: 0.125rem 0.5rem;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .dark .filter-btn .badge {
            background: #1e3a8a;
            color: #93c5fd;
        }

        .filter-btn.active .badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* View Toggle */
        .view-toggle-btn {
            padding: 0.5rem 0.75rem;
            background: transparent;
            color: #6b7280;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .view-toggle-btn:hover {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .view-toggle-btn.active {
            background: white;
            color: #3b82f6;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .dark .view-toggle-btn.active {
            background: #374151;
            color: #60a5fa;
        }

        /* Question Cards */
        .question-card {
            position: relative;
            user-select: none;
        }

        .question-card:active {
            transform: scale(0.95);
        }

        /* Question List Item */
        .question-list-item {
            user-select: none;
        }

        .question-list-item:active {
            transform: scale(0.99);
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 640px) {
            .summary-review-component {
                padding: 1rem;
            }

            .filter-btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }

            .question-card {
                font-size: 0.875rem;
            }
        }

        /* Print styles */
        @media print {
            .summary-review-component {
                display: none !important;
            }
        }
    </style>
@endpush