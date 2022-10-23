@extends('layouts.librenmsv1')

@section('title', __('service.title'))

@section('content')
<div class="container-fluid">
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
</div>
@endsection
