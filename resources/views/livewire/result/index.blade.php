<div x-data="{ activeTab: @entangle('activeTab') }" class="space-y-6">
    <!-- Header with Academic Period Selector -->
    <div class="bg-gradient-to-r from-indigo-600 to-teal-700 rounded-2xl shadow-xl p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white mb-2">Results Management System</h2>
                <p class="text-indigo-100">Select academic period to manage results</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                <livewire:result.academic-period-selector />
                <livewire:result.cleanup-invalid-results />
            </div>
        </div>
    </div>

    <!-- Display cleanup details if available -->
    @if(session('cleanup_details'))
        <div class="bg-green-50 border-2 border-green-200 rounded-2xl p-6">
            <div class="flex items-start">
                <i class="fas fa-check-circle text-green-600 text-3xl mr-4 mt-1"></i>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-green-900 mb-3">Cleanup Successful!</h3>
                    <ul class="space-y-2">
                        @foreach(session('cleanup_details') as $detail)
                            <li class="text-green-800 flex items-start">
                                <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                                <span>{{ $detail }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex flex-wrap -mb-px">
                <button @click="activeTab = 'dashboard'"
                    :class="activeTab === 'dashboard' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </button>

                <button @click="activeTab = 'individual'"
                    :class="activeTab === 'individual' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-user-edit mr-2"></i> Individual Upload
                </button>

                <button @click="activeTab = 'bulk'"
                    :class="activeTab === 'bulk' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-users-cog mr-2"></i> Bulk Upload
                </button>

                <button @click="activeTab = 'view'"
                    :class="activeTab === 'view' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-eye mr-2"></i> View Results
                </button>

                <button @click="activeTab = 'settings'"
                    :class="activeTab === 'settings' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-cog mr-2"></i> Term Settings
                </button>

                <button @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-history mr-2"></i> Student History
                </button>

                <button @click="activeTab = 'spreadsheet'" 
                    :class="activeTab === 'spreadsheet' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-table mr-2"></i> Class Spreadsheet
                </button>
                
                <button @click="activeTab = 'awards'" 
                    :class="activeTab === 'awards' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-trophy mr-2"></i> Awards
                </button>
                
                <button @click="activeTab = 'analytics'" 
                    :class="activeTab === 'analytics' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-chart-line mr-2"></i> Analytics
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <div x-show="activeTab === 'dashboard'" x-transition>
                <livewire:result.dashboard />
            </div>

            <div x-show="activeTab === 'individual'" x-transition>
                <livewire:result.upload.individual-upload />
            </div>

            <div x-show="activeTab === 'bulk'" x-transition>
                <livewire:result.upload.bulk-upload />
            </div>

            <div x-show="activeTab === 'view'" x-transition>
                <div x-data="{ viewTab: 'class' }" class="space-y-4">
                    <div class="flex space-x-2 border-b">
                        <button @click="viewTab = 'class'"
                            :class="viewTab === 'class' ? 'bg-indigo-50 text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600'"
                            class="px-4 py-2 font-medium">
                            Class Results
                        </button>
                        <button @click="viewTab = 'subject'"
                            :class="viewTab === 'subject' ? 'bg-indigo-50 text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600'"
                            class="px-4 py-2 font-medium">
                            Subject Results
                        </button>
                        <button @click="viewTab = 'student'"
                            :class="viewTab === 'student' ? 'bg-indigo-50 text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600'"
                            class="px-4 py-2 font-medium">
                            Student Results
                        </button>
                    </div>

                    <div x-show="viewTab === 'class'" x-transition>
                        <livewire:result.view.class-results />
                    </div>
                    <div x-show="viewTab === 'subject'" x-transition>
                        <livewire:result.view.subject-results />
                    </div>
                    <div x-show="viewTab === 'student'" x-transition>
                        <livewire:result.view.student-results />
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'settings'" x-transition>
                <livewire:result.term-settings-manager />
            </div>

            <div x-show="activeTab === 'history'" x-transition>
                <livewire:result.student-history />
            </div>

            <div x-show="activeTab === 'spreadsheet'" x-transition>
                <livewire:result.class-results-spreadsheet />
            </div>
            
            <div x-show="activeTab === 'awards'" x-transition>
                <livewire:result.awards-manager />
            </div>
            
            <div x-show="activeTab === 'analytics'" x-transition>
                <livewire:result.performance-analytics />
            </div>
        </div>
    </div>
</div>