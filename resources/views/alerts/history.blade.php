@extends('layouts.librenmsv1')

@section('title', __('Alert History'))

@section('content')
    <div class="container-fluid">
        @include('alerts.partials.history-table', [
            'title' => __('Alert History'),
            'filterName' => 'alert-log',
        ])
    </div>
@endsection
