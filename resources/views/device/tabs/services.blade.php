@extends('device.index')

@section('tab')
    <div x-data="{ showAddServiceModal: false}">
        <x-panel body-class="!tw-p-0">
            <x-slot name="title">
                <div class="tw-flex tw-justify-between">
                    <x-submenu title="{{ __('service.title') }}" :menu="$menu" :current="$view" />
                    <a x-on:click="showAddServiceModal=true">
                        <i class="fa-solid fa-plus" style="color:green" aria-hidden="true"></i>
                        {{ __('service.add') }}
                    </a>
                </div>
            </x-slot>

            @include('service.table')
        </x-panel>

        <x-modal x-model="showAddServiceModal" max-width="5xl" id="service-add-modal">
            <x-panel title="{{ __('service.add') }}" class="!tw-mb-0" x-on:service-saved="showAddServiceModal=false; location.reload();">
                @include('service.form', ['device_id' => $device->device_id ?? null])
            </x-panel>
        </x-modal>
    </div>
@endsection
