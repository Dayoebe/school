@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('exams.index'), 'text' => 'Exams'],
    ['href' => route('exam-papers.index', $exam), 'text' => 'Exam Papers'],
    ['href' => route('exam-papers.create', $exam), 'text' => 'Upload Paper', 'active'],
]])

@section('title', __('Upload Exam Paper'))
@section('page_heading', __('Upload Exam Paper'))

@section('content')
    @include('livewire.exams.pages.exam-paper._form')
@endsection
