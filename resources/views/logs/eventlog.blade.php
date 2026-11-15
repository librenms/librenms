@extends('layouts.librenmsv1')

@section('title', __('Eventlog'))

@section('content')
<div class="container-fluid">
    <x-logs.eventlog :device="$device" :eventtype="$eventtype" />
</div>
@endsection
