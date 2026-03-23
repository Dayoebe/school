<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExamPaperRequest;
use App\Http\Requests\UpdateExamPaperRequest;
use App\Models\Exam;
use App\Models\ExamPaper;
use App\Models\Semester;
use App\Models\Subject;
use App\Traits\ResolvesAccessibleStudents;
use App\Traits\RestrictsTeacherExamPaperManagement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExamPaperController extends Controller
{
    use ResolvesAccessibleStudents;
    use RestrictsTeacherExamPaperManagement;

    public function index(Exam $exam): View
    {
        $this->authorize('viewAny', ExamPaper::class);
        $this->ensureExamInCurrentSchool($exam);

        $papers = $this->examPapersForManagement($exam)
            ->with(['myClass', 'subject', 'uploader', 'publisher', 'sealer'])
            ->get()
            ->sortBy(fn (ExamPaper $paper) => sprintf(
                '%s|%s|%s',
                strtolower((string) ($paper->myClass?->name ?? '')),
                strtolower((string) ($paper->subject?->name ?? '')),
                strtolower((string) $paper->title)
            ))
            ->values();

        return view('livewire.exams.pages.exam-paper.index', compact('exam', 'papers'));
    }

    public function create(Exam $exam): View
    {
        $this->authorize('create', ExamPaper::class);
        $this->ensureExamInCurrentSchool($exam);

        [$classes, $subjects] = $this->formOptions();

        return view('livewire.exams.pages.exam-paper.create', [
            'exam' => $exam,
            'classes' => $classes,
            'subjects' => $subjects,
        ]);
    }

    public function createForCurrentTerm(): View|RedirectResponse
    {
        $this->authorize('create', ExamPaper::class);

        $semester = $this->currentSchoolSemester();

        if (!$semester) {
            return redirect()
                ->route('exams.index')
                ->with('danger', 'Set the active academic session and term before uploading an exam.');
        }

        $exam = $this->resolveCurrentTermUploadExam($semester, true);
        [$classes, $subjects] = $this->formOptions();

        return view('livewire.exams.pages.create', [
            'exam' => $exam,
            'classes' => $classes,
            'subjects' => $subjects,
            'semester' => $semester,
        ]);
    }

    public function store(StoreExamPaperRequest $request, Exam $exam): RedirectResponse
    {
        $this->authorize('create', ExamPaper::class);
        $this->ensureExamInCurrentSchool($exam);

        $paper = $this->persistExamPaper($request, $exam);

        return redirect()
            ->route('exam-papers.index', $paper->exam_id)
            ->with('success', 'Exam paper uploaded successfully.');
    }

    public function storeForCurrentTerm(StoreExamPaperRequest $request): RedirectResponse
    {
        $this->authorize('create', ExamPaper::class);

        $semester = $this->currentSchoolSemester();

        if (!$semester) {
            return redirect()
                ->route('exams.index')
                ->with('danger', 'Set the active academic session and term before uploading an exam.');
        }

        $exam = $this->resolveCurrentTermUploadExam($semester, true);
        $this->persistExamPaper($request, $exam);

        return redirect()
            ->route('exam-papers.index', $exam)
            ->with('success', 'Exam uploaded successfully for the current term.');
    }

    public function edit(Exam $exam, ExamPaper $examPaper): View
    {
        $this->authorize('update', $examPaper);
        $this->ensureExamPaperContext($exam, $examPaper);
        $this->ensureCurrentUserCanManagePaperScope($examPaper->my_class_id, $examPaper->subject_id);

        [$classes, $subjects] = $this->formOptions();

        return view('livewire.exams.pages.exam-paper.edit', compact('exam', 'examPaper', 'classes', 'subjects'));
    }

    public function update(UpdateExamPaperRequest $request, Exam $exam, ExamPaper $examPaper): RedirectResponse
    {
        $this->authorize('update', $examPaper);
        $this->ensureExamPaperContext($exam, $examPaper);
        $this->ensureEditablePaper($examPaper);

        $data = $request->validated();
        $classId = (int) $data['my_class_id'];
        $subjectId = (int) $data['subject_id'];

        $this->ensureCurrentUserCanManagePaperScope($classId, $subjectId);
        $this->ensurePaperPayload(
            $data['typed_content'] ?? null,
            $request->file('attachment'),
            $examPaper->attachment_path !== null && !$request->boolean('remove_attachment')
        );
        $this->ensureUniquePaperPerExam($exam, $classId, $subjectId, $examPaper->id);

        $attachment = $this->replaceAttachment($request, $examPaper);

        $examPaper->update([
            'my_class_id' => $classId,
            'subject_id' => $subjectId,
            'title' => $this->resolvePaperTitle($data['title'] ?? null, $subjectId, $exam),
            'instructions' => $data['instructions'] ?? null,
            'typed_content' => $data['typed_content'] ?? null,
            ...$attachment,
        ]);

        return redirect()
            ->route('exam-papers.index', $exam)
            ->with('success', 'Exam paper updated successfully.');
    }

    public function destroy(Exam $exam, ExamPaper $examPaper): RedirectResponse
    {
        $this->authorize('delete', $examPaper);
        $this->ensureExamPaperContext($exam, $examPaper);
        $this->ensureEditablePaper($examPaper);
        $this->ensureCurrentUserCanManagePaperScope($examPaper->my_class_id, $examPaper->subject_id);

        if ($examPaper->attachment_path) {
            Storage::disk('public')->delete($examPaper->attachment_path);
        }

        $examPaper->delete();

        return redirect()
            ->route('exam-papers.index', $exam)
            ->with('success', 'Exam paper deleted successfully.');
    }

    public function togglePublish(Exam $exam, ExamPaper $examPaper): RedirectResponse
    {
        $this->authorize('publish', $examPaper);
        $this->ensureExamPaperContext($exam, $examPaper);

        $publish = !$examPaper->is_published;

        $examPaper->update([
            'published_at' => $publish ? now() : null,
            'published_by' => $publish ? auth()->id() : null,
        ]);

        return redirect()
            ->route('exam-papers.index', $exam)
            ->with('success', $publish ? 'Exam paper published to the portal.' : 'Exam paper withdrawn from the portal.');
    }

    public function toggleSeal(Exam $exam, ExamPaper $examPaper): RedirectResponse
    {
        $this->authorize('seal', $examPaper);
        $this->ensureExamPaperContext($exam, $examPaper);

        $seal = !$examPaper->is_sealed;

        $examPaper->update([
            'sealed_at' => $seal ? now() : null,
            'sealed_by' => $seal ? auth()->id() : null,
        ]);

        return redirect()
            ->route('exam-papers.index', $exam)
            ->with('success', $seal ? 'Exam paper sealed successfully.' : 'Exam paper unsealed successfully.');
    }

    public function print(ExamPaper $examPaper): View
    {
        $this->authorizeExamPaperPrint($examPaper);

        $examPaper->loadMissing([
            'exam.semester.academicYear',
            'myClass',
            'subject',
            'uploader',
            'publisher',
            'sealer',
        ]);

        return view('livewire.exams.pages.exam-paper.print', compact('examPaper'));
    }

    protected function formOptions(): array
    {
        return [
            $this->accessibleExamPaperClassesQuery()
                ->orderBy('name')
                ->get(['id', 'name']),
            $this->accessibleExamPaperSubjectsQuery()
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    protected function examPapersForManagement(Exam $exam): Builder
    {
        $query = ExamPaper::query()->where('exam_id', $exam->id);

        if (!$this->isRestrictedTeacherExamPaperManager()) {
            return $query;
        }

        $user = auth()->user();

        return $query->whereExists(function ($subQuery) use ($user) {
            $subQuery->selectRaw('1')
                ->from('subject_teacher as st')
                ->whereColumn('st.subject_id', 'exam_papers.subject_id')
                ->where('st.user_id', $user->id)
                ->where('st.school_id', $user->school_id)
                ->where(function ($assignmentQuery) {
                    $assignmentQuery->where('st.is_general', true)
                        ->orWhereColumn('st.my_class_id', 'exam_papers.my_class_id');
                });
        });
    }

    protected function ensureExamInCurrentSchool(Exam $exam): void
    {
        abort_unless(
            $exam->semester?->school_id === auth()->user()?->school_id,
            403
        );
    }

    protected function ensureExamPaperContext(Exam $exam, ExamPaper $examPaper): void
    {
        $this->ensureExamInCurrentSchool($exam);

        abort_unless(
            (int) $examPaper->exam_id === (int) $exam->id,
            404
        );
    }

    protected function ensureCurrentUserCanManagePaperScope(int $classId, int $subjectId): void
    {
        abort_unless($this->currentUserCanManageExamPaperClass($classId), 403);
        abort_unless($this->currentUserCanManageExamPaperSubject($subjectId, $classId), 403);
    }

    protected function ensurePaperPayload(?string $typedContent, $attachment = null, bool $hasExistingAttachment = false): void
    {
        $hasTypedContent = trim((string) $typedContent) !== '';

        if ($hasTypedContent || $attachment !== null || $hasExistingAttachment) {
            return;
        }

        throw ValidationException::withMessages([
            'typed_content' => 'Provide typed paper content or upload an exam file.',
        ]);
    }

    protected function ensureUniquePaperPerExam(Exam $exam, int $classId, int $subjectId, ?int $ignorePaperId = null): void
    {
        $exists = $exam->papers()
            ->where('my_class_id', $classId)
            ->where('subject_id', $subjectId)
            ->when($ignorePaperId, fn (Builder $query) => $query->whereKeyNot($ignorePaperId))
            ->exists();

        if (!$exists) {
            return;
        }

        throw ValidationException::withMessages([
            'subject_id' => 'An exam paper already exists for this class and subject in the current term.',
        ]);
    }

    protected function storeAttachment(Request $request): array
    {
        $file = $request->file('attachment');

        if (!$file) {
            return [
                'attachment_path' => null,
                'attachment_name' => null,
                'attachment_mime_type' => null,
                'attachment_size' => null,
            ];
        }

        return [
            'attachment_path' => $file->store('exam-papers', 'public'),
            'attachment_name' => $file->getClientOriginalName(),
            'attachment_mime_type' => $file->getMimeType(),
            'attachment_size' => $file->getSize(),
        ];
    }

    protected function replaceAttachment(Request $request, ExamPaper $examPaper): array
    {
        if ($request->file('attachment')) {
            if ($examPaper->attachment_path) {
                Storage::disk('public')->delete($examPaper->attachment_path);
            }

            return $this->storeAttachment($request);
        }

        if ($request->boolean('remove_attachment')) {
            if ($examPaper->attachment_path) {
                Storage::disk('public')->delete($examPaper->attachment_path);
            }

            return [
                'attachment_path' => null,
                'attachment_name' => null,
                'attachment_mime_type' => null,
                'attachment_size' => null,
            ];
        }

        return [
            'attachment_path' => $examPaper->attachment_path,
            'attachment_name' => $examPaper->attachment_name,
            'attachment_mime_type' => $examPaper->attachment_mime_type,
            'attachment_size' => $examPaper->attachment_size,
        ];
    }

    protected function ensureEditablePaper(ExamPaper $examPaper): void
    {
        if (!$examPaper->is_sealed) {
            return;
        }

        abort_unless(auth()->user()?->can('seal exam paper'), 403);
    }

    protected function authorizeExamPaperPrint(ExamPaper $examPaper): void
    {
        $user = auth()->user();

        if ($user?->can('read exam paper')) {
            $this->ensureExamInCurrentSchool($examPaper->exam);
            $this->ensureCurrentUserCanManagePaperScope($examPaper->my_class_id, $examPaper->subject_id);
            return;
        }

        abort_unless($user?->can('view exam paper'), 403);
        abort_unless($examPaper->is_published, 403);

        $accessibleStudentIds = $this->portalAccessibleStudentUserIds();

        if ($this->isStudentStudentPortalViewer() && auth()->id()) {
            $accessibleStudentIds = collect([(int) auth()->id()]);
        }

        $canView = $accessibleStudentIds
            ->filter()
            ->contains(fn (int $studentId): bool => ExamPaper::query()
                ->whereKey($examPaper->id)
                ->published()
                ->visibleToStudent($studentId)
                ->exists());

        abort_unless($canView, 403);
    }

    protected function currentSchoolSemester(): ?Semester
    {
        return auth()->user()?->school?->semester?->loadMissing('academicYear');
    }

    protected function resolveCurrentTermUploadExam(?Semester $semester = null, bool $createIfMissing = false): ?Exam
    {
        $semester = $semester ?: $this->currentSchoolSemester();

        if (!$semester) {
            return null;
        }

        $query = Exam::query()
            ->where('semester_id', $semester->id)
            ->uploadArchives();

        if (!$createIfMissing) {
            return $query->first();
        }

        return $query->firstOrCreate(
            [
                'semester_id' => $semester->id,
                'description' => Exam::uploadArchiveDescription($semester),
            ],
            [
                'name' => Exam::uploadArchiveName($semester),
                'start_date' => now()->toDateString(),
                'stop_date' => now()->toDateString(),
                'active' => false,
                'publish_result' => false,
            ]
        );
    }

    protected function persistExamPaper(Request $request, Exam $exam): ExamPaper
    {
        $data = $request->validated();
        $classId = (int) $data['my_class_id'];
        $subjectId = (int) $data['subject_id'];

        $this->ensureCurrentUserCanManagePaperScope($classId, $subjectId);
        $this->ensurePaperPayload($data['typed_content'] ?? null, $request->file('attachment'));
        $this->ensureUniquePaperPerExam($exam, $classId, $subjectId);

        $attachment = $this->storeAttachment($request);

        return ExamPaper::create([
            'exam_id' => $exam->id,
            'my_class_id' => $classId,
            'subject_id' => $subjectId,
            'title' => $this->resolvePaperTitle($data['title'] ?? null, $subjectId, $exam),
            'instructions' => $data['instructions'] ?? null,
            'typed_content' => $data['typed_content'] ?? null,
            'uploaded_by' => auth()->id(),
            ...$attachment,
        ]);
    }

    protected function resolvePaperTitle(?string $title, int $subjectId, Exam $exam): string
    {
        $title = trim((string) $title);

        if ($title !== '') {
            return $title;
        }

        $subject = Subject::query()->find($subjectId);
        $term = $exam->semester?->name;
        $session = $exam->semester?->academicYear?->name;

        return trim(collect([
            $subject?->name,
            $term,
            $session,
            'Exam',
        ])->filter()->implode(' '));
    }
}
