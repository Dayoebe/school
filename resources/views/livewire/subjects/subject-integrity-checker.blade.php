<div>
    <!-- Trigger Button -->
    <button wire:click="openModal" 
            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
        <i class="fas fa-wrench mr-2"></i>Data Integrity Check
    </button>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                <!-- Header -->
                <div class="bg-gradient-to-r from-orange-600 to-red-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white">
                        <i class="fas fa-wrench mr-2"></i>Subject Data Integrity Checker
                    </h3>
                    <button wire:click="closeModal" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Flash Messages -->
                    @if (session()->has('check_complete'))
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-blue-400 text-xl mr-3"></i>
                                <p class="text-blue-700">{{ session('check_complete') }}</p>
                            </div>
                        </div>
                    @endif

                    @if (session()->has('success'))
                        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-400 text-xl mr-3"></i>
                                <p class="text-green-700">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle text-red-400 text-xl mr-3"></i>
                                <p class="text-red-700">{{ session('error') }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex gap-3 mb-6">
                        <button wire:click="runIntegrityCheck" 
                                wire:loading.attr="disabled"
                                class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                            <i class="fas fa-search mr-2"></i>
                            <span wire:loading.remove wire:target="runIntegrityCheck">Run Check</span>
                            <span wire:loading wire:target="runIntegrityCheck">Checking...</span>
                        </button>

                        @if(!empty($checkResults) && isset($checkResults['total_issues']) && $checkResults['total_issues'] > 0)
                            <button wire:click="fixIssues" 
                                    wire:loading.attr="disabled"
                                    wire:confirm="This will automatically fix all detected issues. Continue?"
                                    class="px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50">
                                <i class="fas fa-magic mr-2"></i>
                                <span wire:loading.remove wire:target="fixIssues">Auto-Fix All Issues</span>
                                <span wire:loading wire:target="fixIssues">Fixing...</span>
                            </button>
                        @endif
                    </div>

                    <!-- Results Display -->
                    @if(!empty($checkResults))
                        <div class="space-y-4">
                            <!-- Summary Card -->
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-6 border-2 {{ isset($checkResults['total_issues']) && $checkResults['total_issues'] > 0 ? 'border-orange-300' : 'border-green-300' }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900">Total Issues Found</h4>
                                        <p class="text-sm text-gray-600">Database integrity check complete</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-4xl font-bold {{ isset($checkResults['total_issues']) && $checkResults['total_issues'] > 0 ? 'text-orange-600' : 'text-green-600' }}">
                                            {{ $checkResults['total_issues'] ?? 0 }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Issue Categories -->
                            @if(isset($checkResults['duplicates']) && $checkResults['duplicates']['count'] > 0)
                                <div class="bg-white rounded-lg border-2 border-red-200 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-clone text-red-600"></i>
                                            </div>
                                            <div>
                                                <h5 class="font-bold text-gray-900">Duplicate Subjects</h5>
                                                <p class="text-sm text-gray-600">Subjects with same name</p>
                                            </div>
                                        </div>
                                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full font-semibold">
                                            {{ $checkResults['duplicates']['count'] }}
                                        </span>
                                    </div>
                                    <div class="space-y-3 pl-13">
                                        @foreach($checkResults['duplicates']['items'] as $group)
                                            <div class="bg-red-50 p-3 rounded border border-red-200">
                                                <p class="font-bold text-gray-900 mb-2">
                                                    <i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>
                                                    "{{ $group['name'] }}" - {{ $group['count'] }} duplicates found:
                                                </p>
                                                <div class="space-y-2 ml-4">
                                                    @foreach($group['subjects'] as $subject)
                                                        <div class="bg-white p-2 rounded border border-red-100 text-sm">
                                                            <div class="flex justify-between items-start">
                                                                <div>
                                                                    <span class="font-semibold text-gray-800">ID: {{ $subject['id'] }}</span>
                                                                    <span class="mx-2 text-gray-400">|</span>
                                                                    <span class="text-gray-700">Code: {{ $subject['short_name'] }}</span>
                                                                </div>
                                                                <span class="text-xs text-gray-500">{{ $subject['created_at'] }}</span>
                                                            </div>
                                                            <div class="mt-1 text-xs text-gray-600">
                                                                <i class="fas fa-chalkboard mr-1"></i>{{ $subject['classes_count'] }} classes
                                                                <span class="mx-2">•</span>
                                                                <i class="fas fa-user-tie mr-1"></i>{{ $subject['teachers_count'] }} teachers
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(isset($checkResults['no_classes']) && $checkResults['no_classes']['count'] > 0)
                                <div class="bg-white rounded-lg border-2 border-yellow-200 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-unlink text-yellow-600"></i>
                                            </div>
                                            <div>
                                                <h5 class="font-bold text-gray-900">Subjects Without Classes</h5>
                                                <p class="text-sm text-gray-600">Not assigned to any class</p>
                                            </div>
                                        </div>
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full font-semibold">
                                            {{ $checkResults['no_classes']['count'] }}
                                        </span>
                                    </div>
                                    <div class="space-y-1 pl-13">
                                        @foreach($checkResults['no_classes']['items'] as $item)
                                            <p class="text-sm text-gray-700">
                                                • {{ $item->name }} ({{ $item->short_name }})
                                            </p>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(isset($checkResults['orphaned_classes']) && $checkResults['orphaned_classes']['count'] > 0)
                                <div class="bg-white rounded-lg border-2 border-orange-200 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-exclamation-triangle text-orange-600"></i>
                                            </div>
                                            <div>
                                                <h5 class="font-bold text-gray-900">Orphaned Class Assignments</h5>
                                                <p class="text-sm text-gray-600">References to deleted classes</p>
                                            </div>
                                        </div>
                                        <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full font-semibold">
                                            {{ $checkResults['orphaned_classes']['count'] }}
                                        </span>
                                    </div>
                                    <div class="space-y-2 pl-13">
                                        @foreach($checkResults['orphaned_classes']['items'] as $item)
                                            <div class="bg-orange-50 p-2 rounded border border-orange-100 text-sm">
                                                <div class="flex justify-between items-center">
                                                    <div>
                                                        <span class="font-semibold text-gray-800">{{ $item->subject_name }}</span>
                                                        <span class="text-gray-600 text-xs ml-2">({{ $item->short_name }})</span>
                                                    </div>
                                                    <span class="text-xs text-gray-500">Subject ID: {{ $item->subject_id }}</span>
                                                </div>
                                                <p class="text-xs text-orange-700 mt-1">
                                                    <i class="fas fa-unlink mr-1"></i>
                                                    Assigned to non-existent class ID: {{ $item->my_class_id }}
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(isset($checkResults['orphaned_teachers']) && $checkResults['orphaned_teachers']['count'] > 0)
                                <div class="bg-white rounded-lg border-2 border-purple-200 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user-slash text-purple-600"></i>
                                            </div>
                                            <div>
                                                <h5 class="font-bold text-gray-900">Orphaned Teacher Assignments</h5>
                                                <p class="text-sm text-gray-600">References to deleted teachers</p>
                                            </div>
                                        </div>
                                        <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full font-semibold">
                                            {{ $checkResults['orphaned_teachers']['count'] }}
                                        </span>
                                    </div>
                                    <div class="space-y-2 pl-13">
                                        @foreach($checkResults['orphaned_teachers']['items'] as $item)
                                            <div class="bg-purple-50 p-2 rounded border border-purple-100 text-sm">
                                                <div class="flex justify-between items-center">
                                                    <div>
                                                        <span class="font-semibold text-gray-800">{{ $item->subject_name }}</span>
                                                        <span class="text-gray-600 text-xs ml-2">({{ $item->short_name }})</span>
                                                    </div>
                                                    <span class="text-xs text-gray-500">Subject ID: {{ $item->subject_id }}</span>
                                                </div>
                                                <p class="text-xs text-purple-700 mt-1">
                                                    <i class="fas fa-user-times mr-1"></i>
                                                    Assigned to deleted teacher ID: {{ $item->user_id }}
                                                    @if($item->is_general)
                                                        <span class="ml-2 px-1.5 py-0.5 bg-purple-200 rounded">(General)</span>
                                                    @elseif($item->my_class_id)
                                                        <span class="ml-2 px-1.5 py-0.5 bg-purple-200 rounded">(Class ID: {{ $item->my_class_id }})</span>
                                                    @endif
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(isset($checkResults['orphaned_students']) && $checkResults['orphaned_students']['count'] > 0)
                                <div class="bg-white rounded-lg border-2 border-pink-200 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-pink-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user-times text-pink-600"></i>
                                            </div>
                                            <div>
                                                <h5 class="font-bold text-gray-900">Orphaned Student Assignments</h5>
                                                <p class="text-sm text-gray-600">References to deleted students</p>
                                            </div>
                                        </div>
                                        <span class="px-3 py-1 bg-pink-100 text-pink-800 rounded-full font-semibold">
                                            {{ $checkResults['orphaned_students']['count'] }}
                                        </span>
                                    </div>
                                    <div class="space-y-2 pl-13">
                                        @foreach($checkResults['orphaned_students']['items']->take(10) as $item)
                                            <div class="bg-pink-50 p-2 rounded border border-pink-100 text-sm">
                                                <div class="flex justify-between items-center">
                                                    <div>
                                                        <span class="font-semibold text-gray-800">{{ $item->subject_name }}</span>
                                                        <span class="text-gray-600 text-xs ml-2">({{ $item->short_name }})</span>
                                                    </div>
                                                    <span class="text-xs text-gray-500">Subject ID: {{ $item->subject_id }}</span>
                                                </div>
                                                <p class="text-xs text-pink-700 mt-1">
                                                    <i class="fas fa-unlink mr-1"></i>
                                                    Assigned to deleted student record ID: {{ $item->student_record_id }}
                                                </p>
                                            </div>
                                        @endforeach
                                        @if($checkResults['orphaned_students']['count'] > 10)
                                            <p class="text-xs text-gray-500 italic">
                                                ... and {{ $checkResults['orphaned_students']['count'] - 10 }} more
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(isset($checkResults['legacy_cleanup']) && $checkResults['legacy_cleanup']['count'] > 0)
                                <div class="bg-white rounded-lg border-2 border-gray-200 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-archive text-gray-600"></i>
                                            </div>
                                            <div>
                                                <h5 class="font-bold text-gray-900">Legacy Records to Clean</h5>
                                                <p class="text-sm text-gray-600">Old subjects with no results</p>
                                            </div>
                                        </div>
                                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full font-semibold">
                                            {{ $checkResults['legacy_cleanup']['count'] }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <!-- All Clear Message -->
                            @if(isset($checkResults['total_issues']) && $checkResults['total_issues'] == 0)
                                <div class="bg-green-50 rounded-lg p-8 text-center">
                                    <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">All Clear!</h4>
                                    <p class="text-gray-600">No data integrity issues found in your subjects database.</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-clipboard-check text-gray-300 text-5xl mb-4"></i>
                            <h4 class="text-xl font-bold text-gray-700 mb-2">Ready to Check</h4>
                            <p class="text-gray-500">Click "Run Check" to scan for data integrity issues</p>
                        </div>
                    @endif

                    <!-- Fix Results -->
                    @if(!empty($fixResults))
                        <div class="mt-6 bg-green-50 rounded-lg p-6 border-2 border-green-200">
                            <h4 class="text-lg font-bold text-green-900 mb-4">
                                <i class="fas fa-check-double mr-2"></i>Issues Fixed
                            </h4>
                            <div class="space-y-2">
                                @foreach($fixResults as $key => $count)
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-700">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-semibold">
                                            {{ $count }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="border-t px-6 py-4 bg-gray-50 flex justify-end">
                    <button wire:click="closeModal" 
                            class="px-6 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>