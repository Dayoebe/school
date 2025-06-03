@props(['title', 'icon', 'color' => 'blue', 'reports' => []])

@php
    $colors = [
        'blue' => ['text' => 'text-blue-700', 'bg' => 'bg-blue-50', 'icon' => 'text-blue-600'],
        'green' => ['text' => 'text-green-700', 'bg' => 'bg-green-50', 'icon' => 'text-green-600'],
        'red' => ['text' => 'text-red-700', 'bg' => 'bg-red-50', 'icon' => 'text-red-600'],
        'yellow' => ['text' => 'text-yellow-700', 'bg' => 'bg-yellow-50', 'icon' => 'text-yellow-600'],
        'purple' => ['text' => 'text-purple-700', 'bg' => 'bg-purple-50', 'icon' => 'text-purple-600'],
    ];
@endphp

<div class="bg-white p-4 rounded-lg shadow">
    <h4 class="text-lg font-medium {{ $colors[$color]['text'] }} mb-3 flex items-center">
        <i class="fas fa-{{ $icon }} mr-2 {{ $colors[$color]['icon'] }}"></i>
        {{ $title }}
    </h4>
    <div class="space-y-3">
        @foreach($reports as $report)
        <div class="flex items-center justify-between p-3 {{ $colors[$color]['bg'] }} rounded-lg">
            <div class="flex items-center">
                <div class="{{ $colors[$color]['bg'] }} text-{{ $color }}-800 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                    {{ $report['rank'] }}
                </div>
                <div>
                    <div class="font-medium text-gray-900">{{ $report['student']->user->name }}</div>
                    <div class="text-sm text-gray-500">{{ $report['student']->admission_number }}</div>
                </div>
            </div>
            <div class="text-lg font-bold {{ $colors[$color]['text'] }}">
                {{ $report['average_percentage'] }}%
            </div>
        </div>
        @endforeach
    </div>
</div>