<?php

namespace App\Http\Controllers;

use App\Models\ExamPaper;
use App\Traits\ResolvesAccessibleStudents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExamPaperPortalController extends Controller
{
    use ResolvesAccessibleStudents;

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('view exam paper'), 403);

        $availableStudents = [];
        $selectedStudentId = '';

        if ($this->isParentStudentPortalViewer()) {
            $availableStudents = $this->portalAccessibleStudentsQuery()
                ->with(['studentRecord.myClass', 'studentRecord.section'])
                ->orderBy('name')
                ->get()
                ->map(function ($student): array {
                    $record = $student->studentRecord;

                    return [
                        'id' => (int) $student->id,
                        'name' => (string) $student->name,
                        'admission_number' => (string) ($record?->admission_number ?: 'N/A'),
                        'class_name' => (string) ($record?->myClass?->name ?? 'Not assigned'),
                        'section_name' => (string) ($record?->section?->name ?? 'Not assigned'),
                    ];
                })
                ->values()
                ->all();

            $selectedStudentId = (string) $request->query('student', $availableStudents[0]['id'] ?? '');
        }

        if ($this->isStudentStudentPortalViewer()) {
            $selectedStudentId = (string) Auth::id();
        }

        $targetStudentId = $this->resolveTargetStudentId($selectedStudentId, $availableStudents);
        $paperQuery = $this->papersForTargetStudent($targetStudentId);

        $papers = $paperQuery
            ->orderByDesc('published_at')
            ->paginate(10)
            ->withQueryString();

        $selectedPaper = null;
        $selectedPaperId = (int) $request->query('paper', 0);

        if ($selectedPaperId > 0 && $targetStudentId !== null) {
            $selectedPaper = $this->papersForTargetStudent($targetStudentId)
                ->whereKey($selectedPaperId)
                ->first();
        }

        return view('livewire.exams.pages.exam-paper.viewer', [
            'availableStudents' => $availableStudents,
            'selectedStudentId' => $selectedStudentId,
            'selectedStudentProfile' => collect($availableStudents)
                ->first(fn (array $student): bool => (int) $student['id'] === (int) $selectedStudentId),
            'papers' => $papers,
            'selectedPaper' => $selectedPaper,
            'isParentViewer' => $this->isParentStudentPortalViewer(),
        ]);
    }

    protected function papersForTargetStudent(?int $studentId): Builder
    {
        $query = ExamPaper::query()
            ->whereRaw('1 = 0');

        if ($studentId === null) {
            return $query;
        }

        return ExamPaper::query()
            ->published()
            ->forSchool(auth()->user()?->school_id)
            ->forCurrentSchoolAcademicPeriod(auth()->user())
            ->visibleToStudent($studentId)
            ->with([
                'exam.semester.academicYear',
                'myClass',
                'subject',
                'uploader',
                'publisher',
            ]);
    }

    protected function resolveTargetStudentId(string $selectedStudentId, array $availableStudents): ?int
    {
        if ($this->isStudentStudentPortalViewer()) {
            return Auth::id() ? (int) Auth::id() : null;
        }

        if (!$this->isParentStudentPortalViewer()) {
            return null;
        }

        $studentId = (int) $selectedStudentId;

        if ($studentId <= 0) {
            return null;
        }

        $isAccessible = collect($availableStudents)
            ->contains(fn (array $student): bool => (int) $student['id'] === $studentId);

        return $isAccessible ? $studentId : null;
    }
}
