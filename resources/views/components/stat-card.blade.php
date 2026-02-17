@props([
    'value' => '-',
    'label' => '',
    'color' => 'blue',
    'icon' => null,
])

@php
    $styles = [
        'blue' => 'bg-blue-50 text-blue-800 border-blue-200',
        'green' => 'bg-green-50 text-green-800 border-green-200',
        'yellow' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
        'purple' => 'bg-purple-50 text-purple-800 border-purple-200',
        'red' => 'bg-red-50 text-red-800 border-red-200',
        'gray' => 'bg-gray-50 text-gray-800 border-gray-200',
    ];
    $cardClass = $styles[$color] ?? $styles['blue'];
@endphp

<div {{ $attributes->merge(['class' => "border rounded-lg p-4 {$cardClass}"]) }}>
    <div class="flex items-start justify-between gap-3">
        <div>
            <div class="text-2xl font-bold leading-none">{{ $value }}</div>
            <div class="text-sm mt-2 opacity-90">{{ $label }}</div>
        </div>
        @if($icon)
            <i class="fas fa-{{ $icon }} text-lg opacity-80"></i>
        @endif
    </div>
</div>
