<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{MyClass, AcademicYear, Semester, Result, StudentRecord, Subject};
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\{TermlyClassResultsExport, AnnualClassResultsExport};
use Barryvdh\DomPDF\Facade\Pdf;

class ClassSpreadsheetExportController extends Controller
{
    public function exportExcel(Request $request)
    {
        $viewType = $request->input('view_type');
        $classId = $request->input('class_id');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');

        $class = MyClass::findOrFail($classId);
        $academicYear = AcademicYear::findOrFail($academicYearId);

        if ($viewType === 'termly') {
            $semester = Semester::findOrFail($semesterId);
            $data = $this->getTermlyData($classId, $academicYearId, $semesterId);
            
            $export = new TermlyClassResultsExport(
                $data['spreadsheetData'],
                $data['subjects'],
                $class->name,
                $semester->name,
                $academicYear->name
            );

            $filename = "{$class->name}_{$semester->name}_{$academicYear->name}.xlsx";
        } else {
            $data = $this->getAnnualData($classId, $academicYearId);
            
            $export = new AnnualClassResultsExport(
                $data['spreadsheetData'],
                $data['subjects'],
                $data['semesters'],
                $class->name,
                $academicYear->name
            );

            $filename = "{$class->name}_Annual_{$academicYear->name}.xlsx";
        }

        return Excel::download($export, $filename);
    }

    public function exportPdf(Request $request)
    {
        $viewType = $request->input('view_type');
        $classId = $request->input('class_id');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');

        $class = MyClass::findOrFail($classId);
        $academicYear = AcademicYear::findOrFail($academicYearId);

        if ($viewType === 'termly') {
            $semester = Semester::findOrFail($semesterId);
            $data = $this->getTermlyData($classId, $academicYearId, $semesterId);
            $data['semester'] = $semester;
            $data['viewType'] = 'termly';
        } else {
            $data = $this->getAnnualData($classId, $academicYearId);
            $data['viewType'] = 'annual';
        }

        $data['class'] = $class;
        $data['academicYear'] = $academicYear;

        $pdf = Pdf::loadView('pages.result.class-spreadsheet-pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        $filename = $viewType === 'termly' 
            ? "{$class->name}_{$semester->name}_{$academicYear->name}.pdf"
            : "{$class->name}_Annual_{$academicYear->name}.pdf";

        return $pdf->download($filename);
    }

    protected function getTermlyData($classId, $academicYearId, $semesterId)
    {
        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('academic_year_id', $academicYearId)
            ->where('my_class_id', $classId)
            ->pluck('student_record_id');

        $students = StudentRecord::with(['user' => function($q) {
                $q->whereNull('deleted_at');
            }])
            ->whereIn('id', $studentRecordIds)
            ->whereHas('user', function($q) {
                $q->whereNull('deleted_at');
            })
            ->orderByName()
            ->get();

        $subjects = Subject::where('my_class_id', $classId)->orderBy('name')->get();

        $allResults = Result::whereIn('student_record_id', $studentRecordIds)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->with('subject')
            ->get()
            ->groupBy('student_record_id');

        $spreadsheetData = [];
        
        foreach ($students as $student) {
            if (!$student->user) continue;

            $studentResults = $allResults->get($student->id, collect());
            $resultsBySubject = $studentResults->keyBy('subject_id');
            
            $subjectScores = [];
            $totalScore = 0;
            $subjectCount = 0;

            foreach ($subjects as $subject) {
                $result = $resultsBySubject->get($subject->id);
                $score = $result ? $result->total_score : null;
                
                $subjectScores[$subject->id] = [
                    'score' => $score,
                    'grade' => $score !== null ? $this->calculateGrade($score) : '-',
                ];

                if ($score !== null) {
                    $totalScore += $score;
                    $subjectCount++;
                }
            }

            $average = $subjectCount > 0 ? round($totalScore / $subjectCount, 2) : 0;

            $spreadsheetData[] = [
                'student' => $student,
                'subject_scores' => $subjectScores,
                'total_score' => $totalScore,
                'average' => $average,
                'position' => 0,
            ];
        }

        usort($spreadsheetData, fn($a, $b) => $b['total_score'] <=> $a['total_score']);
        
        foreach ($spreadsheetData as $index => &$data) {
            $data['position'] = $index + 1;
        }

        return [
            'spreadsheetData' => $spreadsheetData,
            'subjects' => $subjects,
        ];
    }

    protected function getAnnualData($classId, $academicYearId)
    {
        $semesters = Semester::where('academic_year_id', $academicYearId)->get();
        
        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('academic_year_id', $academicYearId)
            ->where('my_class_id', $classId)
            ->pluck('student_record_id');

        $students = StudentRecord::with(['user' => function($q) {
                $q->whereNull('deleted_at');
            }])
            ->whereIn('id', $studentRecordIds)
            ->whereHas('user', function($q) {
                $q->whereNull('deleted_at');
            })
            ->orderByName()
            ->get();

        $subjects = Subject::where('my_class_id', $classId)->orderBy('name')->get();

        $allResults = Result::whereIn('student_record_id', $studentRecordIds)
            ->where('academic_year_id', $academicYearId)
            ->whereIn('semester_id', $semesters->pluck('id'))
            ->with(['subject', 'semester'])
            ->get()
            ->groupBy('student_record_id');

        $spreadsheetData = [];

        foreach ($students as $student) {
            if (!$student->user) continue;

            $studentResults = $allResults->get($student->id, collect());
            
            $termScores = [];
            $subjectAnnualScores = [];
            $grandTotal = 0;

            foreach ($semesters as $semester) {
                $semesterResults = $studentResults->where('semester_id', $semester->id);
                $termTotal = $semesterResults->sum('total_score');
                $termScores[$semester->id] = $termTotal;
                $grandTotal += $termTotal;
            }

            foreach ($subjects as $subject) {
                $subjectResults = $studentResults->where('subject_id', $subject->id);
                $subjectTotal = $subjectResults->sum('total_score');
                $subjectAverage = $semesters->count() > 0 
                    ? round($subjectTotal / $semesters->count(), 2) 
                    : 0;

                $subjectAnnualScores[$subject->id] = [
                    'total' => $subjectTotal,
                    'average' => $subjectAverage,
                    'grade' => $subjectAverage > 0 ? $this->calculateGrade($subjectAverage) : '-',
                ];
            }

            $maxPossible = $subjects->count() * 100 * $semesters->count();
            $annualAverage = $maxPossible > 0 ? round(($grandTotal / $maxPossible) * 100, 2) : 0;

            $spreadsheetData[] = [
                'student' => $student,
                'term_scores' => $termScores,
                'subject_scores' => $subjectAnnualScores,
                'grand_total' => $grandTotal,
                'annual_average' => $annualAverage,
                'position' => 0,
            ];
        }

        usort($spreadsheetData, fn($a, $b) => $b['grand_total'] <=> $a['grand_total']);
        
        foreach ($spreadsheetData as $index => &$data) {
            $data['position'] = $index + 1;
        }

        return [
            'spreadsheetData' => $spreadsheetData,
            'subjects' => $subjects,
            'semesters' => $semesters,
        ];
    }

    protected function calculateGrade($score)
    {
        return match(true) {
            $score >= 75 => 'A1',
            $score >= 70 => 'B2',
            $score >= 65 => 'B3',
            $score >= 60 => 'C4',
            $score >= 55 => 'C5',
            $score >= 50 => 'C6',
            $score >= 45 => 'D7',
            $score >= 40 => 'E8',
            default => 'F9',
        };
    }
}