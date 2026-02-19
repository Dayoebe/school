@extends('layouts.app', ['mode' => 'public'])

@section('title', '500 Internal Server Error')

@section('content')
    @include('errors.partials.error-page', [
        'code' => '500',
        'heading' => 'Internal Server Error',
        'message' => 'Something went wrong on our end. Please try again in a few moments.'
    ])
@endsection
