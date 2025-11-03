@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" subtitle="{{ __('Eventlog') }}">
        <x-device.log-tabs :device="$device" tab="eventlog" />

        <x-panel title="{{ __('Eventlog') }}">
            <div class="table-responsive">
                <table id="eventlog" class="table table-hover table-condensed table-striped">
                    <thead>
                    <tr>
                        <th data-column-id="datetime" data-order="desc">Timestamp</th>
                        <th data-column-id="type">Type</th>
                        <th data-column-id="device_id">Hostname</th>
                        <th data-column-id="message">Message</th>
                        <th data-column-id="username">User</th>
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
    <script>
        var eventlog_grid = $("#eventlog").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            post: function () {
                return {
                    device: {{ $device->device_id }},
                    eventtype: @json($eventtype),
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
                '<option value=$device->device_id>" . $device->displayName() . "</option>' +
                '</select>' +
                '</div>&nbsp;&nbsp;&nbsp;&nbsp;' +
            @endif

            '<div class="form-group"><label><strong>Type&nbsp;&nbsp;</strong></label>' +
            '<select name="eventtype" id="eventtype" class="form-control input-sm">' +
                '<option value="">All types</option>' +
                '<option value=\"' + @json($eventtype) + '\">' + @json($eventtype) + '</option>' +
            '</select>' +
            '</div>&nbsp;&nbsp;' +
            '<button type="submit" class="btn btn-default">Filter</button>' +
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
        }, @json($eventtype));
    </script>
@endsection
