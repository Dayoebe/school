<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Class Results - {{ $class->name }} - {{ $academicYear->name }} - {{ $semester->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Base styles for A4 page and print adjustments */
        @page {
            size: A4;
            margin: 1cm;
            /* Give a consistent margin on all sides for the page */
        }

        @media print {
            body {
                margin: 0;
                /* Reset body margin, as @page handles it */
                padding: 0;
                /* Reset body padding */
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }

            .print-container {
                width: 100%;
                height: 27.7cm;
                /* A4 height (29.7cm) - 2cm (1cm top + 1cm bottom page margin) */
                box-sizing: border-box;
                display: flex;
                flex-direction: column;
                position: relative;
                border: 2px solid #000;
                /* Border around each student's report */
                padding: 0.5rem;
                /* Inner padding for the content within the border */
            }

            .flex-header,
            .flex-footer {
                flex-shrink: 0;
                /* Prevent header/footer from shrinking */
            }

            .flex-content {
                flex-grow: 1;
                /* Main content area will grow to fill available space */
                overflow: hidden;
                /* Hide overflow if table is too big, but table height should handle this */
                display: flex;
                flex-direction: column;
            }

            .results-table-wrapper {
                flex-grow: 1;
                /* Table wrapper grows to fill flex-content */
            }

            .results-table {
                height: 100%;
                /* Make the table itself fill its wrapper */
                table-layout: fixed;
                /* Ensures columns are sized correctly */
            }

            .results-table th,
            .results-table td {
                padding: 2px 4px !important;
                font-size: 10px !important;
                line-height: 1.3 !important;
                text-align: center;
                vertical-align: top;
                /* Align content to top for better spacing */
            }

            .results-table th:first-child,
            .results-table td:first-child {
                text-align: left;
                width: 20%;
                /* Allocate more width for subject name */
            }

            .results-table td:last-child {
                text-align: left;
                width: 20%;
                /* Allocate more width for remark */
            }

            /* Compact other sections to ensure they don't take up too much space */
            .compact-section {
                font-size: 10px !important;
                line-height: 1.3 !important;
            }

            .compact-section div,
            .compact-section p,
            .compact-section li {
                margin-bottom: 0 !important;
                padding: 1px 0 !important;
            }

            .compact-box {
                padding: 0.4rem !important;
            }
        }

        /* Grade colors */
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
        <button onclick="window.print()"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-lg flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z"
                    clip-rule="evenodd" />
            </svg>
            Print All Reports
        </button>
    </div>

    @foreach ($studentsData as $data)
        <div class="page-break">
            <div class="print-container mx-auto print-border">
                {{-- Watermark and School Logo on Report --}}
                <div class="absolute inset-0 flex items-center justify-center opacity-10 -z-100">
                    <img src="{{ asset('img/logo.png') }}" alt="Watermark" class="h-auto w-auto rotate-0">
                </div>
                <div class="absolute bottom-4 right-4 opacity-80 -z-100">
                    <div
                        class="border-2 border-red-500 rounded-full h-20 w-20 flex items-center justify-center text-red-500 font-bold text-xs text-center p-2">
                        ELITES <br>INTERNATIONAL <br> COLLEGE
                    </div>
                </div>

                {{-- Header Section --}}
                <div class="flex-header">
                    <div class="flex items-center border-b border-blue-900 pb-1 mb-1">
                        <img src="{{ asset('img/logo.png') }}" alt="School Logo" class="h-20 w-20 object-contain ml-10">
                        <div class="text-center flex-1">
                            <h1 class="text-2xl font-bold text-blue-900 uppercase leading-tight">ELITES INTERNATIONAL
                                COLLEGE, AWKA</h1>
                            <p class="text-sm uppercase tracking-wide text-gray-700">To Create a Brighter Future</p>
                            <p class="text-xs text-gray-600">Email: elitesinternationalcollege@gmail.com | Tel:
                                08066025508</p>
                            <p class="font-semibold text-sm mt-1 uppercase text-blue-900">
                                {{ strtoupper($data['studentRecord']->myClass->name) }} -
                                {{ strtoupper($data['semesterName']) }} {{ $data['academicYearName'] }} ACADEMIC REPORT
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-1 text-sm compact-section">
                        <div class="space-y-0.5">
                            <div><span class="font-bold uppercase">Name:</span>
                                {{ strtoupper($data['studentRecord']->user->name) }}</div>
                            <div><span class="font-bold">Class:</span> {{ $data['studentRecord']->myClass->name }}</div>
                            <div><span class="font-bold">Gender:</span>
                                {{ ucfirst($data['studentRecord']->user->gender) }}</div>
                            <div><span class="font-bold">Attendance:</span>
                                Present:__{{ $data['studentRecord']->present }}
                                Absent:__{{ $data['studentRecord']->absent }}</div>
                            <div><span class="font-bold">Number of students in class:</span>
                                {{ $data['totalStudents'] ?? 'N/A' }}</div>
                        </div>
                        <div class="space-y-0.5">
                            <div><span class="font-bold">Admission No:</span>
                                {{ $data['studentRecord']->admission_number ?? 'N/A' }}</div>
                            <div><span class="font-bold">Academic Year:</span> {{ $data['academicYearName'] }}</div>
                            <div><span class="font-bold">Term:</span> {{ $data['semesterName'] }}</div>
                            <div>
                                <span class="font-bold">Date of Birth:</span>
                                @if ($data['studentRecord']->user->birthday)
                                    {{ \Carbon\Carbon::parse($data['studentRecord']->user->birthday)->format('d/m/Y') }}
                                @else
                                    N/A
                                @endif
                            </div>
                            <div><span class="font-bold">Class Position:</span> {{ $data['classPosition'] ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="flex justify-center items-start">
                            <img src="{{ $data['studentRecord']->user->profile_photo_url }}" alt="Student Photo"
                                class="h-20 w-20 object-cover border border-white rounded-md shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Main Content Area (Results Table) --}}
                <div class="flex-content">
                    <div class="results-table-wrapper">
                        <table class="w-full border border-gray-300 results-table">
                            <colgroup>
                                <col style="width: 23%;">
                                <col style="width: 8%;">
                                <col style="width: 8%;">
                                <col style="width: 8%;">
                                <col style="width: 8%;">
                                <col style="width: 10%;">
                                <col style="width: 8%;">
                                <col style="width: 8%;">
                                <col style="width: 8%;">
                                <col style="width: 8%;">
                                <col style="width: 13%;"> <!-- Make comment column wider -->
                            </colgroup>
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
                                    <th class="border p-1">Highest</th>
                                    <th class="border p-1">Lowest</th>
                                    <th class="border p-1 text-left">Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['subjects']->sortBy('name') as $subject)
                                    @php
                                        $result = $data['results'][$subject->id] ?? null;
                                        $stats = $data['subjectStats'][$subject->id] ?? null;
                                        $gradeClass = $result ? 'grade-' . substr($result['grade'], 0, 1) : '';
                                        $isHighest = $result && $stats && $result['total_score'] == $stats['highest'];
                                        $isLowest = $result && $stats && $result['total_score'] == $stats['lowest'];
                                    @endphp
                                    <tr class="{{ $gradeClass }}">
                                        <td class="border p-1">{{ $subject->name }}</td>
                                        <td class="border p-1">{{ $result['ca1_score'] ?? '-' }}</td>
                                        <td class="border p-1">{{ $result['ca2_score'] ?? '-' }}</td>
                                        <td class="border p-1">{{ $result['ca3_score'] ?? '-' }}</td>
                                        <td class="border p-1">{{ $result['ca4_score'] ?? '-' }}</td>
                                        <td class="border p-1">{{ $result['exam_score'] ?? '-' }}</td>
                                        <td
                                            class="border p-1 {{ $isHighest ? 'font-bold text-green-600' : '' }} {{ $isLowest ? 'font-bold text-red-600' : '' }}">
                                            {{ $result['total_score'] ?? '-' }}</td>
                                        <td class="border p-1">{{ $result['grade'] ?? '-' }}</td>
                                        <td class="border p-1 bg-green-50">{{ $stats['highest'] ?? '-' }}</td>
                                        <td class="border p-1 bg-red-50">{{ $stats['lowest'] ?? '-' }}</td>
                                        <td class="border p-1 text-left">{{ $result['comment'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Footer Section --}}
                <div class="flex-footer mt-3">
                    <div class="grid grid-cols-3 gap-2 mb-2 text-xs break-inside-avoid">
                        <div class="border border-gray-300 p-1 rounded-lg shadow-sm">
                            <h3 class="font-bold text-blue-900 border-b border-gray-300 pb-1 mb-1">PSYCHOMOTOR</h3>
                            <ul class="space-y-1">
                                <li>Handwriting: 4</li>
                                <li>Verbal Fluency: 4</li>
                                <li>Game/Sports: 4</li>
                                <li>Handling Tools: 4</li>
                            </ul>
                        </div>

                        <div class="border border-gray-300 p-1 rounded-lg shadow-sm">
                            <h3 class="font-bold text-blue-900 border-b border-gray-300 pb-1 mb-1">AFFECTIVE</h3>
                            <ul class="space-y-1">
                                <li>Punctuality: 4</li>
                                <li>Neatness: 4</li>
                                <li>Politeness: 4</li>
                                <li>Leadership: 4</li>
                            </ul>
                        </div>

                        <div class="border border-gray-300 p-1 rounded-lg shadow-sm">
                            <h3 class="font-bold text-blue-900 border-b border-gray-300 pb-1 mb-1">CO-CURRICULAR</h3>
                            <ul class="space-y-1">
                                <li>Athletics: 4</li>
                                <li>Football: 4</li>
                                <li>Volley Ball: 4</li>
                                <li>Table Tennis: 4</li>
                            </ul>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-2 text-xs break-inside-avoid">
                        <div class="border border-gray-300 p-1 rounded-lg shadow-sm">
                            <h3 class="font-bold text-blue-900 border-b border-gray-300 pb-1 mb-1">ACADEMIC SUMMARY</h3>
                            <div class="space-y-1">
                                @php
                                    $subjectCount = count($data['subjects']);
                                    $passedCount = $data['subjectsPassed'] ?? 0;
                                    $totalScore = $data['totalScore'] ?? 0;
                                    $maxPossible = $subjectCount * 100;
                                    $percentage = $data['percentage'] ?? 0;
                                    $passed = $percentage >= 40;
                                @endphp

                                <p><span class="font-semibold">Subjects:</span> {{ $subjectCount }} (Passed:
                                    {{ $passedCount }})</p>
                                <p><span class="font-semibold">Total Score:</span>
                                    {{ $totalScore }}/{{ $maxPossible }}</p>
                                <p><span class="font-semibold">Percentage:</span> {{ $percentage }}%</p>
                                <p><span class="font-semibold">Position:</span> {{ $data['classPosition'] }} out of
                                    {{ $data['totalStudents'] }}</p>
                                <p><span class="font-semibold">Result:</span>
                                    @if ($subjectCount > 0)
                                        <span class="font-bold {{ $passed ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $passed ? 'PASSED' : 'FAILED' }}
                                        </span>
                                    @else
                                        <span class="font-bold text-gray-600">N/A</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="border border-gray-300 p-1 rounded-lg shadow-sm">
                            <h3 class="font-bold text-blue-900 border-b border-gray-300 pb-1 mb-1">GRADING KEY</h3>
                            <div class="grid grid-cols-3 gap-2">
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

                    {{-- Comments Section --}}
                    <div class="grid grid-cols-2 gap-4 p-3 text-xs bg-gray-50">
                        @php
                            $percentage = $data['percentage'] ?? 0;
                            $termReport = $data['termReport'];
                            // Dynamic comments are now passed as finalTeacherComment and finalPrincipalComment from controller
                        @endphp
                        <div class="border border-gray-300 p-2 rounded-lg shadow-sm">
                            <h3 class="font-bold text-blue-800 border-b-2 border-blue-800 pb-1 mb-1 uppercase">
                                Teacher's
                                Comment</h3>
                            <p class="text-gray-900">{{ $data['finalTeacherComment'] }}</p>
                        </div>
                        <div class="border border-gray-300 p-2 rounded-lg shadow-sm">
                            <h3 class="font-bold text-blue-800 border-b-2 border-blue-800 pb-1 mb-1 uppercase">
                                Principal's
                                Comment</h3>
                            <p class="text-gray-900">{{ $data['finalPrincipalComment'] }}</p>
                        </div>
                    </div>

                    {{-- Important Information --}}
                    <div class="border border-gray-300 p-2 px-4 text-xs rounded-lg shadow-sm col-span-2 mt-2">
                        <h3 class="font-bold text-blue-800 border-b-2 border-blue-800 pb-1 mb-1 uppercase">Important
                            Information</h3>
                        <p class="text-gray-900">
                            {{ $termReport->general_announcement ?? 'No announcement provided.' }}</p>
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

                {{-- Signatures --}}
                <div class="grid grid-cols-2 gap-4 mt-6 text-xs break-inside-avoid">
                    <div class="text-center">
                        <div class="border-b border-black w-40 mx-auto mb-1"></div>
                        <p class="font-semibold">Class Teacher</p>
                    </div>
                    <div class="text-center">
                        <div class="border-t border-black w-40 mx-auto mb-1"></div>
                        <p class="font-semibold">Principal</p>
                    </div>
                </div>

                {{-- Generated Date --}}
                <div class="text-center text-2xs text-gray-400 mt-2">
                    <p>Official document - Generated on {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    @endforeach
    {{-- <script>
        // Automatically trigger print dialog when the page loads
        window.onload = function() {
            window.print();
        };
    </script> --}}
</body>

</html>
