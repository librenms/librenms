@extends('layouts.librenmsv1')

@section('content')
    <div class="container-fluid{{ ! empty($legacy_host) ? ' ' . $legacy_host : '' }}">
        <div class="row">
            <div class="col-md-12">
                {!! $content !!}
            </div>
        </div>
    </div>
    <x-refresh-timer :refresh="$refresh"></x-refresh-timer>
@endsection
