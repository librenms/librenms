@extends('layouts.librenmsv1')

@section('title', __('service.title'))

@section('content')
<div x-data="{ 'showModal': false }">
    <x-panel body-class="!tw-p-0">
        <x-slot name="title">
            <x-submenu title="{{ __('service.title') }}" :menu="$menu" :device-id="$device->device_id" current-tab="services" :selected="$view">
                <div class="pull-right"><a x-on:click="showModal=true">
                        <i class="fa-solid fa-plus" style="color:green" aria-hidden="true"></i> {{ __('service.add') }}</a>
                </div>
            </x-submenu>
        </x-slot>

        @isset($services)
            <table class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>
                        <div class="col-sm-1"><span class="device-services-page">{{ __('service.fields.service_type') }}</span></div>
                        <div class="col-sm-2">{{ __('service.fields.service_name') }} / {{ __('service.fields.service_ip') }}</div>
                        <div class="col-sm-2">{{ __('service.fields.service_desc') }}</div>
                        <div class="col-sm-4">{{ __('service.fields.service_message') }}</div>
                        <div class="col-sm-2">{{ __('service.fields.service_changed') }}</div>
                        <div class="col-sm-1"></div>
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($services as $service)
                    <tr id="row_{{ $service->service_id }}" x-data="{service_status: {{ $service->service_status }}}">
                        <td class="col-sm-12">
                            <div class="col-sm-1">
                                <span class="alert-status" x-bind:class="{'label-danger': service_status === 2, 'label-warning': service_status === 1, 'label-success': service_status === 0, 'label-info': service_status < 0 || service_status > 2}">
                                    <span class="device-services-page">{{ $service->service_type }}</span>
                                </span>
                            </div>
                            <div class="col-sm-2 text-muted">
                                <div>{{ $service->service_name }}</div>
                                <div>{{ $service->service_ip ?: $device->overwrite_ip ?: $device->hostname }}</div>
                            </div>
                            <div class="col-sm-2 text-muted">{{ $service->service_desc }}</div>
                            <div class="col-sm-4">{!! nl2br(e($service->service_message)) !!}</div>
                            <div class="col-sm-2 text-muted">{{ \LibreNMS\Util\Time::formatInterval(time() - $service->service_changed, 'short') }}</div>
                            <div class="col-sm-1">
                                <div class="tw-flex tw-flex-nowrap tw-flex-row-reverse">
                                    <button type='button' class='btn btn-primary btn-sm' aria-label='Edit' data-toggle='modal' data-target='#create-service' data-service_id='{$service->service_id}' name='edit-service'><i class='fa fa-pencil' aria-hidden='true'></i></button>
                                    <button type='button' class='btn btn-danger btn-sm tw-mr-1' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-service_id='{$service->service_id}' name='delete-service'><i class='fa fa-trash' aria-hidden='true'></i></button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @if($view == 'graphs')
                        @foreach($service->service_ds ?? [] as $type => $unit)
                            <tr><td>
                                <div class="col-sm-12">
                                    <div>{{ __('service.graph', ['ds' => $type]) }} @if($unit) ({{ $unit }}) @endif</div>
                                    <x-graph-row type="service_graph" :device="$device" :vars="['id' => $service->service_id, 'ds' => $type]" columns="responsive"></x-graph-row>
                                </div>
                            </td></tr>
                        @endforeach
                    @endif
                @endforeach
                </tbody>
            </table>
        @else
            <div class="device-services-page-no-service">No Services</div>
        @endisset
    </x-panel>

    <x-modal x-model="showModal" max-width="5xl">
        <x-panel title="{{ __('service.add') }}" class="!tw-mb-0" x-on:service-saved="showModal=false; console.log('saved');">
            @include('service.form', ['device_id' => $device->device_id ?? null])
        </x-panel>
    </x-modal>

</div>
@endsection
