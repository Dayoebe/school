<div class="space-y-6 exams-command-center">
    <style>
        .exams-command-center .exam-datatable-shell > div > .flex.flex-col.md\:flex-row.gap-4.items-center {
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.28);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(239, 246, 255, 1) 0%, rgba(236, 254, 255, 1) 100%);
            box-shadow: 0 14px 30px -24px rgba(15, 23, 42, 0.45);
        }

        .exams-command-center .exam-datatable-shell label {
            margin: 0;
            font-size: 0.85rem;
            font-weight: 700;
            color: #0f172a;
        }

        .exams-command-center .exam-datatable-shell input[type="search"],
        .exams-command-center .exam-datatable-shell select {
            border: 1px solid #cbd5e1;
            border-radius: 0.9rem;
            background: #ffffff;
            padding: 0.75rem 1rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }

        .exams-command-center .exam-datatable-shell input[type="search"]:focus,
        .exams-command-center .exam-datatable-shell select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .exams-command-center .exam-datatable-shell table {
            border: 0;
            margin-top: 1.25rem;
            overflow: hidden;
            border-radius: 1.25rem;
            border-collapse: separate;
            border-spacing: 0;
            box-shadow: 0 18px 35px -30px rgba(15, 23, 42, 0.5);
        }

        .exams-command-center .exam-datatable-shell thead {
            background: linear-gradient(90deg, #0f172a 0%, #1d4ed8 55%, #0891b2 100%);
        }

        .exams-command-center .exam-datatable-shell thead th {
            border: 0;
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .exams-command-center .exam-datatable-shell tbody tr {
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .exams-command-center .exam-datatable-shell tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .exams-command-center .exam-datatable-shell tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .exams-command-center .exam-datatable-shell tbody tr:hover {
            background: #e0f2fe;
        }

        .exams-command-center .exam-datatable-shell tbody td,
        .exams-command-center .exam-datatable-shell tbody th {
            border-color: rgba(226, 232, 240, 0.9);
            vertical-align: middle;
        }
    </style>

    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-sky-600 via-blue-700 to-slate-900 px-6 py-8 text-white shadow-2xl md:px-8">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -right-14 top-0 h-44 w-44 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute bottom-0 left-0 h-40 w-40 rounded-full bg-cyan-300/10 blur-3xl"></div>
            <div class="absolute right-1/3 top-1/2 h-24 w-24 rounded-full border border-white/10"></div>
        </div>

        <div class="relative flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.3em] text-sky-100 backdrop-blur-sm">
                    <i class="fas fa-file-signature"></i>
                    Exam Command Center
                </span>
                <h2 class="mt-4 text-3xl font-black tracking-tight md:text-4xl">
                    Manage exams for {{ $semester?->name ?? 'the active term' }}
                </h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-sky-50/90 md:text-base">
                    Configure term assessments, upload printable exam papers, manage slots, and track result readiness from one colorful workspace.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    @can('create exam paper')
                        <a href="{{ route('exams.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-100">
                            <i class="fas fa-upload text-emerald-600"></i>
                            Upload exam
                        </a>
                    @endcan

                    @can('create exam')
                        <a href="{{ route('exams.setup.create') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur-sm transition hover:-translate-y-0.5 hover:bg-white/20">
                            <i class="fas fa-sliders text-cyan-200"></i>
                            Exam setup
                        </a>
                    @endcan

                    @can('read exam record')
                        <a href="{{ route('exam-records.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur-sm transition hover:-translate-y-0.5 hover:bg-white/20">
                            <i class="fas fa-clipboard-list text-cyan-200"></i>
                            Exam records
                        </a>
                    @endcan

                    @can('check result')
                        <a href="{{ route('exams.result-checker') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur-sm transition hover:-translate-y-0.5 hover:bg-white/20">
                            <i class="fas fa-chart-column text-emerald-200"></i>
                            Result checker
                        </a>
                    @endcan

                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:w-[24rem]">
                <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-100/80">School</p>
                    <p class="mt-3 text-lg font-bold text-white">{{ $school?->name ?? 'School profile' }}</p>
                    <p class="mt-1 text-xs text-sky-100/80">{{ $semester?->academicYear?->name ?? 'Academic year not set' }}</p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-100/80">Window</p>
                    <p class="mt-3 text-lg font-bold text-white">
                        {{ $dateWindowStart?->format('d M') ?? '--' }}
                        <span class="text-sky-200">to</span>
                        {{ $dateWindowEnd?->format('d M') ?? '--' }}
                    </p>
                    <p class="mt-1 text-xs text-sky-100/80">Current term exam timeline</p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-100/80">Papers Ready</p>
                    <p class="mt-3 text-2xl font-black text-white">{{ $paperCoverage }}%</p>
                    <p class="mt-1 text-xs text-sky-100/80">{{ number_format($stats['exams_with_papers']) }} of {{ number_format($stats['total_exams']) }} exams have uploads</p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-100/80">Slots Ready</p>
                    <p class="mt-3 text-2xl font-black text-white">{{ $slotCoverage }}%</p>
                    <p class="mt-1 text-xs text-sky-100/80">{{ number_format($stats['exams_with_slots']) }} exams already configured</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-sky-100 bg-white p-5 shadow-lg shadow-sky-100/60">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-600">Total exams</p>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['total_exams']) }}</p>
                    <p class="mt-2 text-sm text-slate-500">Planned for {{ $semester?->name ?? 'this term' }}</p>
                </div>
                <div class="rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 p-3 text-white shadow-lg">
                    <i class="fas fa-layer-group text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-lg shadow-emerald-100/60">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-600">Live status</p>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['active_exams']) }}</p>
                    <p class="mt-2 text-sm text-slate-500">Active exams right now</p>
                </div>
                <div class="rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-3 text-white shadow-lg">
                    <i class="fas fa-play-circle text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-amber-100 bg-white p-5 shadow-lg shadow-amber-100/60">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">Result publishing</p>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['published_results']) }}</p>
                    <p class="mt-2 text-sm text-slate-500">Exams with published results</p>
                </div>
                <div class="rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-3 text-white shadow-lg">
                    <i class="fas fa-bullhorn text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-violet-100 bg-white p-5 shadow-lg shadow-violet-100/60">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-violet-600">Exam uploads</p>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['uploaded_papers']) }}</p>
                    <p class="mt-2 text-sm text-slate-500">Typed papers and file uploads</p>
                </div>
                <div class="rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-600 p-3 text-white shadow-lg">
                    <i class="fas fa-file-arrow-up text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl xl:col-span-2">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-600">Term readiness</p>
                    <h3 class="mt-2 text-2xl font-black text-slate-900">Exam operations overview</h3>
                    <p class="mt-2 text-sm text-slate-500">Track configuration, uploads, and publishing before the term closes.</p>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600">
                    <i class="fas fa-calendar-days text-sky-600"></i>
                    {{ $semester?->name ?? 'No active term' }}
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-gradient-to-br from-slate-50 to-slate-100 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Next on deck</p>
                    <p class="mt-3 text-lg font-bold text-slate-900">{{ $nextExam?->name ?? 'No upcoming exam' }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $nextExam?->start_date?->format('D, d M Y') ?? 'Create an exam to build the next schedule.' }}</p>
                </div>
                <div class="rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Published papers</p>
                    <p class="mt-3 text-lg font-bold text-slate-900">{{ number_format($stats['published_papers']) }}</p>
                    <p class="mt-1 text-sm text-slate-500">Visible to students and parents in the portal.</p>
                </div>
                <div class="rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">Sealed papers</p>
                    <p class="mt-3 text-lg font-bold text-slate-900">{{ number_format($stats['sealed_papers']) }}</p>
                    <p class="mt-1 text-sm text-slate-500">Locked down by principal or super admin.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-sky-100 bg-sky-50/70 p-5">
                    <div class="flex items-center justify-between text-sm font-semibold text-slate-700">
                        <span>Slot configuration coverage</span>
                        <span>{{ $slotCoverage }}%</span>
                    </div>
                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-white">
                        <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-blue-600" style="width: {{ $slotCoverage }}%"></div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">{{ number_format($stats['exams_with_slots']) }} exam(s) already have slots configured.</p>
                </div>
                <div class="rounded-2xl border border-violet-100 bg-violet-50/70 p-5">
                    <div class="flex items-center justify-between text-sm font-semibold text-slate-700">
                        <span>Upload coverage</span>
                        <span>{{ $paperCoverage }}%</span>
                    </div>
                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-white">
                        <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-fuchsia-600" style="width: {{ $paperCoverage }}%"></div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">{{ number_format($stats['exams_with_papers']) }} exam(s) already have uploaded papers.</p>
                </div>
            </div>
        </div>

        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-600">Workflow</p>
            <h3 class="mt-2 text-2xl font-black text-slate-900">What to do next</h3>
            <div class="mt-6 space-y-4">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-900">1. Upload the current term paper</p>
                    <p class="mt-1 text-sm text-slate-500">Teachers can upload their own subject exam, while principal and super admin can upload any class subject for the active term.</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-900">2. Use exam setup only when needed</p>
                    <p class="mt-1 text-sm text-slate-500">Create or adjust exam schedules and slots only for result processing or timetable control.</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-900">3. Keep records printable</p>
                    <p class="mt-1 text-sm text-slate-500">Use typed content, image upload, PDF, or both so the school keeps a printable exam record.</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-900">4. Publish only when ready</p>
                    <p class="mt-1 text-sm text-slate-500">Release results or papers only after verification and sealing where required.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="exam-register" class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl">
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-sky-700 px-6 py-5 text-white md:px-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-200">Term register</p>
                    <h3 class="mt-2 text-2xl font-black">Exam list for {{ $semester?->name ?? 'the current term' }}</h3>
                    <p class="mt-2 text-sm text-slate-200">Every exam in one place, with direct actions for management, slots, uploads, and publishing.</p>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm">
                    <i class="fas fa-list-check text-cyan-200"></i>
                    {{ number_format($stats['total_exams']) }} exam{{ $stats['total_exams'] === 1 ? '' : 's' }} this term
                </div>
            </div>
        </div>

        <div class="p-6 md:p-8 exam-datatable-shell">
            @if ($semester)
                <livewire:common.datatable
                    uniqueId="list-exams-table"
                    :model="App\Models\Exam::class"
                    :filters="$tableFilters"
                    :columns="[
                        ['property' => 'name'],
                        ['property' => 'start_date'],
                        ['property' => 'stop_date'],
                        ['property' => 'active', 'type' => 'boolean-switch', 'action' => 'exams.set-active-status', 'field' => 'status', 'true-statement' => 'Active', 'false-statement' => 'Inactive',  'can' => 'update exam'],
                        ['property' => 'publish_result','type' => 'boolean-switch', 'action' => 'exams.set-publish-result-status', 'field' => 'status', 'true-statement' => 'Published', 'false-statement' => 'Not published',  'can' => 'update exam'],
                        ['name' => 'Actions', 'type' => 'dropdown' , 'links' => [
                            ['href' => 'exams.edit', 'text' => 'Manage exam setup', 'icon' => 'fas fa-cog', 'can' => 'update exam'],
                            ['href' => 'exam-slots.index', 'text' => 'Manage/View exam slots', 'icon' => 'fas fa-calendar-alt',  'can' => 'read exam slot'],
                            ['href' => 'exam-papers.create', 'text' => 'Upload exam', 'icon' => 'fas fa-upload', 'can' => 'create exam paper'],
                            ['href' => 'exam-papers.index', 'text' => 'Manage uploaded exams', 'icon' => 'fas fa-file-lines', 'can' => 'read exam paper'],
                            ['href' => 'exam-slots.create', 'text' => 'Create exam slots', 'icon' => 'fas fa-key',  'can' => 'create exam slot'],
                        ]],
                        ['type' => 'delete', 'name' => 'Delete', 'action' => 'exams.destroy', 'can' => 'delete exam']
                    ]"
                />
            @else
                <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-sky-600 shadow-sm">
                        <i class="fas fa-calendar-xmark text-2xl"></i>
                    </div>
                    <h4 class="mt-4 text-xl font-bold text-slate-900">No active term selected</h4>
                    <p class="mt-2 text-sm text-slate-500">Set the current term before creating and managing exams for the school.</p>
                </div>
            @endif
        </div>
    </div>
</div>
