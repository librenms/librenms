@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        @include('vminfo.includes.table', [
            'vms' => $data['vms'],
            'filterName' => 'device.vminfo',
            'filterFields' => $data['filterFields'],
            'filter' => $data['filter'],
            'exportParams' => ['device_id' => $device->device_id],
            'showDevice' => false,
            'perPage' => $data['perPage'],
        ])
    </x-device.page>
@endsection
