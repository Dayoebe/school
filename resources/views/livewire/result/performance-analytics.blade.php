<!-- Load Chart.js first -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Define the Alpine component globally BEFORE Alpine initializes
window.analyticsCharts = function() {
    return {
        charts: {},
        currentViewMode: @json($viewMode),
        currentStudentId: @json($selectedStudentId),
        currentClassId: @json($selectedClassId),
        
        init() {
            // Initial chart load
            this.$nextTick(() => {
                this.initCharts();
            });
            
            // Listen for Livewire updates using wire:snapshot
            window.addEventListener('livewire:update', () => {
                setTimeout(() => {
                    this.currentViewMode = @json($viewMode);
                    this.currentStudentId = @json($selectedStudentId);
                    this.currentClassId = @json($selectedClassId);
                    this.initCharts();
                }, 300);
            });
        },
        
        destroyAllCharts() {
            Object.values(this.charts).forEach(chart => {
                if (chart) chart.destroy();
            });
            this.charts = {};
        },
        
        initCharts() {
            this.destroyAllCharts();
            
            const viewMode = this.currentViewMode;
            const hasData = @json($academicYearId) && 
                           (this.currentStudentId || this.currentClassId);
            
            if (!hasData) return;
            
            if (viewMode === 'student' && this.currentStudentId) {
                this.initStudentCharts();
            } else if (viewMode === 'class' && this.currentClassId) {
                this.initClassCharts();
            } else if (viewMode === 'subject' && this.currentClassId) {
                this.initSubjectCharts();
            }
        },
        
        initStudentCharts() {
            const trendData = @json($trendData);
            const radarData = @json($radarData);
            const comparisonData = @json($comparisonData);
            
            // Trend Chart
            const trendCanvas = document.getElementById('trendChart');
            if (trendCanvas && trendData.labels && trendData.labels.length > 0) {
                this.charts.trend = new Chart(trendCanvas, {
                    type: 'line',
                    data: trendData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { 
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Radar Chart
            const radarCanvas = document.getElementById('radarChart');
            if (radarCanvas && radarData.labels && radarData.labels.length > 0) {
                this.charts.radar = new Chart(radarCanvas, {
                    type: 'radar',
                    data: radarData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            r: { 
                                beginAtZero: true, 
                                max: 100,
                                ticks: {
                                    stepSize: 20
                                }
                            }
                        }
                    }
                });
            }

            // Comparison Chart
            const compCanvas = document.getElementById('comparisonChart');
            if (compCanvas && comparisonData.labels && comparisonData.labels.length > 0) {
                this.charts.comparison = new Chart(compCanvas, {
                    type: 'bar',
                    data: comparisonData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        },
        
        initClassCharts() {
            const distributionData = @json($performanceDistribution);
            
            // Distribution Chart
            const distCanvas = document.getElementById('distributionChart');
            if (distCanvas && distributionData.labels && distributionData.labels.length > 0) {
                this.charts.distribution = new Chart(distCanvas, {
                    type: 'bar',
                    data: distributionData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        },
        
        initSubjectCharts() {
            const subjectAnalysis = @json($subjectAnalysis);
            
            subjectAnalysis.forEach((analysis, index) => {
                const canvas = document.getElementById('subjectTrend' + index);
                
                if (canvas && analysis.trend.labels.length > 0) {
                    this.charts['subject' + index] = new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: analysis.trend.labels,
                            datasets: [{
                                label: 'Average Score',
                                data: analysis.trend.data,
                                borderColor: 'rgb(168, 85, 247)',
                                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { 
                                    beginAtZero: true, 
                                    max: 100,
                                    ticks: {
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        }
    }
}
</script>

<div class="space-y-6" 
     x-data="analyticsCharts()" 
     wire:key="analytics-{{ $viewMode }}-{{ $selectedStudentId }}-{{ $selectedClassId }}">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 via-pink-600 to-red-600 rounded-2xl shadow-xl p-6">
        <h2 class="text-2xl font-bold text-white mb-2 flex items-center">
            <i class="fas fa-chart-line mr-3"></i>
            Performance Analytics Dashboard
        </h2>
        <p class="text-purple-100">Comprehensive insights into student performance</p>
    </div>

    <!-- Controls -->
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">View Mode</label>
                <select wire:model.live="viewMode"
                    class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-purple-500">
                    <option value="class">Class Analytics</option>
                    <option value="student">Student Analytics</option>
                    <option value="subject">Subject Analytics</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                <select wire:model.live="selectedClassId"
                    class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-purple-500">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($viewMode === 'student')
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                    <select wire:model.live="selectedStudentId"
                        class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-purple-500"
                        @if(!$selectedClassId) disabled @endif>
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->user->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>

    @if(!$academicYearId)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
            <i class="fas fa-info-circle text-yellow-600 text-3xl mb-3"></i>
            <p class="text-yellow-800 font-medium">Please select an academic period to view analytics</p>
        </div>
    @elseif($viewMode === 'student' && $selectedStudentId)
        <!-- Student Analytics View -->
        <div class="space-y-6">
            <!-- Insights -->
            @if(!empty($insights))
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($insights as $insight)
                        <div class="border-l-4 rounded-lg p-4 
                            {{ $insight['type'] === 'success' ? 'bg-green-50 border-green-500' : '' }}
                            {{ $insight['type'] === 'info' ? 'bg-blue-50 border-blue-500' : '' }}
                            {{ $insight['type'] === 'warning' ? 'bg-yellow-50 border-yellow-500' : '' }}
                            {{ $insight['type'] === 'danger' ? 'bg-red-50 border-red-500' : '' }}">
                            <div class="flex items-start">
                                <i class="fas {{ $insight['icon'] }} text-2xl mr-3
                                    {{ $insight['type'] === 'success' ? 'text-green-600' : '' }}
                                    {{ $insight['type'] === 'info' ? 'text-blue-600' : '' }}
                                    {{ $insight['type'] === 'warning' ? 'text-yellow-600' : '' }}
                                    {{ $insight['type'] === 'danger' ? 'text-red-600' : '' }}"></i>
                                <div>
                                    <h4 class="font-bold text-sm text-gray-800">{{ $insight['title'] }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $insight['message'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Trend Chart -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                        Performance Trend
                    </h3>
                    <div style="position: relative; height: 250px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Radar Chart -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-bullseye text-purple-600 mr-2"></i>
                        Subject Strengths
                    </h3>
                    <div style="position: relative; height: 250px;">
                        <canvas id="radarChart"></canvas>
                    </div>
                </div>

                <!-- Comparison Chart -->
                <div class="bg-white rounded-2xl shadow-lg p-6 lg:col-span-2">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-balance-scale text-green-600 mr-2"></i>
                        Student vs Class Average
                    </h3>
                    <div style="position: relative; height: 150px;">
                        <canvas id="comparisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    @elseif($viewMode === 'class' && $selectedClassId)
        <!-- Class Analytics View -->
        <div class="space-y-6">
            <!-- Insights -->
            @if(!empty($insights))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($insights as $insight)
                        <div class="border-l-4 rounded-lg p-4 
                            {{ $insight['type'] === 'success' ? 'bg-green-50 border-green-500' : '' }}
                            {{ $insight['type'] === 'info' ? 'bg-blue-50 border-blue-500' : '' }}
                            {{ $insight['type'] === 'warning' ? 'bg-yellow-50 border-yellow-500' : '' }}
                            {{ $insight['type'] === 'danger' ? 'bg-red-50 border-red-500' : '' }}">
                            <div class="flex items-start">
                                <i class="fas {{ $insight['icon'] }} text-3xl mr-3
                                    {{ $insight['type'] === 'success' ? 'text-green-600' : '' }}
                                    {{ $insight['type'] === 'info' ? 'text-blue-600' : '' }}
                                    {{ $insight['type'] === 'warning' ? 'text-yellow-600' : '' }}
                                    {{ $insight['type'] === 'danger' ? 'text-red-600' : '' }}"></i>
                                <div>
                                    <h4 class="font-bold text-lg text-gray-800">{{ $insight['title'] }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $insight['message'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Performance Distribution -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                        Performance Distribution
                    </h3>
                    <div style="position: relative; height: 250px;">
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>

                <!-- Subject Performance -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-chart-pie text-purple-600 mr-2"></i>
                        Subject Performance Overview
                    </h3>
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        @foreach($subjectAnalysis as $analysis)
                            <div class="border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="font-bold text-gray-800">{{ $analysis['subject']->name }}</h4>
                                    <span class="text-lg font-bold 
                                        {{ $analysis['average'] >= 75 ? 'text-green-600' : '' }}
                                        {{ $analysis['average'] >= 60 && $analysis['average'] < 75 ? 'text-blue-600' : '' }}
                                        {{ $analysis['average'] >= 50 && $analysis['average'] < 60 ? 'text-yellow-600' : '' }}
                                        {{ $analysis['average'] < 50 ? 'text-red-600' : '' }}">
                                        {{ $analysis['average'] }}%
                                    </span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <span class="text-gray-500">Highest:</span>
                                        <span class="font-semibold text-green-600">{{ $analysis['highest'] }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Lowest:</span>
                                        <span class="font-semibold text-red-600">{{ $analysis['lowest'] }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Pass Rate:</span>
                                        <span class="font-semibold text-blue-600">{{ $analysis['pass_rate'] }}%</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- At-Risk Students -->
            @if(!empty($atRiskStudents))
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-red-600 to-orange-600 px-6 py-4">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            At-Risk Students ({{ count($atRiskStudents) }})
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($atRiskStudents as $student)
                                <div class="border-2 rounded-xl p-4
                                    {{ $student['risk_level'] === 'critical' ? 'border-red-500 bg-red-50' : '' }}
                                    {{ $student['risk_level'] === 'high' ? 'border-orange-500 bg-orange-50' : '' }}
                                    {{ $student['risk_level'] === 'moderate' ? 'border-yellow-500 bg-yellow-50' : '' }}">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center">
                                            <img class="h-10 w-10 rounded-full object-cover mr-3"
                                                src="{{ $student['student']->user->profile_photo_url }}"
                                                alt="{{ $student['student']->user->name }}">
                                            <div>
                                                <h4 class="font-bold text-sm text-gray-900">{{ $student['student']->user->name }}</h4>
                                                <p class="text-xs text-gray-500">{{ $student['student']->myClass->name }}</p>
                                            </div>
                                        </div>
                                        <span class="px-2 py-1 text-xs font-bold rounded-full uppercase
                                            {{ $student['risk_level'] === 'critical' ? 'bg-red-200 text-red-800' : '' }}
                                            {{ $student['risk_level'] === 'high' ? 'bg-orange-200 text-orange-800' : '' }}
                                            {{ $student['risk_level'] === 'moderate' ? 'bg-yellow-200 text-yellow-800' : '' }}">
                                            {{ $student['risk_level'] }}
                                        </span>
                                    </div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Average:</span>
                                            <span class="font-bold text-red-600">{{ $student['average'] }}%</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Failing Subjects:</span>
                                            <span class="font-bold text-orange-600">{{ $student['failing_subjects'] }}/{{ $student['total_subjects'] }}</span>
                                        </div>
                                    </div>
                                    <button wire:click="$set('selectedStudentId', {{ $student['student']->id }}); $set('viewMode', 'student')"
                                        class="w-full mt-3 bg-white hover:bg-gray-50 text-gray-800 font-medium py-2 px-3 rounded-lg border border-gray-300 transition-colors text-xs">
                                        View Details
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

    @elseif($viewMode === 'subject' && $selectedClassId)
        <!-- Subject Analytics View -->
        <div class="space-y-6">
            @foreach($subjectAnalysis as $index => $analysis)
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">{{ $analysis['subject']->name }}</h3>
                    <div style="position: relative; height: 150px;">
                        <canvas id="subjectTrend{{ $index }}"></canvas>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>