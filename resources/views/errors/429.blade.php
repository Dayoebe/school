@extends('layouts.app', ['mode' => 'public'])

@section('title', '429 Too Many Requests')

@section('content')
    @include('errors.partials.error-page', [
        'code' => '429',
        'heading' => 'Too Many Requests',
        'message' => 'You have made too many requests in a short time. Please wait and try again.'
    ])
@endsection
