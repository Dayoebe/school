@extends('layouts.app', [
    'breadcrumbs' => [
        ['href' => route('dashboard'), 'text' => 'Dashboard'],
        ['href' => route('result'), 'text' => 'Results', 'active' => true],
    ]
])

@section('title', __('Results Management'))

@section('page_heading', __('Results Management System'))

@section('content')
<div x-data="{ activeTab: 'dashboard' }" class="space-y-6">
    
    <!-- Header with Navigation Tabs -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-xl p-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <div class="text-white">
                <h1 class="text-3xl font-bold flex items-center">
                    <i class="fas fa-chart-line mr-3 text-yellow-300"></i>
                    Results Management
                </h1>
                <p class="text-blue-100 mt-2">Comprehensive student performance tracking</p>
            </div>
            
            <!-- Academic Period Selector -->
            <div class="mt-4 md:mt-0 bg-white/10 backdrop-blur-sm rounded-xl px-4 py-2">
                @livewire('result.academic-period-selector')
            </div>
        </div>
        
        <!-- Navigation Tabs -->
        <div class="flex flex-wrap gap-2">
            <button 
                @click="activeTab = 'dashboard'"
                :class="activeTab === 'dashboard' ? 'bg-white text-blue-600' : 'bg-white/20 text-white hover:bg-white/30'"
                class="px-4 py-2 rounded-lg font-medium transition-all duration-300 flex items-center">
                <i class="fas fa-th-large mr-2"></i> Dashboard
            </button>
            
            <button 
                @click="activeTab = 'upload'"
                :class="activeTab === 'upload' ? 'bg-white text-blue-600' : 'bg-white/20 text-white hover:bg-white/30'"
                class="px-4 py-2 rounded-lg font-medium transition-all duration-300 flex items-center">
                <i class="fas fa-upload mr-2"></i> Upload Results
            </button>
            
            <button 
                @click="activeTab = 'view'"
                :class="activeTab === 'view' ? 'bg-white text-blue-600' : 'bg-white/20 text-white hover:bg-white/30'"
                class="px-4 py-2 rounded-lg font-medium transition-all duration-300 flex items-center">
                <i class="fas fa-eye mr-2"></i> View Results
            </button>
            
            <button 
                @click="activeTab = 'history'"
                :class="activeTab === 'history' ? 'bg-white text-blue-600' : 'bg-white/20 text-white hover:bg-white/30'"
                class="px-4 py-2 rounded-lg font-medium transition-all duration-300 flex items-center">
                <i class="fas fa-history mr-2"></i> Student History
            </button>
        </div>
    </div>
    
    <!-- Content Area -->
    <div class="animate-fade-in">
        <!-- Dashboard -->
        <div x-show="activeTab === 'dashboard'" x-transition>
            @livewire('result.dashboard')
        </div>
        
        <!-- Upload Results -->
        <div x-show="activeTab === 'upload'" x-transition>
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div x-data="{ uploadMode: 'individual' }">
                    <div class="flex gap-4 mb-6 border-b pb-4">
                        <button 
                            @click="uploadMode = 'individual'"
                            :class="uploadMode === 'individual' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center">
                            <i class="fas fa-user-edit mr-2"></i> Individual Upload
                        </button>
                        <button 
                            @click="uploadMode = 'bulk'"
                            :class="uploadMode === 'bulk' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center">
                            <i class="fas fa-users mr-2"></i> Bulk Upload
                        </button>
                    </div>
                    
                    <div x-show="uploadMode === 'individual'" x-transition>
                        @livewire('result.upload.individual-upload')
                    </div>
                    
                    <div x-show="uploadMode === 'bulk'" x-transition>
                        @livewire('result.upload.bulk-upload')
                    </div>
                </div>
            </div>
        </div>
        
        <!-- View Results -->
        <div x-show="activeTab === 'view'" x-transition>
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div x-data="{ viewMode: 'student' }">
                    <div class="flex gap-4 mb-6 border-b pb-4">
                        <button 
                            @click="viewMode = 'student'"
                            :class="viewMode === 'student' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center">
                            <i class="fas fa-user mr-2"></i> By Student
                        </button>
                        <button 
                            @click="viewMode = 'class'"
                            :class="viewMode === 'class' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center">
                            <i class="fas fa-users mr-2"></i> By Class
                        </button>
                        <button 
                            @click="viewMode = 'subject'"
                            :class="viewMode === 'subject' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center">
                            <i class="fas fa-book mr-2"></i> By Subject
                        </button>
                    </div>
                    
                    <div x-show="viewMode === 'student'" x-transition>
                        @livewire('result.view.student-results')
                    </div>
                    
                    <div x-show="viewMode === 'class'" x-transition>
                        @livewire('result.view.class-results')
                    </div>
                    
                    <div x-show="viewMode === 'subject'" x-transition>
                        @livewire('result.view.subject-results')
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Student History -->
        <div x-show="activeTab === 'history'" x-transition>
            @livewire('result.student-history')
        </div>
    </div>
</div>

@push('styles')
<style>
    .animate-fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush
@endsection