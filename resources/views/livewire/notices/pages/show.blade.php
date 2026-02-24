@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('notices.index'), 'text' => 'Notices'],
    ['href' => route('notices.show', $notice), 'text' => 'View', 'active'],
]])

@section('title', __("Notice: {$notice->title}"))
@section('page_heading', __('Notice Details'))

@section('content')
    @livewire('notices.show-notice', ['notice' => $notice])
@endsection

