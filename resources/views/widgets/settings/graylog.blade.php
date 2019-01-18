@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">@lang('Widget title')</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Default Title')" value="{{ $title }}">
    </div>

    <div class="form-group">
        <label for="stream-{{ $id }}" class="control-label">@lang('Stream')</label>
        <select class="form-control" id="stream-{{ $id }}" name="stream" data-placeholder="@lang('All Messages')">
            @if($stream)
                <option value="{{ $stream }}">{{ $stream }}</option>
            @endif
        </select>
    </div>

    <div class="form-group">
        <label for="device-{{ $id }}" class="control-label">@lang('Device')</label>
        <select class="form-control" id="device-{{ $id }}" name="device" data-placeholder="@lang('All Devices')">
            @if($device)
                <option value="{{ $device->device_id }}">{{ $device->displayName() }}</option>
            @endif
        </select>
    </div>

    <div class="form-group">
        <label for="limit-{{ $id }}" class="control-label">@lang('Page Size')</label>
        <input type="number" min="1" class="form-control" name="limit" id="limit-{{ $id }}" placeholder="@lang('Page Size')" value="{{ $limit }}">
    </div>

    <div class="form-group">
        <label for="range-{{ $id }}" class="control-label">@lang('Time Range')</label>
        <select name="range" id="range-{{ $id }}" class="form-control">
            <option value="0" @if($range == 0) selected @endif>@lang('Search all time')</option>
            <option value="300" @if($range == 300) selected @endif>@lang('Search last 5 minutes')</option>
            <option value="900" @if($range == 900) selected @endif>@lang('Search last 15 minutes')</option>
            <option value="1800" @if($range == 1800) selected @endif>@lang('Search last 30 minutes')</option>
            <option value="3600" @if($range == 3600) selected @endif>@lang('Search last 1 hour')</option>
            <option value="7200" @if($range == 7200) selected @endif>@lang('Search last 2 hours')</option>
            <option value="28800" @if($range == 28800) selected @endif>@lang('Search last 8 hours')</option>
            <option value="86400" @if($range == 86400) selected @endif>@lang('Search last 1 day')</option>
            <option value="172800" @if($range == 172800) selected @endif>@lang('Search last 2 days')</option>
            <option value="432000" @if($range == 432000) selected @endif>@lang('Search last 5 days')</option>
            <option value="604800" @if($range == 604800) selected @endif>@lang('Search last 7 days')</option>
            <option value="1209600" @if($range == 1209600) selected @endif>@lang('Search last 14 days')</option>
            <option value="2592000" @if($range == 2592000) selected @endif>@lang('Search last 30 days')</option>
        </select>
    </div>
@endsection

@section('javascript')
    <script type="application/javascript">
        init_select2('#device-{{ $id }}', 'device', {limit: 100}, '{{ $device ? $device->device_id : '' }}');
        init_select2('#stream-{{ $id }}', 'graylog-streams', {}, '{{ $stream }}');
    </script>
@endsection
