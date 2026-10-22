@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('CEF')">
        <x-device.routing-tabs :device="$device" tab="cef" />

        <x-option-bar name="CEF" :options="$cef_options" :selected="$view" />

        <x-panel>
            <x-routing.cef-table :cefs="$cefs" :graphs="$view === 'graphs'" />
        </x-panel>
    </x-device.page>
@endsection
