<?php

namespace App\Livewire\Exams;

use App\Models\Exam;
use Livewire\Component;
use Illuminate\Support\Facades\Schema;

class ListExamsTable extends Component
{
    public function render()
    {
        $school = auth()->user()?->school;

        if ($school) {
            $school->loadMissing('semester.academicYear');
        }

        $semester = $school?->semester;
        $exams = collect();
        $nextExam = null;
        $latestExam = null;
        $featuredExam = null;
        $dateWindowStart = null;
        $dateWindowEnd = null;
        $paperCoverage = 0;
        $slotCoverage = 0;
        $paperUploadsEnabled = Schema::hasTable('exam_papers');
        $tableFilters = [['name' => 'whereRaw', 'arguments' => ['1 = 0']]];
        $stats = [
            'total_exams' => 0,
            'active_exams' => 0,
            'published_results' => 0,
            'exams_with_slots' => 0,
            'uploaded_papers' => 0,
            'exams_with_papers' => 0,
            'published_papers' => 0,
            'sealed_papers' => 0,
        ];

        if ($semester) {
            $tableFilters = [
                ['name' => 'withoutUploadArchives', 'arguments' => []],
                ['name' => 'where', 'arguments' => ['semester_id', $semester->id]],
            ];

            $exams = Exam::query()
                ->withoutUploadArchives()
                ->where('semester_id', $semester->id)
                ->withCount(['examSlots']);

            if ($paperUploadsEnabled) {
                $exams->withCount([
                    'papers',
                    'papers as published_papers_count' => fn ($query) => $query->whereNotNull('published_at'),
                    'papers as sealed_papers_count' => fn ($query) => $query->whereNotNull('sealed_at'),
                ]);
            }

            $exams = $exams
                ->orderBy('start_date')
                ->get();

            $stats = [
                'total_exams' => $exams->count(),
                'active_exams' => $exams->where('active', true)->count(),
                'published_results' => $exams->where('publish_result', true)->count(),
                'exams_with_slots' => $exams->filter(fn (Exam $exam) => $exam->exam_slots_count > 0)->count(),
                'uploaded_papers' => (int) $exams->sum('papers_count'),
                'exams_with_papers' => $exams->filter(fn (Exam $exam) => $exam->papers_count > 0)->count(),
                'published_papers' => (int) $exams->sum('published_papers_count'),
                'sealed_papers' => (int) $exams->sum('sealed_papers_count'),
            ];

            $nextExam = $exams
                ->filter(fn (Exam $exam) => $exam->stop_date?->isToday() || $exam->stop_date?->isFuture())
                ->sortBy('start_date')
                ->first();

            $latestExam = $exams
                ->sortByDesc('stop_date')
                ->first();

            $featuredExam = $nextExam ?: $latestExam;
            $dateWindowStart = $exams->min('start_date');
            $dateWindowEnd = $exams->max('stop_date');
            $paperCoverage = $stats['total_exams'] > 0
                ? (int) round(($stats['exams_with_papers'] / $stats['total_exams']) * 100)
                : 0;
            $slotCoverage = $stats['total_exams'] > 0
                ? (int) round(($stats['exams_with_slots'] / $stats['total_exams']) * 100)
                : 0;
        }

        return view('livewire.exams.list-exams-table', [
            'school' => $school,
            'semester' => $semester,
            'stats' => $stats,
            'paperUploadsEnabled' => $paperUploadsEnabled,
            'nextExam' => $nextExam,
            'latestExam' => $latestExam,
            'featuredExam' => $featuredExam,
            'dateWindowStart' => $dateWindowStart,
            'dateWindowEnd' => $dateWindowEnd,
            'paperCoverage' => $paperCoverage,
            'slotCoverage' => $slotCoverage,
            'tableFilters' => $tableFilters,
        ]);
    }
}
