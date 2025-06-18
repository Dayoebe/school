<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Official Academic Report - {{ $studentRecord->user->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        @media print {
            body {
                margin: 0;
                padding: 0.5cm;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-container {
                transform: scale(0.85);
                transform-origin: top left;
                width: 117%;
            }

            .no-print {
                display: none !important;
            }

            .print-border {
                border: 2px solid #000;
                min-height: 27.7cm;
                box-sizing: border-box;
            }

            .break-inside-avoid {
                break-inside: avoid;
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

        .grade-D,
        .grade-E {
            background-color: #fff5f5 !important;
        }

        .grade-F {
            background-color: #ffebee !important;
        }
    </style>
</head>

<body class="bg-white text-gray-900 font-sans relative">
    <div class="no-print fixed top-4 right-4 z-50">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-lg flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
            </svg>
            Print Report
        </button>
    </div>

    <div class="print-container mx-auto p-4 print-border">
        <div class="absolute inset-0 flex items-center justify-center opacity-10 -z-10">
            <img src="{{ asset('img/logo.png') }}" alt="Watermark" class="h-auto w-auto rotate-0">
        </div>

        <div class="absolute bottom-4 right-4 opacity-80 -z-10">
            <div class="border-2 border-red-500 rounded-full h-20 w-20 flex items-center justify-center text-red-500 font-bold text-xs text-center p-2">
                ELITES <br>INTERNATIONAL <br> COLLEGE
            </div>
        </div>

        <div class="flex items-center border-b border-blue-900 pb-2 mb-2 break-inside-avoid">
            <img src="{{ asset('img/logo.png') }}" alt="School Logo" class="h-20 w-20 object-contain ml-10">
            <div class="text-center flex-1">
                <h1 class="text-3xl font-bold text-blue-900 uppercase leading-tight">ELITES INTERNATIONAL COLLEGE, AWKA</h1>
                <p class="text-sm uppercase tracking-wide text-gray-700">To Create a Brighter Future</p>
                <p class="text-xs text-gray-600">Email: elitesinternationalcollege@gmail.com | Tel: 08066025508</p>
                <p class="font-semibold text-sm mt-1 uppercase text-blue-900">
                    {{ strtoupper($studentRecord->myClass->name) }} - {{ strtoupper($semesterName) }} {{ $academicYearName }} ACADEMIC REPORT
                </p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-1 text-sm break-inside-avoid">
            <div class="space-y-1">
                <div><span class="font-bold uppercase">Name:</span> {{ strtoupper($studentRecord->user->name) }}</div>
                <div><span class="font-bold">Class:</span> {{ $studentRecord->myClass->name }}</div>
                <div><span class="font-bold">Gender:</span> {{ ucfirst($studentRecord->user->gender) }}</div>
                <div><span class="font-bold">Attendance:</span> Present:__{{ $studentRecord->present }} Absent:__{{ $studentRecord->absent }}</div>
                <div><span class="font-bold">Number of students in class:</span> {{ $totalStudents }}</div>
            </div>

            <div class="space-y-1">
                <div><span class="font-bold">Admission No:</span> {{ $studentRecord->admission_number ?? ($studentRecord->admission_no ?? 'N/A') }}</div>
                <div><span class="font-bold">Academic Year:</span> {{ $academicYearName }}</div>
                <div><span class="font-bold">Term:</span> {{ $semesterName }}</div>
                <div><span class="font-bold">Date of Birth:</span>
                    @if ($studentRecord->user->birthday)
                        {{ \Carbon\Carbon::parse($studentRecord->user->birthday)->format('d/m/Y') }}
                    @else
                        N/A
                    @endif
                </div>
                <div><span class="font-bold">Class Position:</span> {{ $classPosition }}</div>
            </div>

            <div class="flex justify-center items-start">
                <img src="{{ $studentRecord->user->profile_photo_url }}" alt="Student Photo" class="h-24 w-20 object-cover border border-white rounded-md shadow-sm">
            </div>
        </div>

        <div class="overflow-x-auto mb-2 text-sm break-inside-avoid">
            <table class="w-full border border-gray-300 text-xs">
                <thead class="bg-blue-900 text-white">
                    <tr>
                        <th class="border p-1 text-left">Subject</th>
                        <th class="border p-1">CA1 (10)</th>
                        <th class="border p-1">CA2 (10)</th>
                        <th class="border p-1">CA3 (10)</th>
                        <th class="border p-1">CA4 (10)</th>
                        <th class="border p-1">Exam (60)</th>
                        <th class="border p-1">Total</th>
                        <th class="border p-1">Grade</th>
                        <th class="border p-1">Highest Score</th>
                        <th class="border p-1">Lowest Score</th>
                        <th class="border p-1 text-left">Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subjects->sortBy('name') as $subject)
                        @php
                            $result = $results[$subject->id] ?? null;
                            $stats = $subjectStats[$subject->id] ?? null;
                            $gradeClass = $result ? 'grade-' . substr($result['grade'], 0, 1) : '';
                            $isHighest = $result && $stats && $result['total_score'] == $stats['highest'];
                            $isLowest = $result && $stats && $result['total_score'] == $stats['lowest'];
                        @endphp
                        <tr class="{{ $gradeClass }}">
                            <td class="border p-1">{{ $subject->name }}</td>
                            <td class="border p-1 text-center">{{ $result['ca1_score'] ?? '-' }}</td>
                            <td class="border p-1 text-center">{{ $result['ca2_score'] ?? '-' }}</td>
                            <td class="border p-1 text-center">{{ $result['ca3_score'] ?? '-' }}</td>
                            <td class="border p-1 text-center">{{ $result['ca4_score'] ?? '-' }}</td>
                            <td class="border p-1 text-center">{{ $result['exam_score'] ?? '-' }}</td>
                            <td class="border p-1 text-center {{ $isHighest ? 'font-bold text-green-600' : '' }} {{ $isLowest ? 'font-bold text-red-600' : '' }}">
                                {{ $result['total_score'] ?? '-' }}
                            </td>
                            <td class="border p-1 text-center">{{ $result['grade'] ?? '-' }}</td>
                            <td class="border p-1 text-center bg-green-50">{{ $stats['highest'] ?? '-' }}</td>
                            <td class="border p-1 text-center bg-red-50">{{ $stats['lowest'] ?? '-' }}</td>
                            <td class="border p-1">{{ $result['comment'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-2 text-xs break-inside-avoid">
            <div class="border border-gray-300 p-3 rounded-lg shadow-sm">
                <h3 class="font-bold text-blue-900 border-b border-gray-300 pb-2 mb-2">PSYCHOMOTOR</h3>
                <ul class="space-y-1">
                    <li>Handwriting: 4</li>
                    <li>Verbal Fluency: 4</li>
                    <li>Game/Sports: 4</li>
                    <li>Handling Tools: 4</li>
                </ul>
            </div>

            <div class="border border-gray-300 p-3 rounded-lg shadow-sm">
                <h3 class="font-bold text-blue-900 border-b border-gray-300 pb-2 mb-2">AFFECTIVE</h3>
                <ul class="space-y-1">
                    <li>Punctuality: 4</li>
                    <li>Neatness: 4</li>
                    <li>Politeness: 4</li>
                    <li>Leadership: 4</li>
                </ul>
            </div>

            <div class="border border-gray-300 p-3 rounded-lg shadow-sm">
                <h3 class="font-bold text-blue-900 border-b border-gray-300 pb-2 mb-2">CO-CURRICULAR</h3>
                <ul class="space-y-1">
                    <li>Athletics: 4</li>
                    <li>Football: 4</li>
                    <li>Volley Ball: 4</li>
                    <li>Table Tennis: 4</li>
                </ul>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-2 text-xs break-inside-avoid">
            <div class="border border-gray-300 p-4 rounded-lg shadow-sm">
                <h3 class="font-bold text-blue-900 border-b border-gray-300 pb-2 mb-2">ACADEMIC SUMMARY</h3>
                <div class="space-y-2">
                    @php
                        $subjectCount = count($subjects);
                        $passedCount = $subjectCount > 0 
                            ? ($subjectsPassed ?? collect($results)->filter(fn($r) => ($r['total_score'] ?? 0) >= 40)->count())
                            : 0;
                        $maxPossible = $subjectCount * 100;
                        $percentage = $maxPossible > 0 
                            ? round(($totalScore / $maxPossible) * 100, 2) 
                            : 0;
                        $passed = $maxPossible > 0 && $totalScore >= ($maxPossible * 0.4);
                    @endphp
                    
                    <p><span class="font-semibold">Subjects:</span> {{ $subjectCount }} (Passed: {{ $passedCount }})</p>
                    <p><span class="font-semibold">Total Score:</span> {{ $totalScore }}/{{ $maxPossible }}</p>
                    <p><span class="font-semibold">Percentage:</span> {{ $percentage }}%</p>
                    <p><span class="font-semibold">Position:</span> {{ $classPosition }} out of {{ $totalStudents }}</p>
                    <p><span class="font-semibold">Result:</span>
                        @if($subjectCount > 0)
                            <span class="font-bold {{ $passed ? 'text-green-600' : 'text-red-600' }}">
                                {{ $passed ? 'PASSED' : 'FAILED' }}
                            </span>
                        @else
                            <span class="font-bold text-gray-600">N/A</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="border border-gray-300 p-4 rounded-lg shadow-sm">
                <h3 class="font-bold text-blue-900 border-b border-gray-300 pb-2 mb-2">GRADING KEY</h3>
                <div class="grid grid-cols-2 gap-2">
                    <p>75-100 = A1</p>
                    <p>70-74 = B2</p>
                    <p>65-69 = B3</p>
                    <p>60-64 = C4</p>
                    <p>55-59 = C5</p>
                    <p>50-54 = C6</p>
                    <p>45-49 = D7</p>
                    <p>40-44 = E8</p>
                    <p>0-39 = F9</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-2 text-xs bg-gray-50 break-inside-avoid">
            <div class="border border-gray-300 p-4 rounded-lg shadow-sm">
                <h3 class="font-bold text-blue-800 border-b-2 border-blue-800 pb-2 mb-3 uppercase">Teacher's Comment</h3>
                <p class="text-gray-900">{{ $termReport->class_teacher_comment ?? 'No comment available' }}</p>
            </div>

            <div class="border border-gray-300 p-4 rounded-lg shadow-sm">
                <h3 class="font-bold text-blue-800 border-b-2 border-blue-800 pb-2 mb-3 uppercase">Principal's Comment</h3>
                <p class="text-gray-900">{{ $termReport->principal_comment ?? 'No comment available' }}</p>
            </div>

            <div class="border border-gray-300 p-2 px-4 rounded-lg shadow-sm col-span-2">
                <h3 class="font-bold text-blue-800 border-b-2 border-blue-800 pb-2 mb-3 uppercase">Important Information</h3>
                <p class="text-gray-900">{{ $termReport->general_announcement ?? 'No announcement provided.' }}</p>
                <p><span class="font-semibold text-gray-700">Resumption Date:</span>
                    <span class="text-gray-900">
                        @if ($termReport->resumption_date)
                            {{ \Carbon\Carbon::parse($termReport->resumption_date)->format('d/m/Y') }}
                        @else
                            To be announced
                        @endif
                    </span>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-6 text-xs break-inside-avoid">
            <div class="text-center">
                <div class="border-b border-black w-32 mx-auto mb-2"></div>
                <p class="font-semibold">Class Teacher</p>
            </div>
            <div class="text-center">
                <div class="border-t border-black w-32 mx-auto mb-2"></div>
                <p class="font-semibold">Principal</p>
            </div>
        </div>

        <div class="text-center text-2xs text-gray-400 mt-4">
            <p>Official document - Generated on {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>

</html>