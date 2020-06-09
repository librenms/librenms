@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">@lang('Widget title')</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Custom title')" value="{{ $title }}">
    </div>
    <div class="form-group">
        <label for="device-{{ $id }}" class="control-label">@lang('Device')</label>
        <select class="form-control" id="device-{{ $id }}" name="device" data-placeholder="@lang('All devices')">
            @if($device)
                <option value="{{ $device->device_id }}">{{ $device->displayName() }}</option>
            @endif
        </select>
    </div>
    <div class="form-group">
        <label for="eventtype-{{ $id }}" class="control-label">@lang('Event type')</label>
        <select class="form-control" id="eventtype-{{ $id }}" name="eventtype" data-placeholder="@lang('All types')" data-tags="true">
            @if($eventtype)
                <option value="{{ $eventtype }}">{{ $eventtype }}</option>
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
@endsection

@section('javascript')
    <script type="text/javascript">
        init_select2('#device-{{ $id }}', 'device', {}, '{{ $device ? $device->device_id : '' }}');
        init_select2('#device_group-{{ $id }}', 'device-group', {});
        init_select2('#eventtype-{{ $id }}', 'eventlog', function(params) {
            return {
                field: "type",
                device: $('#device-{{ $id }}').val(),
                term: params.term,
                page: params.page || 1
            }
        }, '{{ $eventtype ?: "" }}');
    </script>
@endsection
