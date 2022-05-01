@extends('layouts.librenmsv1')

@section('title', __('service.title'))

@section('content')
<div class="container-fluid" x-data="{ 'showModal': false }">

    <x-panel>
        <x-slot name="title">
            <div class="tw-flex tw-justify-between">
            <x-submenu title="{{ __('service.title') }}" :menu="$view_menu" :selected="$view" />

            <x-submenu title="{{ __('service.status') }}" :menu="$state_menu" />
            </div>
        </x-slot>

        @foreach($devices as $device)
            <x-panel body-class="!tw-p-0">
                <x-slot name="header">
                    {!! \LibreNMS\Util\Url::deviceLink($device) !!}
                </x-slot>
                @php($services = $device->services)
                @include('service.table')
            </x-panel>
        @endforeach
    </x-panel>

    <x-modal x-model="showModal" max-width="5xl" id="service-add-modal">
        <x-panel title="{{ __('service.add') }}" class="!tw-mb-0" x-on:service-saved="showModal=false; console.log('saved');">
            @include('service.form', ['device_id' => $device->device_id ?? null])
        </x-panel>
    </x-modal>

</div>
@endsection
