{{-- resources/views/livewire/results/upload-result-component.blade.php --}}
<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Upload Student Results</h2>
    {{-- form and logic here --}}
</div>



{{-- 
@extends('layouts.app')

@section('title', 'Upload Results')

@section('content')
<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Upload Student Results</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label class="block">Select Class</label>
            <input type="text" wire:model="selectedClass" class="border p-2 w-full" placeholder="Enter Class ID">
        </div>

        <div>
            <label class="block">Select Section</label>
            <input type="text" wire:model="selectedSection" class="border p-2 w-full" placeholder="Enter Section ID">
        </div>

        <div>
            <label class="block">Select Subject</label>
            <select wire:model="selectedSubject" class="border p-2 w-full">
                <option value="">Select Subject</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if($students)
        <form wire:submit.prevent="save">
            <table class="table-auto w-full border-collapse border border-gray-200 mb-4">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-4 py-2">Student Name</th>
                        <th class="border px-4 py-2">Test Score</th>
                        <th class="border px-4 py-2">Exam Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td class="border px-4 py-2">{{ $student->user->name ?? 'N/A' }}</td>
                            <td class="border px-4 py-2">
                                <input type="number" min="0" wire:model.lazy="scores.{{ $student->id }}.test_score" class="border p-2 w-full" />
                            </td>
                            <td class="border px-4 py-2">
                                <input type="number" min="0" wire:model.lazy="scores.{{ $student->id }}.exam_score" class="border p-2 w-full" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Submit Results
            </button>
        </form>
    @endif

    @if(session()->has('message'))
        <div class="mt-4 text-green-600">
            {{ session('message') }}
        </div>
    @endif
</div>

@endsection --}}


{{-- Another one -- --}}
{{-- 
<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">Upload Results for {{ $subject->name }}</h2>

    @if (session()->has('success'))
        <div class="p-4 mb-4 text-green-700 bg-green-100 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-4">
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2">Student</th>
                        <th class="p-2">Test Score (40%)</th>
                        <th class="p-2">Exam Score (60%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr class="border-b">
                            <td class="p-2">{{ $student->user->name }}</td>
                            <td class="p-2">
                                <input type="number" wire:model.lazy="scores.{{ $student->id }}.test_score"
                                    class="border rounded p-1 w-full" min="0" max="40" step="1">
                            </td>
                            <td class="p-2">
                                <input type="number" wire:model.lazy="scores.{{ $student->id }}.exam_score"
                                    class="border rounded p-1 w-full" min="0" max="60" step="1">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            Save Results
        </button>
    </form>
</div> --}}
