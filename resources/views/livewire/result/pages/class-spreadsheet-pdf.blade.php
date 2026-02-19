<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $class->name }} - Class Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 5px 0;
            font-size: 18px;
            color: #1e40af;
        }
        .header p {
            margin: 3px 0;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #1e40af;
            color: white;
            padding: 8px 4px;
            text-align: center;
            font-size: 9px;
            border: 1px solid #ddd;
        }
        td {
            padding: 6px 4px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        .student-name {
            text-align: left;
            font-weight: bold;
        }
        .total-col {
            background-color: #eff6ff;
            font-weight: bold;
        }
        .position {
            font-weight: bold;
            color: #1e40af;
        }
        .grade-A { background-color: #dcfce7; }
        .grade-B { background-color: #dbeafe; }
        .grade-C { background-color: #fef9c3; }
        .grade-D { background-color: #fed7aa; }
        .grade-F { background-color: #fee2e2; }
        .stats {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 8px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .stat-box {
            text-align: center;
            padding: 10px;
        }
        .stat-label {
            color: #64748b;
            font-size: 9px;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $class->name }} - {{ $viewType === 'termly' ? $semester->name : 'Annual' }} Results</h1>
        <p>{{ $academicYear->name }}</p>
        <p>Generated on {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @if($viewType === 'termly')
        {{-- Termly Results Table --}}
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">Pos</th>
                    <th style="width: 15%;">Student Name</th>
                    @foreach($subjects as $subject)
                        <th style="width: {{ 70 / ($subjects->count() * 2) }}%;">
                            {{ $subject->short_name ?? substr($subject->name, 0, 15) }}
                        </th>
                    @endforeach
                    <th class="total-col" style="width: 8%;">Total</th>
                    <th class="total-col" style="width: 7%;">Avg %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($spreadsheetData as $data)
                    <tr>
                        <td class="position">{{ $data['position'] }}</td>
                        <td class="student-name">{{ $data['student']->user->name }}</td>
                        @foreach($subjects as $subject)
                            @php
                                $subjectData = $data['subject_scores'][$subject->id] ?? null;
                                $score = $subjectData['score'] ?? null;
                                $grade = $subjectData['grade'] ?? '-';
                                $gradeClass = match(substr($grade, 0, 1)) {
                                    'A' => 'grade-A',
                                    'B' => 'grade-B',
                                    'C' => 'grade-C',
                                    'D', 'E' => 'grade-D',
                                    'F' => 'grade-F',
                                    default => ''
                                };
                            @endphp
                            <td class="{{ $gradeClass }}">
                                {{ $score ?? '-' }}<br>
                                <small style="color: #64748b;">({{ $grade }})</small>
                            </td>
                        @endforeach
                        <td class="total-col">{{ $data['total_score'] }}</td>
                        <td class="total-col">{{ $data['average'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        {{-- Annual Results Table --}}
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 5%;">Pos</th>
                    <th rowspan="2" style="width: 15%;">Student Name</th>
                    <th colspan="{{ $semesters->count() }}" style="background-color: #6366f1;">Term Totals</th>
                    <th colspan="{{ $subjects->count() }}" style="background-color: #7c3aed;">Subject Averages</th>
                    <th rowspan="2" class="total-col" style="width: 8%;">Grand Total</th>
                    <th rowspan="2" class="total-col" style="width: 7%;">Avg %</th>
                </tr>
                <tr>
                    @foreach($semesters as $semester)
                        <th style="background-color: #818cf8;">{{ $semester->name }}</th>
                    @endforeach
                    @foreach($subjects as $subject)
                        <th style="background-color: #a78bfa;">{{ $subject->short_name ?? substr($subject->name, 0, 10) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($spreadsheetData as $data)
                    <tr>
                        <td class="position">{{ $data['position'] }}</td>
                        <td class="student-name">{{ $data['student']->user->name }}</td>
                        @foreach($semesters as $semester)
                            <td style="background-color: #eef2ff;">{{ $data['term_scores'][$semester->id] ?? 0 }}</td>
                        @endforeach
                        @foreach($subjects as $subject)
                            @php
                                $subjectData = $data['subject_scores'][$subject->id] ?? null;
                                $average = $subjectData['average'] ?? 0;
                                $grade = $subjectData['grade'] ?? '-';
                                $gradeClass = match(substr($grade, 0, 1)) {
                                    'A' => 'grade-A',
                                    'B' => 'grade-B',
                                    'C' => 'grade-C',
                                    'D', 'E' => 'grade-D',
                                    'F' => 'grade-F',
                                    default => ''
                                };
                            @endphp
                            <td class="{{ $gradeClass }}">
                                {{ $average }}<br>
                                <small style="color: #64748b;">({{ $grade }})</small>
                            </td>
                        @endforeach
                        <td class="total-col">{{ $data['grand_total'] }}</td>
                        <td class="total-col">{{ $data['annual_average'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div style="margin-top: 30px; text-align: center; font-size: 9px; color: #94a3b8;">
        <p>This is an official document generated by the school management system.</p>
        <p>© {{ now()->year }} {{ config('app.name') }}</p>
    </div>
</body>
</html>