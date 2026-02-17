<div class="flex flex-col md:flex-row gap-3 text-sm">
    <div>
        <select 
            wire:model.live="academicYearId"
            class="border-0 bg-white/90 text-gray-800 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 font-medium">
            <option value="">Select Year</option>
            @foreach ($academicYears as $year)
                <option value="{{ $year->id }}">{{ $year->name }}</option>
            @endforeach
        </select>
    </div>
    
    <div>
        <select 
            wire:model.live="semesterId"
            class="border-0 bg-white/90 text-gray-800 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 font-medium"
            @if(!$academicYearId) disabled @endif>
            <option value="">Select Term</option>
            @foreach ($semesters as $semester)
                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
            @endforeach
        </select>
    </div>
</div>