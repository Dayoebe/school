<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Result</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @media print {
      @page { size: A4; margin: 1cm; }
    }
  </style>
</head>
<body class="text-[13px] p-6 font-sans relative bg-white text-gray-900">

  <!-- Watermark -->
  <div class="absolute top-1/2 left-1/4 text-[5rem] opacity-5 -rotate-45 -z-10 whitespace-nowrap">
    {{ strtoupper(config('app.name', 'Elites Int’l College')) }}
  </div>

  <!-- Header -->
  <div class="flex items-center border-b border-gray-400 pb-4 mb-6">
    <img src="{{ asset('img/logo.png') }}" alt="Logo" class="h-20 w-20 object-contain mr-4">
    <div class="text-center flex-1 leading-tight">
      <h1 class="text-xl font-bold uppercase">Elites International College, Awka</h1>
      <p class="text-xs uppercase tracking-wide">To create a brighter future</p>
      <p class="text-xs">Email: elitesinternationalcollege@gmail.com | Tel: 08066025508</p>
      <p class="font-semibold text-base mt-2 uppercase">SSS Two - First Term Academic Report</p>
    </div>
  </div>

  <!-- Student Info -->
  <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-2 gap-x-6 mb-6">
    <div><strong>Student Name:</strong> {{ $studentRecord->user->name }}</div>
    <div><strong>Admission No:</strong> {{ $studentRecord->admission_no }}</div>
    <div><strong>Class:</strong> {{ $studentRecord->myClass->name }}</div>
    <div><strong>Term:</strong> {{ $semesterName }}</div>
    <div><strong>Times Present:</strong> {{ $studentRecord->present }}</div>
    <div><strong>Times Absent:</strong> {{ $studentRecord->absent }}</div>
    <div><strong>Total Marks Obtainable:</strong> {{ $maxTotalScore }}</div>
    <div><strong>Total Scored:</strong> {{ $totalScore }}</div>
    <div><strong>Average:</strong> {{ round($totalScore / count($subjects), 2) }}%</div>
    <div><strong>Class Position:</strong> {{ $classPosition }}</div>
  </div>

  <!-- Subjects Table -->
  <div class="overflow-x-auto mb-6">
    <table class="w-full border border-gray-300 text-sm">
      <thead class="bg-blue-100 text-gray-800">
        <tr>
          <th class="border px-2 py-1">Subject</th>
          <th class="border px-2 py-1">Test</th>
          <th class="border px-2 py-1">Exam</th>
          <th class="border px-2 py-1">Total</th>
          <th class="border px-2 py-1">Grade</th>
          <th class="border px-2 py-1">Remark</th>
        </tr>
      </thead>
      <tbody>
        @foreach($subjects as $subject)
        <tr class="hover:bg-gray-50">
          <td class="border px-2 py-1">{{ $subject->name }}</td>
          <td class="border px-2 py-1 text-center">{{ $results[$subject->id]['test_score'] }}</td>
          <td class="border px-2 py-1 text-center">{{ $results[$subject->id]['exam_score'] }}</td>
          <td class="border px-2 py-1 text-center">{{ $results[$subject->id]['total_score'] }}</td>
          <td class="border px-2 py-1 text-center">{{ $results[$subject->id]['grade'] }}</td>
          <td class="border px-2 py-1 text-center">{{ $results[$subject->id]['comment'] }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- Domains Section -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm mb-6">
    <div>
      <h4 class="font-semibold mb-1 underline">Psychomotor Domain</h4>
      <ul>
        <li>Handwriting: 4</li>
        <li>Verbal Fluency: 4</li>
        <li>Game/Sports: 4</li>
        <li>Handling Tools: 4</li>
      </ul>
    </div>
    <div>
      <h4 class="font-semibold mb-1 underline">Affective Domain</h4>
      <ul>
        <li>Punctuality: 4</li>
        <li>Neatness: 4</li>
        <li>Politeness: 4</li>
        <li>Leadership: 4</li>
      </ul>
    </div>
    <div>
      <h4 class="font-semibold mb-1 underline">Co-curricular Activities</h4>
      <ul>
        <li>Athletics: 4</li>
        <li>Volley Ball: 4</li>
        <li>Table Tennis: 4</li>
      </ul>
    </div>
  </div>

  <!-- Summary -->
  <div class="mb-6 text-sm space-y-1">
    <p><strong>Total Subjects Offered:</strong> {{ count($subjects) }}</p>
    <p><strong>Subjects Passed:</strong> {{ $subjectsPassed }}</p>
    <p><strong>Net Score:</strong> {{ $totalScore }} / {{ $maxTotalScore }}</p>
    <p><strong>Result:</strong>
      @if($totalScore < ($maxTotalScore / 3))
        <span class="text-red-600 font-semibold">FAILED</span>
      @else
        <span class="text-green-600 font-semibold">PASSED</span>
      @endif
    </p>
    
  </div>

  <!-- Grading Key -->
  <div class="text-sm mb-8">
    <h4 class="font-semibold underline mb-1">KEY TO GRADE</h4>
    <ul class="grid grid-cols-2 sm:grid-cols-3 gap-y-1 list-disc list-inside">
      <li>0–39 = F9 (FAIL)</li>
      <li>40–44 = E8 (PASS)</li>
      <li>45–49 = D7</li>
      <li>50–54 = C6 (CREDIT)</li>
      <li>55–59 = C5 (CREDIT)</li>
      <li>60–64 = C4 (CREDIT)</li>
      <li>65–69 = B3 (VERY GOOD)</li>
      <li>70–74 = B2</li>
      <li>75–100 = A1 (DISTINCTION)</li>
    </ul>
  </div>



  

  <!-- Signature -->
  <div class="flex justify-between mt-10 text-center">
    <div>
      <p>________________________</p>
      <p class="mt-1">Class Teacher</p>
    </div>
    <div>
      <p>________________________</p>
      <p class="mt-1">Principal</p>
    </div>
  </div>

  <script>
    window.onload = function () {
      window.print();
    };
  </script>
</body>
</html>
