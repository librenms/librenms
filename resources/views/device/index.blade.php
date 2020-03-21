@extends('layouts.librenmsv1')

@section('content')
    <div class="container-fluid">
        @include('device.header')

        @include('device.tabs')

        <div class="tab-content">
            @yield('tab')
        </div>
    </div>
@endsection
