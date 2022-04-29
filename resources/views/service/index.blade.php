@extends('layouts.librenmsv1')

@section('title', __('service.title'))

@section('content')
<div x-data="{ 'showModal': false }">
    <x-panel title=""
    @foreach($devices as $device)
    <x-panel body-class="!tw-p-0">
        <x-slot name="title">

            <x-submenu title="{{ __('service.title') }}" :menu="$menu" :device-id="$device->device_id" current-tab="services" :selected="$view">
                <div class="pull-right"><a x-on:click="showModal=true">
                        <i class="fa-solid fa-plus" style="color:green" aria-hidden="true"></i> {{ __('service.add') }}</a>
                </div>
            </x-submenu>

            <x-submenu title="{{ __('service.status') }}" :menu="$menu" :device-id="$device->device_id" current-tab="services" :selected="$view">
                <div class="pull-right"><a x-on:click="showModal=true">
                        <i class="fa-solid fa-plus" style="color:green" aria-hidden="true"></i> {{ __('service.add') }}</a>
                </div>
            </x-submenu>
        </x-slot>

        @php($services = $device->services)
        @include('service.table')

    </x-panel>

    <x-modal x-model="showModal" max-width="5xl">
        <x-panel title="{{ __('service.add') }}" class="!tw-mb-0" x-on:service-saved="showModal=false; console.log('saved');">
            @include('service.form', ['device_id' => $device->device_id ?? null])
        </x-panel>
    </x-modal>

</div>
@endsection
