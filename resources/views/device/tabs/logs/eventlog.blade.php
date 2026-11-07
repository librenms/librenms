@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" subtitle="{{ __('Eventlog') }}">
        <x-device.log-tabs :device="$device" tab="eventlog" />

        <x-panel title="{{ __('Eventlog') }}">
            <div class="table-responsive">
                <table id="eventlog" class="table table-hover table-condensed table-striped">
                    <thead>
                    <tr>
                        <th data-column-id="datetime" data-order="desc">{{ __('Timestamp') }}</th>
                        <th data-column-id="type">{{ __('Type') }}</th>
                        <th data-column-id="device_id">{{ __('device.attributes.hostname') }}</th>
                        <th data-column-id="message">{{ __('Message') }}</th>
                        <th data-column-id="username">{{ __('User') }}</th>
                    </tr>
                    </thead>
                </table>
            </div>

            @if(! $filter_device)
                <input type="hidden" name="device" id="device" value="{{ $device->device_id }}">
            @endif
        </x-panel>
    </x-device.page>
@endsection

@section('scripts')
@php
    $reference_port = $reference
        ? \App\Models\Port::where('port_id', (int) $reference)
            ->where('device_id', $device->device_id)
            ->first()
        : null;
    $clear_reference_url = $reference
        ? route('device.eventlog', ['device' => $device->device_id] + ($eventtype !== '' ? ['eventtype' => $eventtype] : []))
        : null;
@endphp
<script>
    var eventlog_grid = $("#eventlog").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function () {
            return {
                device: {{ $device->device_id }},
                eventtype: @json($eventtype),
                reference: @json($reference ?: null),
            };
        },
        url: "{{ route('table.eventlog') }}"
    });

        $('.actionBar').append(
            '<div class="pull-left">' +
            '<form method="GET" action="" class="form-inline" role="form" id="result_form">' +

            @if($filter_device)
                '<div class="form-group">' +
                '<label><strong>Device&nbsp;&nbsp;</strong></label>' +
                '<select name="device" id="device" class="form-control">' +
                '<option value="">All Devices</option>' +
                '<option value="{{ $device->device_id }}">' + @json(e($device->display)) + '</option>' +
                '</select>' +
                '</div>&nbsp;&nbsp;&nbsp;&nbsp;' +
        @endif

        '<div class="form-group"><label><strong>Type&nbsp;&nbsp;</strong></label>' +
            '<select name="eventtype" id="eventtype" class="form-control input-sm">' +
                '<option value="">All types</option>' +
                '<option value=\"' + @json(e($eventtype)) + '\">' + @json(e($eventtype)) + '</option>' +
            '</select>' +
            '</div>&nbsp;&nbsp;' +
            '<button type="submit" class="btn btn-default">Filter</button>' +

            @if($reference)
            '<input type="hidden" name="reference" value="{{ (int) $reference }}">' +
            '&nbsp;&nbsp;' +
            '<div class="form-group"><label><strong>{{ $reference_port ? 'Port' : 'Reference' }}&nbsp;&nbsp;</strong></label>' +
            '<span class="form-control-static">' + @json($reference_port ? $reference_port->getLabel() : $reference) +
            ' <a href="' + @json($clear_reference_url) + '" title="Clear reference filter">&times;</a></span></div>' +
            @endif

            '</form>' +
            '</div>'
        );

        init_select2("select#device", "device", {limit: 100}, "{{ $device->device_id }}");
        init_select2("#eventtype", "eventlog", function(params) {
            return {
                field: "type",
                device: $('#device').val(),
                term: params.term,
                page: params.page || 1
            }
        }, @json($eventtype), 'All Types');
    </script>
@endsection
