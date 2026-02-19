@extends('layouts.app', ['mode' => 'public'])

@section('title', '404 Not Found')

@section('content')
    @include('errors.partials.error-page', [
        'code' => '404',
        'heading' => 'Page Not Found',
        'message' => 'The page you are looking for does not exist or has been moved.'
    ])
@endsection
