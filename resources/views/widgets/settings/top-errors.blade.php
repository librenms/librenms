@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="interface_count-{{ $id }}" class="control-label">@lang('Number of interfaces'):</label>
        <input class="form-control" type="number" min="1" step="1" name="interface_count" id="interface_count-{{ $id }}" value="{{ $interface_count }}">
    </div>
    <div class="form-group">
        <label for="time_interval-{{ $id }}" class="control-label">@lang('Last polled (minutes)'):</label>
        <input class="form-control" type="number" min="1" step="1" name="time_interval" id="time_interval-{{ $id }}" value="{{ $time_interval }}">
    </div>
    <div class="form-group">
        <label for="interface_filter-{{ $id }}" class="control-label">@lang('Interface type'):</label>
        <select class="form-control" id="interface_filter-{{ $id }}" name="interface_filter"  data-placeholder="@lang('All Ports')">
        @if($interface_filter)
            <option value="{{ $interface_filter }}">{{ $interface_filter }}</option>
        @endif
        </select>
    </div>
    <div class="form-group">
        <label for="device_group-{{ $id }}" class="control-label">@lang('Device group')</label>
        <select class="form-control" name="device_group" id="device_group-{{ $id }}" data-placeholder="@lang('All Devices')">
            @if($device_group)
                <option value="{{ $device_group->id }}" selected>{{ $device_group->name }}</option>
            @endif
        </select>
    </div>
    <div class="form-group">
        <label for="port_group-{{ $id }}" class="control-label">@lang('Port group')</label>
        <select class="form-control" name="port_group" id="port_group-{{ $id }}" data-placeholder="@lang('All Ports')">
            @if($port_group)
                <option value="{{ $port_group->id }}" selected>{{ $port_group->name }}</option>
            @endif
        </select>
    </div>

@endsection

@section('javascript')
    <script type="text/javascript">
        init_select2('#interface_filter-{{ $id }}', 'port-field', {limit: 100, field: 'ifType'}, '{{ $interface_filter ?: '' }}');
        init_select2('#device_group-{{ $id }}', 'device-group', {});
        init_select2('#port_group-{{ $id }}', 'port-group', {});
    </script>
@endsection
