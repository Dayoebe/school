<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Annual Academic Report - {{ $data['class']->name }} ({{ $data['academicYear']->name }})</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                transform: scale(0.9);
                transform-origin: top left;
                width: 111%;
            }

            .no-print {
                display: none !important;
            }

            .print-border {
                border: 1px solid #000;
                min-height: 27.7cm;
                box-sizing: border-box;
            }

            .break-inside-avoid {
                break-inside: avoid;
            }

            .grade-A { background-color: #e6ffec !important; }
            .grade-B { background-color: #f0fff4 !important; }
            .grade-C { background-color: #fffaf0 !important; }
            .grade-D, .grade-E { background-color: #fff5f5 !important; }
            .grade-F { background-color: #ffebee !important; }
        }
    </style>
</head>
<body class="bg-white text-gray-900 font-sans relative">
    <div class="no-print fixed top-4 right-4 z-50">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-lg flex items-center gap-2">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>

    <div class="print-container mx-auto p-4 print-border">
        <!-- Watermark -->
        <div class="absolute inset-0 flex items-center justify-center opacity-10 -z-10">
            <img src="{{ asset('img/logo.png') }}" alt="Watermark" class="h-auto w-auto rotate-0">
        </div>

        <!-- School Header -->
        <div class="flex items-center border-b border-blue-900 pb-2 mb-2 break-inside-avoid">
            <img src="{{ asset('img/logo.png') }}" alt="School Logo" class="h-20 w-20 object-contain ml-10">
            <div class="text-center flex-1">
                <h1 class="text-3xl font-bold text-blue-900 uppercase leading-tight">ELITES INTERNATIONAL COLLEGE, AWKA</h1>
                <p class="text-sm uppercase tracking-wide text-gray-700">To Create a Brighter Future</p>
                <p class="text-xs text-gray-600">Email: elitesinternationalcollege@gmail.com | Tel: 08066025508</p>
                <p class="font-semibold text-sm mt-1 uppercase text-blue-900">
                    {{ strtoupper($data['class']->name) }} - ANNUAL {{ $data['academicYear']->name }} ACADEMIC REPORT
                </p>
            </div>
        </div>

        <!-- Class Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 bg-blue-50 p-4 rounded-lg">
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-800">{{ $data['stats']['total_students'] }}</p>
                <p class="text-sm text-gray-700">Total Students</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-800">{{ $data['subjects']->count() }}</p>
                <p class="text-sm text-gray-700">Subjects</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-800">{{ $data['semesters']->count() }}</p>
                <p class="text-sm text-gray-700">Terms</p>
            </div>
        </div>

        <!-- Performance Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div class="bg-white p-3 rounded-lg shadow text-center border border-blue-200">
                <p class="text-xl font-bold text-blue-700">
                    {{ number_format(collect($data['annualReports'])->avg('average_percentage'), 1) }}%
                </p>
                <p class="text-xs text-gray-600">Class Average</p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow text-center border border-green-200">
                <p class="text-xl font-bold text-green-700">
                    {{ collect($data['annualReports'])->max('average_percentage') }}%
                </p>
                <p class="text-xs text-gray-600">Highest Score</p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow text-center border border-yellow-200">
                <p class="text-xl font-bold text-yellow-700">
                    {{ collect($data['annualReports'])->min('average_percentage') }}%
                </p>
                <p class="text-xs text-gray-600">Lowest Score</p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow text-center border border-purple-200">
                <p class="text-xl font-bold text-purple-700">
                    {{ count(array_filter($data['annualReports'], fn($r) => $r['average_percentage'] >= 50)) }}
                </p>
                <p class="text-xs text-gray-600">Passing Students</p>
            </div>
        </div>

        <!-- Top Performers -->
        <div class="mb-4">
            <h3 class="text-lg font-bold text-blue-800 border-b border-blue-200 pb-1 mb-2">
                <i class="fas fa-trophy text-yellow-500 mr-2"></i> Top 5 Performers
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                @foreach(array_slice($data['annualReports'], 0, 5) as $top)
                <div class="bg-green-50 p-2 rounded border border-green-200">
                    <p class="font-bold text-green-800">#{{ $top['rank'] }} {{ $top['student']->user->name }}</p>
                    <p class="text-sm">{{ $top['average_percentage'] }}%</p>
                    <p class="text-xs text-gray-600">{{ $top['student']->admission_number }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Results Table -->
        <div class="overflow-x-auto mb-4">
            <table class="w-full border border-gray-300 text-xs">
                <thead class="bg-blue-900 text-white">
                    <tr>
                        <th class="border p-1">Rank</th>
                        <th class="border p-1 text-left">Student</th>
                        <th class="border p-1">Adm No</th>
                        @foreach($data['subjects'] as $subject)
                        <th class="border p-1">{{ $subject->name }}</th>
                        @endforeach
                        <th class="border p-1">Total</th>
                        <th class="border p-1">Avg %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['annualReports'] as $report)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                        <td class="border p-1 text-center">{{ $report['rank'] }}</td>
                        <td class="border p-1">{{ $report['student']->user->name }}</td>
                        <td class="border p-1 text-center">{{ $report['student']->admission_number }}</td>
                        @foreach($data['subjects'] as $subject)
                        <td class="border p-1 text-center">
                            {{ $report['subject_totals'][$subject->id]['total'] ?? '-' }}
                        </td>
                        @endforeach
                        <td class="border p-1 text-center font-bold">{{ $report['grand_total'] }}</td>
                        <td class="border p-1 text-center font-bold 
                            {{ $report['average_percentage'] >= 80 ? 'text-green-600' : 
                               ($report['average_percentage'] >= 50 ? 'text-blue-600' : 'text-red-600') }}">
                            {{ $report['average_percentage'] }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Subject Performance -->
        <div class="mb-4">
            <h3 class="text-lg font-bold text-blue-800 border-b border-blue-200 pb-1 mb-2">
                <i class="fas fa-chart-bar text-blue-500 mr-2"></i> Subject Performance Summary
            </h3>
            <table class="w-full border border-gray-300 text-xs">
                <thead class="bg-blue-900 text-white">
                    <tr>
                        <th class="border p-1 text-left">Subject</th>
                        <th class="border p-1">Avg Score</th>
                        <th class="border p-1">Highest</th>
                        <th class="border p-1">Lowest</th>
                        <th class="border p-1">Pass Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['subjects'] as $subject)
                    @php
                        $totals = array_column(array_column($data['annualReports'], 'subject_totals'), $subject->id);
                        $averages = array_map(fn($item) => $item['average'] ?? 0, $totals);
                        $subjectAvg = count($averages) ? number_format(array_sum($averages) / count($averages), 1) : 0;
                        $subjectMax = count($averages) ? max($averages) : 0;
                        $subjectMin = count($averages) ? min($averages) : 0;
                        $passCount = count(array_filter($averages, fn($avg) => $avg >= 50));
                        $passRate = count($averages) ? number_format(($passCount / count($averages)) * 100, 1) : 0;
                    @endphp
                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                        <td class="border p-1">{{ $subject->name }}</td>
                        <td class="border p-1 text-center">{{ $subjectAvg }}</td>
                        <td class="border p-1 text-center bg-green-50">{{ $subjectMax }}</td>
                        <td class="border p-1 text-center bg-red-50">{{ $subjectMin }}</td>
                        <td class="border p-1 text-center">{{ $passRate }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Grading Key -->
        <div class="border border-gray-300 p-4 rounded-lg shadow-sm mb-4">
            <h3 class="font-bold text-blue-900 border-b border-blue-900 pb-2 mb-2">GRADING KEY</h3>
            <div class="grid grid-cols-3 gap-2 text-xs">
                <p>75-100 = A1 (Excellent)</p>
                <p>70-74 = B2 (Very Good)</p>
                <p>65-69 = B3 (Good)</p>
                <p>60-64 = C4 (Credit)</p>
                <p>55-59 = C5 (Credit)</p>
                <p>50-54 = C6 (Credit)</p>
                <p>45-49 = D7 (Pass)</p>
                <p>40-44 = E8 (Pass)</p>
                <p>0-39 = F9 (Fail)</p>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 bg-gray-50 p-4 rounded-lg">
            <div class="border border-gray-300 p-4 rounded-lg shadow-sm">
                <h3 class="font-bold text-blue-800 border-b-2 border-blue-800 pb-2 mb-3 uppercase">
                    <i class="fas fa-user-tie mr-2"></i>Class Teacher's Comment
                </h3>
                <p class="text-gray-900 italic">Overall class performance analysis and remarks...</p>
            </div>

            <div class="border border-gray-300 p-4 rounded-lg shadow-sm">
                <h3 class="font-bold text-blue-800 border-b-2 border-blue-800 pb-2 mb-3 uppercase">
                    <i class="fas fa-user-shield mr-2"></i>Principal's Comment
                </h3>
                <p class="text-gray-900 italic">Annual academic summary and recommendations...</p>
            </div>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-4 mt-6 text-xs">
            <div class="text-center">
                <div class="border-b border-black w-32 mx-auto mb-2"></div>
                <p class="font-semibold">Class Teacher</p>
            </div>
            <div class="text-center">
                <div class="border-t border-black w-32 mx-auto mb-2"></div>
                <p class="font-semibold">Principal</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-2xs text-gray-400 mt-4">
            <p>Official document - Generated on {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <script>
        // Alpine.js for any interactive elements (though mostly for print)
        document.addEventListener('alpine:init', () => {
            Alpine.data('pdfViewer', () => ({
                printReport() {
                    window.print();
                }
            }));
        });
    </script>
</body>
</html>