@extends('layouts.librenmsv1')

@section('title', __('Apps'))

@section('content')
<x-device.page :device="$device">
    @if($selected)
        <x-option-bar :name="__('Apps')" :options="$appOptions" :selected="$selected->app_id" />

        @include('apps.device-content')
    @else
        <x-panel><em>{{ __('No applications found.') }}</em></x-panel>
    @endif
</x-device.page>
@endsection
