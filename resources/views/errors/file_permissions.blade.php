@extends('layouts.error')

@section('title')
    {{ __('Whoops, the web server could not write required files to the filesystem.') }}
@endsection

@section('content')
    <h3>{{ __('Running the following commands will fix the issue most of the time:') }}</h3>

    @foreach($commands as $command)
        <p>{{ $command }}</p>
    @endforeach
@endsection
