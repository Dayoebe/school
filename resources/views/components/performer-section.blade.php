<div class="bg-white p-4 rounded-lg shadow border-l-4 border-{{ $color }}-500">
    <div class="flex items-center mb-3">
        <i class="fas fa-{{ $icon }} text-{{ $color }}-500 mr-2"></i>
        <h3 class="text-lg font-semibold">{{ $title }}</h3>
    </div>
    <ul class="space-y-2">
        @foreach ($reports as $report)
            <li class="flex justify-between items-center">
                <span class="font-medium">{{ $report['student']->user->name }}</span>
                <span class="bg-{{ $color }}-100 text-{{ $color }}-800 px-2 py-1 rounded-full text-xs font-bold">
                    {{ $report['average_percentage'] }}%
                </span>
            </li>
        @endforeach
    </ul>
</div>