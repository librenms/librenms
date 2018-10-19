@extends('layouts.error')

@section('title')
    @lang('Whoops, the web server could not write required files to the filesystem.')
@endsection

@section('content')
    <h3>@lang('Running the following commands will fix the issue most of the time:')</h3>

    @foreach($commands as $command)
        <p>{{ $command }}</p>
    @endforeach
    <hr class="separator"/>

    <p>@lang("If that doesn't fix the issue. You can find how to get help at") <a href="https://docs.librenms.org/Support">https://docs.librenms.org/Support</a>.</p>
@endsection
