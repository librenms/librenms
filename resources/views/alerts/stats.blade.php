@extends('layouts.librenmsv1')

@section('title', __('Alert Stats'))

@section('content')
    <div class="container-fluid">
        @include('alerts.partials.stats-chart')
    </div>
@endsection
