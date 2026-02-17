@props([
    'title' => 'Performers',
    'icon' => 'users',
    'color' => 'blue',
    'reports' => [],
])

@php
    $palette = [
        'green' => ['header' => 'text-green-700', 'badge' => 'bg-green-100 text-green-700'],
        'red' => ['header' => 'text-red-700', 'badge' => 'bg-red-100 text-red-700'],
        'blue' => ['header' => 'text-blue-700', 'badge' => 'bg-blue-100 text-blue-700'],
        'purple' => ['header' => 'text-purple-700', 'badge' => 'bg-purple-100 text-purple-700'],
        'yellow' => ['header' => 'text-yellow-700', 'badge' => 'bg-yellow-100 text-yellow-700'],
    ];
    $theme = $palette[$color] ?? $palette['blue'];
@endphp

<div class="bg-white p-5 rounded-lg shadow">
    <h4 class="font-semibold mb-4 {{ $theme['header'] }}">
        <i class="fas fa-{{ $icon }} mr-2"></i>{{ $title }}
    </h4>

    <div class="space-y-2">
        @forelse($reports as $index => $report)
            @php
                $name = $report['student']['user']['name'] ?? ($report['student']->user->name ?? 'Unknown');
                $percentage = $report['average_percentage'] ?? 0;
            @endphp
            <div class="flex items-center justify-between p-3 rounded border border-gray-100">
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2 py-1 rounded {{ $theme['badge'] }}">{{ $index + 1 }}</span>
                    <span class="text-sm font-medium text-gray-800">{{ $name }}</span>
                </div>
                <span class="text-sm font-semibold text-gray-700">{{ number_format((float) $percentage, 1) }}%</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">No records found.</p>
        @endforelse
    </div>
</div>
