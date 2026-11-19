@extends('layouts.librenmsv1')

@section('title', __('Graylog'))

@section('content')
<div class="container-fluid">
    <x-logs.graylog :device="$device" :stream="$stream" :range="$range" :loglevel="$loglevel" />
</div>
@endsection
