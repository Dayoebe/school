<div x-data="enhancedTimer()" 
     x-init="init()" 
     class="enhanced-timer-component inline-block"
     data-time-remaining="{{ $timeRemaining ?? 0 }}"
     data-total-duration="{{ ($estimatedDuration ?? 60) * 60 }}"
     data-question-count="{{ $questionCount ?? 0 }}"
     data-current-index="{{ $currentQuestionIndex ?? 0 }}">
    
    {{-- Main Timer Display --}}
    <div class="flex items-center space-x-2 sm:space-x-4">
        {{-- Timer with Progress Ring --}}
        <div class="relative">
            <svg class="timer-ring" width="48" height="48" viewBox="0 0 48 48">
                <circle cx="24" cy="24" r="20" fill="none" stroke="currentColor" stroke-width="3"
                    class="text-white/20" />
                <circle cx="24" cy="24" r="20" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                    class="timer-progress-circle transition-all duration-1000" :class="{
                        'text-green-400': percentage > 50,
                        'text-yellow-400': percentage <= 50 && percentage > 25,
                        'text-orange-400': percentage <= 25 && percentage > 10,
                        'text-red-400': percentage <= 10
                    }"
                    :style="`stroke-dasharray: ${circumference}; stroke-dashoffset: ${strokeDashoffset}; transform: rotate(-90deg); transform-origin: center;`" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
                <i class="fas fa-clock text-white text-lg" :class="{ 'animate-pulse': timeRemaining <= 60 }"></i>
            </div>
        </div>

        {{-- Time Display --}}
        <div class="timer-display flex flex-col">
            <div class="flex items-center space-x-2 bg-white/20 backdrop-blur-sm px-3 sm:px-4 py-2 rounded-xl transition-all"
                :class="{
                    'bg-red-500/90': timeRemaining <= 60,
                    'bg-orange-500/70': timeRemaining > 60 && timeRemaining <= 300,
                    'bg-white/20': timeRemaining > 300
                }">
                <span class="font-mono text-lg sm:text-xl font-bold text-white" x-text="formatTime(timeRemaining)"></span>
                <div class="hidden lg:flex flex-col text-xs text-white/80">
                    <span x-show="timeRemaining > 300">Remaining</span>
                    <span x-show="timeRemaining <= 300 && timeRemaining > 60" class="text-orange-200 font-semibold">Hurry!</span>
                    <span x-show="timeRemaining <= 60" class="text-red-200 font-bold animate-pulse">URGENT!</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Timer Warnings Modal --}}
    <div x-show="showWarning" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90" 
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90" 
         @click="dismissWarning()"
         class="fixed inset-0 z-[105] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         style="display: none;">

        <div @click.stop class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6 sm:p-8"
            :class="{
                'border-4 border-yellow-500': warningLevel === 'info',
                'border-4 border-orange-500': warningLevel === 'warning',
                'border-4 border-red-500': warningLevel === 'critical'
            }">

            <div class="flex items-center justify-center mb-4">
                <div class="w-20 h-20 rounded-full flex items-center justify-center"
                    :class="{
                        'bg-yellow-100 dark:bg-yellow-900/30': warningLevel === 'info',
                        'bg-orange-100 dark:bg-orange-900/30': warningLevel === 'warning',
                        'bg-red-100 dark:bg-red-900/30': warningLevel === 'critical'
                    }">
                    <i class="fas text-4xl" :class="{
                        'fa-clock text-yellow-600 dark:text-yellow-400': warningLevel === 'info',
                        'fa-exclamation-triangle text-orange-600 dark:text-orange-400': warningLevel === 'warning',
                        'fa-exclamation-circle text-red-600 dark:text-red-400': warningLevel === 'critical'
                    }"></i>
                </div>
            </div>

            <h3 class="text-2xl font-bold text-center mb-3 text-gray-800 dark:text-white" x-text="warningTitle"></h3>
            <p class="text-center text-gray-600 dark:text-gray-300 mb-6" x-text="warningMessage"></p>

            <div class="bg-gray-100 dark:bg-gray-700 rounded-xl p-4 mb-6">
                <div class="text-center">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Time Remaining</div>
                    <div class="text-4xl font-bold font-mono" :class="{
                        'text-yellow-600 dark:text-yellow-400': warningLevel === 'info',
                        'text-orange-600 dark:text-orange-400': warningLevel === 'warning',
                        'text-red-600 dark:text-red-400': warningLevel === 'critical'
                    }" x-text="formatTime(timeRemaining)"></div>
                </div>
            </div>

            <button @click="dismissWarning()"
                class="w-full py-3 rounded-xl font-bold text-white transition-all shadow-lg"
                :class="{
                    'bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700': warningLevel === 'info',
                    'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700': warningLevel === 'warning',
                    'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700': warningLevel === 'critical'
                }">
                <i class="fas fa-check mr-2"></i>Got It, Continue Exam
            </button>
        </div>
    </div>

    {{-- Time Milestone Toast Notifications --}}
    <div class="fixed top-20 right-4 z-[106] space-y-2" style="max-width: 320px;">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-x-full opacity-0" 
                 x-transition:enter-end="translate-x-0 opacity-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-x-0 opacity-100" 
                 x-transition:leave-end="translate-x-full opacity-0"
                 class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-4 border-l-4"
                 :class="{
                     'border-blue-500': toast.type === 'info',
                     'border-yellow-500': toast.type === 'warning',
                     'border-orange-500': toast.type === 'urgent',
                     'border-red-500': toast.type === 'critical'
                 }">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <i class="fas text-xl" :class="{
                            'fa-info-circle text-blue-500': toast.type === 'info',
                            'fa-clock text-yellow-500': toast.type === 'warning',
                            'fa-exclamation-triangle text-orange-500': toast.type === 'urgent',
                            'fa-exclamation-circle text-red-500': toast.type === 'critical'
                        }"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 dark:text-white text-sm" x-text="toast.message"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="toast.time"></p>
                    </div>
                    <button @click="removeToast(toast.id)" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

{{-- NO SCRIPTS HERE - Component is registered in parent --}}