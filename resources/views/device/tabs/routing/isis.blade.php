@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('ISIS')">
        <x-device.routing-tabs :device="$device" tab="isis" />

        <x-panel>
            <x-routing.isis-table :adjacencies="$adjacencies" />
        </x-panel>
    </x-device.page>
@endsection
