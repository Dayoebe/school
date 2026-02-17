<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TermlyClassResultsExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $spreadsheetData;
    protected $subjects;
    protected $className;
    protected $term;
    protected $year;

    public function __construct($spreadsheetData, $subjects, $className, $term, $year)
    {
        $this->spreadsheetData = $spreadsheetData;
        $this->subjects = $subjects;
        $this->className = $className;
        $this->term = $term;
        $this->year = $year;
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->spreadsheetData as $data) {
            $row = [
                $data['position'],
                $data['student']->user->name,
            ];

            // Add subject scores
            foreach ($this->subjects as $subject) {
                $subjectData = $data['subject_scores'][$subject->id] ?? null;
                $row[] = $subjectData['score'] ?? '-';
                $row[] = $subjectData['grade'] ?? '-';
            }

            // Add totals
            $row[] = $data['total_score'];
            $row[] = $data['average'];
            $row[] = $data['position'];

            $rows->push($row);
        }

        return $rows;
    }

    public function headings(): array
    {
        $headings = ['Position', 'Student Name'];

        foreach ($this->subjects as $subject) {
            $headings[] = $subject->name . ' (Score)';
            $headings[] = $subject->name . ' (Grade)';
        }

        $headings[] = 'Total Score';
        $headings[] = 'Average %';
        $headings[] = 'Position';

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return $this->className . ' - ' . $this->term;
    }
}

class AnnualClassResultsExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $spreadsheetData;
    protected $subjects;
    protected $semesters;
    protected $className;
    protected $year;

    public function __construct($spreadsheetData, $subjects, $semesters, $className, $year)
    {
        $this->spreadsheetData = $spreadsheetData;
        $this->subjects = $subjects;
        $this->semesters = $semesters;
        $this->className = $className;
        $this->year = $year;
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->spreadsheetData as $data) {
            $row = [
                $data['position'],
                $data['student']->user->name,
            ];

            // Add term scores
            foreach ($this->semesters as $semester) {
                $row[] = $data['term_scores'][$semester->id] ?? 0;
            }

            // Add subject averages
            foreach ($this->subjects as $subject) {
                $subjectData = $data['subject_scores'][$subject->id] ?? null;
                $row[] = $subjectData['average'] ?? '-';
                $row[] = $subjectData['grade'] ?? '-';
            }

            // Add totals
            $row[] = $data['grand_total'];
            $row[] = $data['annual_average'];
            $row[] = $data['position'];

            $rows->push($row);
        }

        return $rows;
    }

    public function headings(): array
    {
        $headings = ['Position', 'Student Name'];

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

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return $this->className . ' - Annual';
    }
}