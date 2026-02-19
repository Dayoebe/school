@extends('layouts.app', ['mode' => 'public'])

@section('title', '419 Page Expired')

@section('content')
    @include('errors.partials.error-page', [
        'code' => '419',
        'heading' => 'Page Expired',
        'message' => 'Your session has expired. Please refresh the page and try again.'
    ])
@endsection
