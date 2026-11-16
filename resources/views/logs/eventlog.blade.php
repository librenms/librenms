@extends('layouts.librenmsv1')

@section('title', __('Eventlog'))

@section('content')
<div class="container-fluid">
    <x-logs.eventlog filter-name="eventlog" :filter="$filter" :filter-fields="$filterFields" />
</div>
@endsection
