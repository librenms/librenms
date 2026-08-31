@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <div class="panel panel-default">
        <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div style="display: flex; align-items: center;">
                <h3 class="panel-title" style="margin-right: 15px;">{{ __('Services') }}</h3>
                <ul class="nav nav-pills" style="margin: 0;">
                    <li class="{{ $data['view'] === 'basic' ? 'active' : '' }}">
                        <a href="{{ route('device', ['device' => $device, 'tab' => 'services', 'vars' => 'view=basic']) }}">
                            {{ __('Basic') }}
                        </a>
                    </li>
                    <li class="{{ $data['view'] === 'details' ? 'active' : '' }}">
                        <a href="{{ route('device', ['device' => $device, 'tab' => 'services', 'vars' => 'view=details']) }}">
                            {{ __('Details') }}
                        </a>
                    </li>
                </ul>
            </div>
            @can('create', \App\Models\Service::class)
                <div>
                    <a data-toggle="modal" href="#create-service" class="btn btn-success btn-sm">
                        <i class="fa fa-plus" aria-hidden="true"></i> {{ __('Add Service') }}
                    </a>
                </div>
            @endcan
        </div>
        <div class="panel-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table table-hover table-condensed table-striped" style="margin-bottom: 0;">
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
                                <span class="label {{ $item['status_class'] }} text-nowrap">
                                    {{ $service->service_name ?: $service->service_type }}
                                </span>
                            </td>
                            <td class="col-sm-1 text-muted">{{ $service->service_type }}</td>
                            <td class="col-sm-1 text-muted">{!! nl2br(e($service->service_ip)) !!}</td>
                            <td class="col-sm-4">{!! nl2br(e(trim($service->service_message))) !!}</td>
                            <td class="col-sm-2 text-muted">{{ $service->service_desc }}</td>
                            <td class="col-sm-1 text-muted">{{ $item['last_changed'] }}</td>
                            <td class="col-sm-1 text-right">
                                <div class="btn-group btn-group-xs">
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
                                    <td colspan="7" style="padding: 10px; background-color: #fafafa;">
                                        <div class="row">
                                            <div class="col-md-12 text-center">
                                                <strong>{{ $graph['title'] }}</strong>
                                                <x-graph
                                                    :device="$device"
                                                    type="service_graph"
                                                    :id="$service->service_id"
                                                    :ds="$graph['ds']"
                                                />
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 20px;">
                                <em>{{ __('No services found for this device.') }}</em>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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
