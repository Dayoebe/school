@extends('layouts.app', ['mode' => 'public'])

@section('title', '400 Bad Request')

@section('content')
    @include('errors.partials.error-page', [
        'code' => '400',
        'heading' => 'Bad Request',
        'message' => 'The request could not be understood by the server. Please refresh and try again.'
    ])
@endsection
