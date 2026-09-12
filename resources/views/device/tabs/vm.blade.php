@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        @isset($data['submenu'])
            <x-submenu :title="$title" :menu="$data['submenu']" :device-id="$device_id" :current-tab="$current_tab" :selected="$vars" />
        @endisset

        <div class="tw:flex tw:items-center tw:justify-between tw:pb-4 tw:-mt-4 tw:gap-4">
            <div class="tw:flex-1">
                <x-filter name="device.vminfo" :fields="$data['filterFields']" :initial="$data['filter']" :reload="true"/>
            </div>
            <x-table-export
                :export-route="route('table.vminfo.export')"
                :params="['device_id' => $device->device_id]"
                :filter="$data['filter']"
                :page="$data['vms']->currentPage()"
                :per-page="$data['perPage']"
                class="tw:shrink-0"
            />
        </div>

        @include('vminfo.includes.table', [
            'vms' => $data['vms'],
            'showDevice' => false,
            'perPage' => $data['perPage'],
        ])
    </x-device.page>
@endsection
