@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-option-bar name="{{ __('Routing') }}" :options="$data['options']" :selected="$data['proto']" />

    @if(view()->exists('device.tabs.routing.' . $data['proto']))
        @include('device.tabs.routing.' . $data['proto'])
    @endif
</x-device.page>
@endsection
