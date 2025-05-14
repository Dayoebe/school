@extends('layouts.app', [
    'breadcrumbs' => [
        ['href' => route('dashboard'), 'text' => 'Dashboard'],
        ['href' => route('result'), 'text' => 'Results', 'active' => true],
    ]
])

@section('title', __('Results'))

@section('page_heading', __('Manage Student Results'))

@section('content')
    @livewire('result-page')
@endsection
