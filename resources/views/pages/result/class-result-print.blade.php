<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Results - {{ $class->name }} - {{ $semester->name }} {{ $academicYear->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        
        .student-page {
            page-break-after: always;
            height: 297mm;
        }
        
        .student-page:last-child {
            page-break-after: auto;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
            
            .print-border {
                border: 1px solid #000;
                min-height: 27.7cm;
                box-sizing: border-box;
            }
        }
        
        .grade-A {
            background-color: #e6ffec !important;
        }
        
        .grade-B {
            background-color: #f0fff4 !important;
        }
        
        .grade-C {
            background-color: #fffaf0 !important;
        }
        
        .grade-D, .grade-E {
            background-color: #fff5f5 !important;
        }
        
        .grade-F {
            background-color: #ffebee !important;
        }
    </style>
</head>

<body class="bg-white text-gray-900 font-sans">
    @foreach($studentReports as $report)
    <div class="student-page p-4">
        <!-- School Header -->
        <div class="flex items-center justify-between border-b-2 border-blue-900 pb-2 mb-4">
            <img src="{{ asset('img/logo.png') }}" alt="School Logo" class="h-16 w-16">
            <div class="text-center flex-1">
                <h1 class="text-2xl font-bold text-blue-900 uppercase">ELITES INTERNATIONAL COLLEGE, AWKA</h1>
                <p class="text-xs uppercase tracking-wide text-gray-700">To Create a Brighter Future</p>
                <p class="text-xs text-gray-600">Email: elitesinternationalcollege@gmail.com | Tel: 08066025508</p>
                <p class="font-semibold text-sm mt-1 uppercase text-blue-900">
                    {{ strtoupper($class->name) }} - {{ strtoupper($semester->name) }} {{ $academicYear->name }} ACADEMIC REPORT
                </p>
            </div>
        </div>
        
        <!-- Student Info -->
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <p class="font-bold">Name: <span class="font-normal">{{ $report['student']->user->name }}</span></p>
                <p class="font-bold">Class: <span class="font-normal">{{ $class->name }}</span></p>
                <p class="font-bold">Admission No: <span class="font-normal">{{ $report['student']->admission_number }}</span></p>
            </div>
            <div>
                <p class="font-bold">Academic Year: <span class="font-normal">{{ $academicYear->name }}</span></p>
                <p class="font-bold">Term: <span class="font-normal">{{ $semester->name }}</span></p>
                <p class="font-bold">Position: <span class="font-normal">{{ $report['rank'] }} of {{ $classStats['total_students'] }}</span></p>
            </div>
            <div class="flex justify-end">
                <img src="{{ $report['student']->user->profile_photo_url }}" alt="Student Photo" 
                     class="h-20 w-16 object-cover border border-gray-300 rounded-md">
            </div>
        </div>
        
        <!-- Results Table -->
        <table class="w-full border border-gray-300 text-xs mb-4">
            <thead class="bg-blue-900 text-white">
                <tr>
                    <th class="border p-1 text-left">Subject</th>
                    <th class="border p-1">Test (40)</th>
                    <th class="border p-1">Exam (60)</th>
                    <th class="border p-1">Total</th>
                    <th class="border p-1">Grade</th>
                    <th class="border p-1 text-left">Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                    @php
                        $result = $report['results'][$subject->id] ?? null;
                        $gradeClass = $result ? 'grade-' . substr($result->grade, 0, 1) : '';
                    @endphp
                    <tr class="{{ $gradeClass }}">
                        <td class="border p-1">{{ $subject->name }}</td>
                        <td class="border p-1 text-center">{{ $result->test_score ?? '-' }}</td>
                        <td class="border p-1 text-center">{{ $result->exam_score ?? '-' }}</td>
                        <td class="border p-1 text-center">{{ $result->total_score ?? '-' }}</td>
                        <td class="border p-1 text-center">{{ $result->grade ?? '-' }}</td>
                        <td class="border p-1">{{ $result->teacher_comment ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Summary -->
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="border border-gray-300 p-2 rounded">
                <p class="font-bold text-center border-b border-gray-300 mb-1">Total Score</p>
                <p class="text-center">{{ $report['total_score'] }}/{{ $classStats['max_total_score'] }}</p>
            </div>
            <div class="border border-gray-300 p-2 rounded">
                <p class="font-bold text-center border-b border-gray-300 mb-1">Percentage</p>
                <p class="text-center">{{ $report['percentage'] }}%</p>
            </div>
            <div class="border border-gray-300 p-2 rounded">
                <p class="font-bold text-center border-b border-gray-300 mb-1">Class Position</p>
                <p class="text-center">{{ $report['rank'] }} of {{ $classStats['total_students'] }}</p>
            </div>
        </div>
        
        <!-- Comments -->
        <div class="grid grid-cols-2 gap-4">
            <div class="border border-gray-300 p-2 rounded">
                <p class="font-bold text-center border-b border-gray-300 mb-1">Teacher's Comment</p>
                <p class="text-sm">{{ $report['student']->termReports->firstWhere('semester_id', $semester->id)->class_teacher_comment ?? 'No comment' }}</p>
            </div>
            <div class="border border-gray-300 p-2 rounded">
                <p class="font-bold text-center border-b border-gray-300 mb-1">Principal's Comment</p>
                <p class="text-sm">{{ $report['student']->termReports->firstWhere('semester_id', $semester->id)->principal_comment ?? 'No comment' }}</p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="grid grid-cols-2 gap-4 mt-8">
            <div class="text-center">
                <div class="border-t border-black w-32 mx-auto mb-1"></div>
                <p class="text-xs font-semibold">Class Teacher</p>
            </div>
            <div class="text-center">
                <div class="border-t border-black w-32 mx-auto mb-1"></div>
                <p class="text-xs font-semibold">Principal</p>
            </div>
        </div>
        
        <div class="text-center text-xs text-gray-500 mt-4">
            Generated on {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
    @endforeach
</body>
</html>