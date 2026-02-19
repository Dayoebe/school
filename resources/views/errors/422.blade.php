@extends('layouts.app', ['mode' => 'public'])

@section('title', '422 Unprocessable Entity')

@section('content')
    @include('errors.partials.error-page', [
        'code' => '422',
        'heading' => 'Unprocessable Entity',
        'message' => 'The request was well-formed but could not be processed. Please check your input and retry.'
    ])
@endsection
