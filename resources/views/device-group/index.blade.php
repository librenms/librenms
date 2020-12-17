@extends('layouts.librenmsv1')

@section('title', __('Device Groups'))

@section('content')
    <div class="container-fluid">
        <x-panel id="manage-device-groups-panel">
            <x-slot name="title">
                <i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> @lang('Device Groups')
            </x-slot>

            <div class="row">
                <div class="col-md-12">
                    <a type="button" class="btn btn-primary" href="{{ route('device-groups.create') }}">
                        <i class="fa fa-plus"></i> @lang('New Device Group')
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table id="manage-device-groups-table" class="table table-condensed table-hover">
                    <thead>
                    <tr>
                        <th>@lang('Name')</th>
                        <th>@lang('Description')</th>
                        <th>@lang('Type')</th>
                        <th>@lang('Devices')</th>
                        <th>@lang('Pattern')</th>
                        <th>@lang('Actions')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($device_groups as $device_group)
                        <tr id="row_{{ $device_group->id }}">
                            <td>{{ $device_group->name }}</td>
                            <td>{{ $device_group->desc }}</td>
                            <td>{{ __(ucfirst($device_group->type)) }}</td>
                            <td>
                                <a href="{{ url("/devices/group=$device_group->id") }}">{{ $device_group->devices_count }}</a>
                            </td>
                            <td>{{ $device_group->type == 'dynamic' ? $device_group->getParser()->toSql(false) : '' }}</td>
                            <td>
                                <button type="button" title="@lang('Rediscover all Devices of Device Group')" class="btn btn-warning btn-sm" aria-label="@lang('Rediscover Group')"
                                        onclick="discover_dg(this, '{{ $device_group->id }}')">
                                    <i
                                        class="fa fa-retweet" aria-hidden="true"></i></button>
                                <a type="button" title="@lang('edit Device Group')" class="btn btn-primary btn-sm" aria-label="@lang('Edit')"
                                   href="{{ route('device-groups.edit', $device_group->id) }}">
                                    <i class="fa fa-pencil" aria-hidden="true"></i></a>
                                <button type="button" class="btn btn-danger btn-sm" title="@lang('delete Device Group')" aria-label="@lang('Delete')"
                                        onclick="delete_dg(this, '{{ $device_group->name }}', '{{ route('device-groups.destroy', $device_group->id) }}')">
                                    <i
                                        class="fa fa-trash" aria-hidden="true"></i></button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-panel>

        <x-panel id="unmanaged-devices-panel" title="{{ __('Ungrouped Devices') }} ({{ $ungrouped_devices->count() }})">
            <div class="table-responsive">
                <table id="ungrouped-devices-table" class="table table-condensed table-hover">
                    <thead>
                    <tr>
                        <th style="width:32px">@lang('Vendor')</th>
                        <th>@lang('Device')</th>
                        <th>@lang('Platform')</th>
                        <th>@lang('Operating System')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($ungrouped_devices as $device)
                        <tr id="row_{{ $device->device_id }}">
                            <td><img alt="{{ $device->os }}" src="{{ asset($device->icon) }}" width="32px" height="32px" title="{{ $device->os }}"></td>
                            <td>@deviceLink($device)<br />{{ $device->sysName }}</td>
                            <td>{{ $device->hardware }}</td>
                            <td>{{ $device->os }} {{ $device->version }} @if($device->features) ({{ $device->features }}) @endif </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-panel>
    </div>
@endsection

@section('scripts')
    <script>
        function delete_dg(button, name, url) {
            var index = button.parentNode.parentNode.rowIndex;

            if (confirm('@lang('Are you sure you want to delete ')' + name + '?')) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    success: function (msg) {
                        document.getElementById("manage-device-groups-table").deleteRow(index);
                        toastr.success(msg);
                    },
                    error: function () {
                        toastr.error('@lang('The device group could not be deleted')');
                    }
                });
            }

            return false;
        }
        function discover_dg(button, id) {
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: { type: "rediscover-device", device_group_id: id },
                dataType: "json",
                success: function(data){
                    if(data['status'] == 'ok') {
                        toastr.success(data['message']);
                    } else {
                        toastr.error(data['message']);
                    }
                },
                error:function(){
                    toastr.error('An error occured setting this device Group to be rediscovered');
                }
            });
        }
    </script>
@endsection

@section('css')
    <style>
        .table-responsive {
            padding-top: 16px
        }
    </style>
@endsection
