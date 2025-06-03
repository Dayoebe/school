@props(['value', 'label', 'color' => 'blue'])

@php
    $colors = [
        'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700'],
        'green' => ['bg' => 'bg-green-50', 'text' => 'text-green-700'],
        'yellow' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700'],
        'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700'],
    ];
@endphp

<div class="{{ $colors[$color]['bg'] }} p-4 rounded-lg text-center">
    <div class="text-3xl font-bold {{ $colors[$color]['text'] }} mb-1">
        {{ $value }}
    </div>
    <div class="text-sm text-gray-600">{{ $label }}</div>
</div>