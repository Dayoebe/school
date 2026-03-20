@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('exams.index'), 'text' => 'Exams'],
    ['href' => route('exam-papers.index', $exam), 'text' => 'Exam Papers'],
    ['href' => route('exam-papers.edit', [$exam, $examPaper]), 'text' => 'Edit Paper', 'active'],
]])

@section('title', __('Edit Exam Paper'))
@section('page_heading', __('Edit Exam Paper'))

@section('content')
    @include('livewire.exams.pages.exam-paper._form')
@endsection
