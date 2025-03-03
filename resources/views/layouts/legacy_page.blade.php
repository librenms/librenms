@extends('layouts.librenmsv1')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                {!! $content !!}
            </div>
        </div>
    </div>
    <x-refresh-timer :refresh="$refresh"></x-refresh-timer>

    @config('enable_footer')
    <nav class="navbar navbar-default {{ $navbar }} navbar-fixed-bottom">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h5>Powered by <a href="{{ \LibreNMS\Config::get('project_home') }}" target="_blank" rel="noopener" class="red">{{ \LibreNMS\Config::get('project_name') }}</a>.</h5>
                </div>
            </div>
        </div>
    </nav>
    @endconfig
@endsection
