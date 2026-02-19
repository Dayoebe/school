@extends('layouts.app', ['mode' => 'public'])

@section('title', '405 Method Not Allowed')

@section('content')
    @include('errors.partials.error-page', [
        'code' => '405',
        'heading' => 'Method Not Allowed',
        'message' => 'The HTTP method used for this request is not supported for this route.'
    ])
@endsection
