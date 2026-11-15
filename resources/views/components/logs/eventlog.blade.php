@props([
    'device' => null,
    'deviceFilter' => true,
    'eventtype' => '',
    'port' => null,
])

<x-panel title="{{ __('Eventlog') }}">
    <x-slot:table>
        <div class="table-responsive">
            <table id="eventlog" class="table table-hover table-condensed table-striped" data-url="{{ route('table.eventlog') }}">
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
    </x-slot:table>
</x-panel>

<template id="eventlog-filter">
    <form method="GET" class="form-inline pull-left tw:flex tw:flex-wrap tw:items-center tw:gap-2" role="form">
        @if($deviceFilter)
            <div class="form-group">
                <label class="tw:mr-1" for="eventlog-device">{{ __('Device') }}</label>
                <select name="device" id="eventlog-device" class="form-control"></select>
            </div>
        @endif
        <div class="form-group">
            <label class="tw:mr-1" for="eventlog-type">{{ __('Type') }}</label>
            <select name="eventtype" id="eventlog-type" class="form-control input-sm"></select>
        </div>
        <button type="submit" class="btn btn-default">{{ __('Filter') }}</button>
        @if($port)
            <input type="hidden" name="port" value="{{ $port->port_id }}">
            <div class="form-group">
                <label class="tw:mr-1">{{ __('Port') }}</label>
                <span class="form-control-static">
                    {{ $port->getLabel() }}
                    <a href="{{ request()->fullUrlWithoutQuery('port') }}" title="{{ __('Clear port filter') }}">&times;</a>
                </span>
            </div>
        @endif
    </form>
</template>

@push('scripts')
<script>
    $("#eventlog").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function () {
            return {
                device: @js($device?->device_id),
                eventtype: @js($eventtype),
                port: @js($port?->port_id),
            };
        }
    });

    $('#eventlog-header .actionBar').prepend(document.getElementById('eventlog-filter').content.cloneNode(true));

    @if($deviceFilter)
        init_select2("#eventlog-device", "device", {limit: 100}, @js($device ? ['id' => $device->device_id, 'text' => $device->display] : null), @js(__('All Devices')));
    @endif
    init_select2("#eventlog-type", "eventlog", function (params) {
        return {
            field: "type",
            device: @if($deviceFilter) $('#eventlog-device').val() @else @js($device?->device_id) @endif,
            term: params.term,
            page: params.page || 1
        };
    }, @js($eventtype), @js(__('All Types')));
</script>
@endpush
