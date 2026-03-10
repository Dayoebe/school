<?php

namespace App\Http\Controllers;

use App\Models\Assessment\Assessment;
use App\Models\User;
use App\Traits\RestrictsTeacherCbtManagement;
use Illuminate\Http\Request;

class CbtResultController extends Controller
{
    use RestrictsTeacherCbtManagement;

    public function print(Request $request, Assessment $assessment, User $student, int $attemptNumber)
    {
        $viewer = $request->user();

        abort_unless($viewer !== null, 403);
        abort_unless($this->currentUserCanManageCbtAssessment($assessment->id, $viewer), 403);
        abort_unless($student->school_id === $viewer->school_id && $student->hasRole('student'), 404);

        $attempt = $assessment->getStudentResults($student->id, $attemptNumber);
        abort_unless($attempt !== null, 404);

        $assessment->loadMissing(['course.classGroup.school', 'lesson', 'questions']);

        return view('cbt.print-result', [
            'assessment' => $assessment,
            'attempt' => $attempt,
            'school' => $assessment->course?->classGroup?->school ?: $viewer->school,
            'student' => $student,
        ]);
    }
}
