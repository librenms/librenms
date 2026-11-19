@extends('layouts.librenmsv1')

@section('title', __('Syslog'))

@section('content')
<div class="container-fluid">
    @if (session('syslogCleared'))
        <div class="alert alert-success" role="status">{{ __('Syslog cleared.') }}</div>
    @endif
    <x-logs.syslog filter-name="syslog" :filter="$filter" :filter-fields="$filterFields" :from="$from" :to="$to" />
</div>
@endsection
