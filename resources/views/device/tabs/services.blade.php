@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-panel>
        <x-slot name="heading" class="tw:flex tw:items-center tw:justify-between tw:flex-wrap">
            <x-option-bar
                :name="__('Services')"
                :options="$data['options']"
                :selected="$data['view']"
                border="none"
                class="tw:inline-block"
            />
            @can('create', \App\Models\Service::class)
                <div>
                    <a data-toggle="modal" href="#create-service" class="btn btn-success btn-sm">
                        <i class="fa fa-plus" aria-hidden="true"></i> {{ __('Add Service') }}
                    </a>
                </div>
            @endcan
        </x-slot>

        <x-slot name="table">
            <div class="table-responsive">
                <table class="table table-hover table-condensed table-striped tw:mb-0">
                    <thead>
                        <tr>
                            <th class="col-sm-2">{{ __('Name') }}</th>
                            <th class="col-sm-1">{{ __('Check Type') }}</th>
                            <th class="col-sm-1">{{ __('Remote Host') }}</th>
                            <th class="col-sm-4">{{ __('Message') }}</th>
                            <th class="col-sm-2">{{ __('Description') }}</th>
                            <th class="col-sm-1">{{ __('Last Changed') }}</th>
                            <th class="col-sm-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($data['services'] as $item)
                        @php $service = $item['service']; @endphp
                        <tr id="row_{{ $service->service_id }}">
                            <td class="col-sm-2">
                                <span class="alert-status {{ $item['status_class'] }}">
                                    <span class="device-services-page text-nowrap">
                                        {{ $service->service_name ?: $service->service_type }}
                                    </span>
                                </span>
                            </td>
                            <td class="col-sm-1 text-muted">{{ $service->service_type }}</td>
                            <td class="col-sm-1 text-muted">{!! nl2br(e($service->service_ip)) !!}</td>
                            <td class="col-sm-4">{!! nl2br(e(trim($service->service_message))) !!}</td>
                            <td class="col-sm-2 text-muted">{{ $service->service_desc }}</td>
                            <td class="col-sm-1 text-muted">{{ $item['last_changed'] }}</td>
                            <td class="col-sm-1 text-right">
                                <div class="btn-group">
                                    @can('service.update')
                                        <button type="button" class="btn btn-primary btn-sm" aria-label="{{ __('Edit') }}" data-toggle="modal" data-target="#create-service" data-service_id="{{ $service->service_id }}" name="edit-service">
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </button>
                                    @endcan
                                    @can('service.delete')
                                        <button type="button" class="btn btn-danger btn-sm" aria-label="{{ __('Delete') }}" data-toggle="modal" data-target="#confirm-delete" data-service_id="{{ $service->service_id }}" name="delete-service">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @if($data['view'] === 'details' && !empty($item['graphs']))
                            @foreach($item['graphs'] as $graph)
                                <tr>
                                    <td colspan="7" class="tw:p-2.5 tw:bg-gray-50 tw:dark:bg-dark-gray-500">
                                        <x-graph-row
                                            :device="$device"
                                            type="service_graph"
                                            :title="$graph['title']"
                                            :vars="['id' => $service->service_id, 'ds' => $graph['ds']]"
                                            columns="responsive"
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center tw:p-5">
                                <em>{{ __('No services found for this device.') }}</em>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-slot>
    </x-panel>

    @php
        if (is_file(base_path('includes/html/modal/new_service.inc.php'))) {
            include base_path('includes/html/modal/new_service.inc.php');
        }
        if (is_file(base_path('includes/html/modal/delete_service.inc.php'))) {
            include base_path('includes/html/modal/delete_service.inc.php');
        }
    @endphp
</x-device.page>
@endsection
