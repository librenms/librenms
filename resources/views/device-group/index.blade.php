@extends('layouts.librenmsv1')

@section('title', __('Device Groups'))

@section('content')
    <div class="container-fluid">
        <x-panel id="manage-device-groups-panel">
            <x-slot name="title">
                <i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> {{ __('Device Groups') }}
            </x-slot>

            <div class="row">
                <div class="col-md-12">
                    @can('create', App\Models\DeviceGroup::class)
                    <a type="button" class="btn btn-primary" href="{{ route('device-groups.create') }}">
                        <i class="fa fa-plus"></i> {{ __('New Device Group') }}
                    </a>
                    @endcan
                    <a type="button" class="btn btn-default" href="{{ route('devices', ['filter' => ['groups.id' => ['is_empty' => 1]]]) }}">
                        <i class="fas fa-border-none"></i> {{ __('View Ungrouped Devices') }}
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table id="manage-device-groups-table" class="table table-condensed table-hover" x-data="{
                    rediscover(url) {
                        axios.post(url)
                            .then((response) => toastr.success(response.data.message))
                            .catch((error) => toastr.error(error.response?.data?.message || @js(__('An error occurred setting this device group to be rediscovered'))));
                    },
                    destroy(button, url) {
                        if (! confirm(@js(__('Are you sure you want to delete ')) + button.dataset.groupName + '?')) {
                            return;
                        }

                        axios.delete(url)
                            .then((response) => {
                                button.closest('tr').remove();
                                toastr.success(response.data.message);
                            })
                            .catch((error) => toastr.error(error.response?.data?.message || @js(__('The device group could not be deleted'))));
                    },
                }">
                    <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Devices') }}</th>
                        <th>{{ __('Ports') }}</th>
                        <th>{{ __('Pattern') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($device_groups as $device_group)
                        <tr id="row_{{ $device_group->id }}">
                            <td>{{ $device_group->name }}</td>
                            <td>{{ $device_group->desc }}</td>
                            <td>{{ __(ucfirst($device_group->type)) }}</td>
                            <td>
                                <a href="{{ route('devices', ['filter' => ['groups.id' => ['eq' => $device_group->id]]]) }}">{{ $device_group->devices_count }}</a>
                            </td>
                            <td>
                                <a href="{{ route('ports', ['filter' => ['device.groups.id' => ['eq' => $device_group->id]]]) }}">View</a>
                            </td>
                            <td>{{ $device_group->type == 'dynamic' ? $device_group->getParser()->toSql(false) : '' }}</td>
                            <td>
                                @can('device.update')
                                <button type="button" title="{{ __('Rediscover all Devices of Device Group') }}" class="btn btn-warning btn-sm" aria-label="{{ __('Rediscover Group') }}"
                                        x-on:click="rediscover(@js(route('device-groups.rediscover', $device_group->id)))">
                                    <i
                                        class="fa fa-retweet" aria-hidden="true"></i></button>
                                @endcan
                                @can('update', $device_group)
                                <a type="button" title="{{ __('edit Device Group') }}" class="btn btn-primary btn-sm" aria-label="{{ __('Edit') }}"
                                   href="{{ route('device-groups.edit', $device_group->id) }}">
                                    <i class="fa fa-pencil" aria-hidden="true"></i></a>
                                @endcan
                                @can('delete', $device_group)
                                <button type="button" class="btn btn-danger btn-sm" title="{{ __('delete Device Group') }}" aria-label="{{ __('Delete') }}"
                                        data-group-name="{{ $device_group->name }}"
                                        x-on:click="destroy($el, @js(route('device-groups.destroy', $device_group->id)))">
                                    <i class="fa fa-trash" aria-hidden="true"></i></button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-panel>
    </div>
@endsection

@section('css')
    <style>
        .table-responsive {
            padding-top: 16px
        }
    </style>
@endsection
