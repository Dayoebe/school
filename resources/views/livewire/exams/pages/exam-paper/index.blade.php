@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('exams.index'), 'text' => 'Exams'],
    ['href' => route('exam-papers.index', $exam), 'text' => $exam->isUploadArchive() ? 'Uploaded Exams' : 'Exam Papers', 'active'],
]])

@section('title', __($exam->isUploadArchive() ? 'Uploaded Exams' : 'Exam Papers'))
@section('page_heading', __($exam->isUploadArchive() ? 'Uploaded Exams' : 'Exam Papers'))

@section('content')
    @php
        $isUploadArchive = $exam->isUploadArchive();
        $examHeading = $isUploadArchive ? (($exam->semester?->name ?? 'Current Term') . ' Uploaded Exams') : $exam->name;
    @endphp

    <div class="card mb-4">
        <div class="card-body">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $examHeading }}</h2>
                    <p class="text-sm text-gray-600">Term: {{ $exam->semester?->name ?? 'N/A' }} | Session: {{ $exam->semester?->academicYear?->name ?? 'N/A' }}</p>
                    @if ($isUploadArchive)
                        <p class="text-sm text-gray-600">Current-term archive for printable class subject uploads.</p>
                    @else
                        <p class="text-sm text-gray-600">Window: {{ $exam->start_date }} to {{ $exam->stop_date }}</p>
                    @endif
                </div>
                @can('create', App\Models\ExamPaper::class)
                    <a href="{{ route('exam-papers.create', $exam) }}" class="btn btn-primary">
                        Upload Exam
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $isUploadArchive ? 'Uploaded Exams' : 'Uploaded Papers' }}</h3>
        </div>
        <div class="card-body overflow-auto">
            @if(session('success'))
                <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if($papers->isEmpty())
                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-gray-600">
                    {{ $isUploadArchive ? 'No exams have been uploaded for the active term yet.' : 'No exam papers have been uploaded for this exam yet.' }}
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="px-4 py-3">Class</th>
                            <th class="px-4 py-3">Subject</th>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Format</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Uploaded By</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($papers as $paper)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $paper->myClass?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $paper->subject?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $paper->title }}</div>
                                    @if($paper->instructions)
                                        <div class="mt-1 text-xs text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags($paper->instructions), 90) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    <div>Typed: {{ $paper->typed_content ? 'Yes' : 'No' }}</div>
                                    <div>File: {{ $paper->attachment_path ? 'Yes' : 'No' }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <div>
                                        <span class="inline-flex rounded-full px-2 py-1 {{ $paper->is_published ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $paper->is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <span class="inline-flex rounded-full px-2 py-1 {{ $paper->is_sealed ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $paper->is_sealed ? 'Sealed' : 'Editable' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $paper->uploader?->name ?? 'System' }}
                                    <div>{{ optional($paper->created_at)->format('M d, Y') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        @can('update', $paper)
                                            <a href="{{ route('exam-papers.edit', [$exam, $paper]) }}" class="btn btn-outline-primary btn-xs">
                                                Edit
                                            </a>
                                        @endcan

                                        <a href="{{ route('exam-papers.print', $paper) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-xs">
                                            Print
                                        </a>

                                        @if($paper->attachment_url)
                                            <a href="{{ $paper->attachment_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-info btn-xs">
                                                File
                                            </a>
                                        @endif

                                        @can('publish', $paper)
                                            <form method="POST" action="{{ route('exam-papers.publish', [$exam, $paper]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-xs">
                                                    {{ $paper->is_published ? 'Unpublish' : 'Publish' }}
                                                </button>
                                            </form>
                                        @endcan

                                        @can('seal', $paper)
                                            <form method="POST" action="{{ route('exam-papers.seal', [$exam, $paper]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-dark btn-xs">
                                                    {{ $paper->is_sealed ? 'Unseal' : 'Seal' }}
                                                </button>
                                            </form>
                                        @endcan

                                        @can('delete', $paper)
                                            <form method="POST" action="{{ route('exam-papers.destroy', [$exam, $paper]) }}" onsubmit="return confirm('Delete this exam paper?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-xs">
                                                    Delete
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
