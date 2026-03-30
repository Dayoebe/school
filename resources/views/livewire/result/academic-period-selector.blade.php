<div class="flex flex-col gap-2 text-sm">
    @php
        $academicYearName = $academicYears->firstWhere('id', $academicYearId)?->name;
        $semesterName = $semesters->firstWhere('id', $semesterId)?->name;
    @endphp

    @if($academicYearName && $semesterName)
        <div class="flex flex-wrap gap-2">
            <span class="rounded-full border border-white/30 bg-white/15 px-4 py-2 font-semibold text-white backdrop-blur">
                Session: {{ $academicYearName }}
            </span>
            <span class="rounded-full border border-white/30 bg-white/15 px-4 py-2 font-semibold text-white backdrop-blur">
                Term: {{ $semesterName }}
            </span>
        </div>
        <p class="text-xs text-indigo-100">Using the active academic session and term set by the admin.</p>
    @else
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
            Set the current academic session and term in Academic Years before managing results.
        </div>
    @endif
</div>
