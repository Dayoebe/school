<div>
    <!-- Cleanup Button -->
    <button 
        wire:click="openModal"
        class="bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-medium px-6 py-3 rounded-xl shadow-lg transition-all duration-300 flex items-center">
        <i class="fas fa-broom mr-2"></i> Cleanup Invalid Results
    </button>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" 
             x-data="{ show: @entangle('showModal') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
                 @click.away="$wire.closeModal()"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-90"
                 x-transition:enter-end="opacity-100 transform scale-100">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-red-600 to-rose-600 text-white px-6 py-4 rounded-t-2xl flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-3xl mr-3"></i>
                        <div>
                            <h3 class="text-2xl font-bold">Invalid Results Cleanup</h3>
                            <p class="text-red-100 text-sm">Review and remove invalid data</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="text-white hover:text-red-100 transition-colors">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6">
                    @if($cleanupResults)
                        <!-- Summary -->
                        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-4 mb-6">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-yellow-600 text-2xl mr-3 mt-1"></i>
                                <div class="flex-1">
                                    <h4 class="font-bold text-yellow-900 text-lg mb-2">Analysis Complete</h4>
                                    <p class="text-yellow-800 mb-3">
                                        Found <span class="font-bold text-2xl">{{ $cleanupResults['total_invalid'] }}</span> invalid result(s) that will be deleted.
                                    </p>
                                    
                                    @if($cleanupResults['total_invalid'] == 0)
                                        <div class="bg-green-100 border border-green-300 rounded-lg p-3 text-green-800">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            No invalid results found. Your database is clean!
                                        </div>
                                    @else
                                        <p class="text-yellow-700 text-sm">
                                            <strong>Warning:</strong> This action cannot be undone. Please review the details below.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($cleanupResults['total_invalid'] > 0)
                            <!-- Details -->
                            <div class="space-y-4">
                                <!-- Type 1: Not Enrolled -->
                                @if($cleanupResults['not_enrolled']->isNotEmpty())
                                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                                        <h5 class="font-bold text-red-900 mb-2 flex items-center">
                                            <i class="fas fa-user-times mr-2"></i>
                                            Students Not Enrolled in Subject ({{ $cleanupResults['not_enrolled']->count() }})
                                        </h5>
                                        <div class="max-h-48 overflow-y-auto space-y-2">
                                            @foreach($cleanupResults['not_enrolled']->take(10) as $result)
                                                <div class="bg-white rounded-lg p-3 text-sm border border-red-100">
                                                    <p class="font-medium text-gray-900">
                                                        {{ $result->student->user->name ?? 'Unknown Student' }}
                                                    </p>
                                                    <p class="text-gray-600">
                                                        Subject: <span class="font-medium">{{ $result->subject->name ?? 'Unknown' }}</span> | 
                                                        Year: {{ $result->academicYear->name ?? 'N/A' }} | 
                                                        Term: {{ $result->semester->name ?? 'N/A' }}
                                                    </p>
                                                </div>
                                            @endforeach
                                            @if($cleanupResults['not_enrolled']->count() > 10)
                                                <p class="text-sm text-gray-600 italic text-center py-2">
                                                    ... and {{ $cleanupResults['not_enrolled']->count() - 10 }} more
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Type 2: Deleted Students -->
                                @if($cleanupResults['deleted_students']->isNotEmpty())
                                    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
                                        <h5 class="font-bold text-orange-900 mb-2 flex items-center">
                                            <i class="fas fa-user-slash mr-2"></i>
                                            Results with Deleted Students ({{ $cleanupResults['deleted_students']->count() }})
                                        </h5>
                                        <p class="text-sm text-orange-700 mb-2">
                                            These results belong to students who have been soft-deleted
                                        </p>
                                        <div class="max-h-32 overflow-y-auto">
                                            @foreach($cleanupResults['deleted_students']->take(5) as $result)
                                                <div class="bg-white rounded-lg p-2 text-sm mb-2 border border-orange-100">
                                                    Student ID: {{ $result->student_record_id }} | 
                                                    Subject: {{ $result->subject->name ?? 'Unknown' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Type 3: Orphaned Results -->
                                @if($cleanupResults['orphaned_results']->isNotEmpty())
                                    <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
                                        <h5 class="font-bold text-purple-900 mb-2 flex items-center">
                                            <i class="fas fa-unlink mr-2"></i>
                                            Orphaned Results - No Student Record ({{ $cleanupResults['orphaned_results']->count() }})
                                        </h5>
                                        <p class="text-sm text-purple-700">
                                            These results reference student records that no longer exist
                                        </p>
                                    </div>
                                @endif

                                <!-- Type 4: Orphaned Subjects -->
                                @if($cleanupResults['orphaned_subjects']->isNotEmpty())
                                    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                                        <h5 class="font-bold text-indigo-900 mb-2 flex items-center">
                                            <i class="fas fa-book-dead mr-2"></i>
                                            Results with Non-existent Subjects ({{ $cleanupResults['orphaned_subjects']->count() }})
                                        </h5>
                                        <p class="text-sm text-indigo-700">
                                            These results reference subjects that no longer exist
                                        </p>
                                    </div>
                                @endif

                                <!-- Type 5: Wrong Class Subjects -->
                                @if($cleanupResults['wrong_class_subjects']->isNotEmpty())
                                    <div class="bg-pink-50 border border-pink-200 rounded-xl p-4">
                                        <h5 class="font-bold text-pink-900 mb-2 flex items-center">
                                            <i class="fas fa-exchange-alt mr-2"></i>
                                            Wrong Class-Subject Assignments ({{ $cleanupResults['wrong_class_subjects']->count() }})
                                        </h5>
                                        <p class="text-sm text-pink-700 mb-2">
                                            Subjects don't belong to the student's class
                                        </p>
                                        <div class="max-h-32 overflow-y-auto">
                                            @foreach($cleanupResults['wrong_class_subjects']->take(5) as $result)
                                                <div class="bg-white rounded-lg p-2 text-sm mb-2 border border-pink-100">
                                                    {{ $result->student->user->name ?? 'Unknown' }} 
                                                    ({{ $result->student->myClass->name ?? 'No Class' }}) - 
                                                    {{ $result->subject->name ?? 'Unknown Subject' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <!-- Loading -->
                        <div class="text-center py-12">
                            <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-4"></i>
                            <p class="text-gray-600">Analyzing results...</p>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex items-center justify-between border-t">
                    <button 
                        wire:click="closeModal"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-6 py-3 rounded-xl transition-colors">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>

                    @if($cleanupResults && $cleanupResults['total_invalid'] > 0)
                        <button 
                            wire:click="executeCleanup"
                            wire:loading.attr="disabled"
                            @if($isProcessing) disabled @endif
                            class="bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold px-8 py-3 rounded-xl transition-all duration-300 shadow-lg flex items-center">
                            <span wire:loading.remove wire:target="executeCleanup">
                                <i class="fas fa-trash-alt mr-2"></i> Delete {{ $cleanupResults['total_invalid'] }} Invalid Result(s)
                            </span>
                            <span wire:loading wire:target="executeCleanup">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            @this.closeModal();
        }
    });
</script>
@endpush