@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('MPLS')">
        <x-device.routing-tabs :device="$device" tab="mpls" />

        <x-option-bar name="{{ __('MPLS') }}" :options="$mpls_options" :selected="$view" />

        <x-panel>
            <x-routing.mpls-table :view="$view" :items="$items" />
        </x-panel>
    </x-device.page>
@endsection
