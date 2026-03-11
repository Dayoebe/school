<div class="flex flex-col gap-3 text-sm md:flex-row">
    <div>
        <select 
            wire:model.live="academicYearId"
            class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 font-medium text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
            <option value="">Select Year</option>
            @foreach ($academicYears as $year)
                <option value="{{ $year->id }}">{{ $year->name }}</option>
            @endforeach
        </select>
    </div>
    
    <div>
        <select 
            wire:model.live="semesterId"
            class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 font-medium text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
            @if(!$academicYearId) disabled @endif>
            <option value="">Select Term</option>
            @foreach ($semesters as $semester)
                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
            @endforeach
        </select>
    </div>
</div>
