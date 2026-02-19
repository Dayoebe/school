@extends('layouts.app', ['mode' => 'public'])

@section('title', '503 Service Unavailable')

@section('content')
    @include('errors.partials.error-page', [
        'code' => '503',
        'heading' => 'Service Unavailable',
        'message' => 'The service is temporarily unavailable due to maintenance. Please check back shortly.'
    ])
@endsection
