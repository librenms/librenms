@extends('layouts.librenmsv1')

@section('title', __('Syslog'))

@section('content')
<div class="container-fluid">
    <x-logs.syslog filter-name="syslog" :filter="$filter" :filter-fields="$filterFields" :from="$from" :to="$to" />
</div>
@endsection
