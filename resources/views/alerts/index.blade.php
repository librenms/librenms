@extends('layouts.librenmsv1')

@section('title', __('Faults'))

@section('content')
    <div class="container-fluid">
        @include('alerts.partials.faults-table', [
            'title' => __('Faults'),
            'filterName' => 'faults',
            'alertId' => $alert_id ?: null,
        ])
    </div>
@endsection
