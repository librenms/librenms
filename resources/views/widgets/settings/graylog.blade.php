@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="stream-{{ $id }}" class="control-label">@lang('Device')</label>
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
            <option value="0" {{ $range == 0 ? 'selected' : '' }}>@lang('Search all time')</option>
            <option value="300" {{ $range == 300 ? 'selected' : '' }}>@lang('Search last 5 minutes')</option>
            <option value="900" {{ $range == 900 ? 'selected' : '' }}>@lang('Search last 15 minutes')</option>
            <option value="1800" {{ $range == 1800 ? 'selected' : '' }}>@lang('Search last 30 minutes')</option>
            <option value="3600" {{ $range == 3600 ? 'selected' : '' }}>@lang('Search last 1 hour')</option>
            <option value="7200" {{ $range == 7200 ? 'selected' : '' }}>@lang('Search last 2 hours')</option>
            <option value="28800" {{ $range == 28800 ? 'selected' : '' }}>@lang('Search last 8 hours')</option>
            <option value="86400" {{ $range == 86400 ? 'selected' : '' }}>@lang('Search last 1 day')</option>
            <option value="172800" {{ $range == 172800 ? 'selected' : '' }}>@lang('Search last 2 days')</option>
            <option value="432000" {{ $range == 432000 ? 'selected' : '' }}>@lang('Search last 5 days')</option>
            <option value="604800" {{ $range == 604800 ? 'selected' : '' }}>@lang('Search last 7 days')</option>
            <option value="1209600" {{ $range == 1209600 ? 'selected' : '' }}>@lang('Search last 14 days')</option>
            <option value="2592000" {{ $range == 2592000 ? 'selected' : '' }}>@lang('Search last 30 days')</option>
        </select>
    </div>
@endsection

@section('javascript')
    <script type="application/javascript">
        init_select2('#device-{{ $id }}', 'device', {limit: 100}, '{{ $device ? $device->device_id : 0 }}');
        init_select2('#stream-{{ $id }}', 'graylog-streams', {}, '{{ $stream }}');
    </script>
@endsection
