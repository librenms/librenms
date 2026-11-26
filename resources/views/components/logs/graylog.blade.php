@props([
    'device' => null,
    'deviceFilter' => true,
    'stream' => '',
    'range' => 0,
    'loglevel' => null,
])

@php
    $ranges = [
        0 => __('Search all time'),
        300 => __('Search last 5 minutes'),
        900 => __('Search last 15 minutes'),
        1800 => __('Search last 30 minutes'),
        3600 => __('Search last 1 hour'),
        7200 => __('Search last 2 hours'),
        28800 => __('Search last 8 hours'),
        86400 => __('Search last 1 day'),
        172800 => __('Search last 2 days'),
        432000 => __('Search last 5 days'),
        604800 => __('Search last 7 days'),
        1209600 => __('Search last 14 days'),
        2592000 => __('Search last 30 days'),
    ];
@endphp

<x-panel title="{{ __('Graylog') }}">
    <x-slot:table>
        <div class="table-responsive">
            <table id="graylog" class="table table-hover table-condensed graylog" data-url="{{ route('table.graylog') }}">
                <thead>
                <tr>
                    <th data-column-id="severity" data-sortable="false"></th>
                    <th data-column-id="origin">{{ __('Origin') }}</th>
                    <th data-column-id="timestamp" data-formatter="browserTime">{{ __('Timestamp') }}</th>
                    <th data-column-id="level">{{ __('Level') }}</th>
                    <th data-column-id="source">{{ __('Source') }}</th>
                    <th data-column-id="message" data-sortable="false">{{ __('Message') }}</th>
                    <th data-column-id="facility">{{ __('Facility') }}</th>
                </tr>
                </thead>
            </table>
        </div>
    </x-slot:table>
</x-panel>

<template id="graylog-filter">
    <form method="GET" class="form-inline pull-left tw:flex tw:flex-wrap tw:items-center tw:gap-2" role="form">
        <div class="form-group">
            <select name="stream" id="graylog-stream" class="form-control"></select>
        </div>
        @if($deviceFilter)
            <div class="form-group">
                <select name="device" id="graylog-device" class="form-control"></select>
            </div>
        @endif
        <div class="form-group">
            <select name="loglevel" class="form-control">
                <option value="">{{ __('Log Level') }}</option>
                @foreach(range(0, 7) as $level)
                    <option value="{{ $level }}" @selected($loglevel === $level)>({{ $level }}) {{ __("syslog.severity.$level") }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <select name="range" class="form-control">
                @foreach($ranges as $seconds => $text)
                    <option value="{{ $seconds }}" @selected($range === $seconds)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-default">{{ __('Filter') }}</button>
    </form>
</template>

@push('scripts')
<script>
    $("#graylog").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        formatters: {
            browserTime: function (column, row) {
                @config('graylog.timezone')
                    return row.timestamp;
                @else
                    return moment.parseZone(row.timestamp).local().format("YYYY-MM-DD HH:mm:ss");
                @endconfig
            }
        },
        post: function () {
            return {
                stream: @js($stream),
                device: @js($device?->device_id),
                range: @js($range),
                loglevel: @js($loglevel),
            };
        }
    });

    $('#graylog-header .actionBar').prepend(document.getElementById('graylog-filter').content.cloneNode(true));

    init_select2("#graylog-stream", "graylog-streams", {}, @js($stream), @js(__('All Streams')));
    @if($deviceFilter)
        init_select2("#graylog-device", "device", {limit: 100}, @js($device ? ['id' => $device->device_id, 'text' => $device->display] : null), @js(__('All Devices')));
    @endif
</script>
@endpush
