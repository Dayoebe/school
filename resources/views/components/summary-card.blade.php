@props(['icon', 'title', 'value', 'color' => 'blue'])

@php
    $colors = [
        'blue' => ['bg' => 'from-blue-50 to-blue-100', 'icon' => 'bg-blue-100 text-blue-600'],
        'green' => ['bg' => 'from-green-50 to-green-100', 'icon' => 'bg-green-100 text-green-600'],
        'red' => ['bg' => 'from-red-50 to-red-100', 'icon' => 'bg-red-100 text-red-600'],
        'yellow' => ['bg' => 'from-yellow-50 to-yellow-100', 'icon' => 'bg-yellow-100 text-yellow-600'],
        'purple' => ['bg' => 'from-purple-50 to-purple-100', 'icon' => 'bg-purple-100 text-purple-600'],
    ];
@endphp

<div class="bg-gradient-to-r {{ $colors[$color]['bg'] }} rounded-lg p-4 border border-{{ $color }}-200 shadow-sm">
    <div class="flex items-center">
        <div class="p-3 rounded-full {{ $colors[$color]['icon'] }} mr-3">
            <i class="fas fa-{{ $icon }}"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-{{ $color }}-800">{{ $title }}</h3>
            <p class="text-gray-700">{{ $value }}</p>
        </div>
    </div>
</div>