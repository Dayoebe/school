@extends('layouts.app', ['mode' => 'public'])

@section('title', '401 Unauthorized')

@section('content')
    @include('errors.partials.error-page', [
        'code' => '401',
        'heading' => 'Unauthorized',
        'message' => 'You need to sign in to access this page.'
    ])
@endsection
