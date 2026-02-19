<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassResultsExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    private string $mode;
    private $spreadsheetData;
    private $subjects;
    private $className;
    private $term;
    private $year;
    private $semesters;

    private function __construct(
        string $mode,
        $spreadsheetData,
        $subjects,
        $className,
        $year,
        $term = null,
        $semesters = null
    ) {
        $this->mode = $mode;
        $this->spreadsheetData = $spreadsheetData;
        $this->subjects = $subjects;
        $this->className = $className;
        $this->term = $term;
        $this->year = $year;
        $this->semesters = $semesters;
    }

    public static function forTermly($spreadsheetData, $subjects, $className, $term, $year): self
    {
        return new self('termly', $spreadsheetData, $subjects, $className, $year, $term);
    }

    public static function forAnnual($spreadsheetData, $subjects, $semesters, $className, $year): self
    {
        return new self('annual', $spreadsheetData, $subjects, $className, $year, null, $semesters);
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->spreadsheetData as $data) {
            $row = [
                $data['position'],
                $data['student']->user->name,
            ];

            if ($this->mode === 'termly') {
                foreach ($this->subjects as $subject) {
                    $subjectData = $data['subject_scores'][$subject->id] ?? null;
                    $row[] = $subjectData['score'] ?? '-';
                    $row[] = $subjectData['grade'] ?? '-';
                }

                $row[] = $data['total_score'];
                $row[] = $data['average'];
                $row[] = $data['position'];
            } else {
                foreach ($this->semesters as $semester) {
                    $row[] = $data['term_scores'][$semester->id] ?? 0;
                }

                foreach ($this->subjects as $subject) {
                    $subjectData = $data['subject_scores'][$subject->id] ?? null;
                    $row[] = $subjectData['average'] ?? '-';
                    $row[] = $subjectData['grade'] ?? '-';
                }

                $row[] = $data['grand_total'];
                $row[] = $data['annual_average'];
                $row[] = $data['position'];
            }

            $rows->push($row);
        }

        return $rows;
    }

    public function headings(): array
    {
        $headings = ['Position', 'Student Name'];

        if ($this->mode === 'termly') {
            foreach ($this->subjects as $subject) {
                $headings[] = $subject->name . ' (Score)';
                $headings[] = $subject->name . ' (Grade)';
            }

            $headings[] = 'Total Score';
            $headings[] = 'Average %';
            $headings[] = 'Position';
        } else {
            foreach ($this->semesters as $semester) {
                $headings[] = $semester->name . ' Total';
            }

            foreach ($this->subjects as $subject) {
                $headings[] = $subject->name . ' (Avg)';
                $headings[] = $subject->name . ' (Grade)';
            }

            $headings[] = 'Grand Total';
            $headings[] = 'Annual Average %';
            $headings[] = 'Position';
        }

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $this->mode === 'termly' ? '2563EB' : '7C3AED'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return $this->mode === 'termly'
            ? $this->className . ' - ' . $this->term
            : $this->className . ' - Annual';
    }
}
