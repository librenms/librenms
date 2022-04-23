@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">{{ __('Widget title') }}</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="{{ __('Default Title') }}" value="{{ $title }}">
    </div>

    <div class="form-group">
        <label for="stream-{{ $id }}" class="control-label">{{ __('Stream') }}</label>
        <select class="form-control" id="stream-{{ $id }}" name="stream" data-placeholder="{{ __('All Messages') }}">
            @if($stream)
                <option value="{{ $stream }}">{{ $stream }}</option>
            @endif
        </select>
    </div>

    <div class="form-group">
        <label for="device-{{ $id }}" class="control-label">{{ __('Device') }}</label>
        <select class="form-control" id="device-{{ $id }}" name="device" data-placeholder="{{ __('All Devices') }}">
            @if($device)
                <option value="{{ $device->device_id }}">{{ $device->displayName() }}</option>
            @endif
        </select>
    </div>

    <div class="form-group">
        <label for="limit-{{ $id }}" class="control-label">{{ __('Page Size') }}</label>
        <input type="number" min="1" class="form-control" name="limit" id="limit-{{ $id }}" placeholder="{{ __('Page Size') }}" value="{{ $limit }}">
    </div>

    <div class="form-group">
        <label for="loglevel-{{ $id }}" class="control-label">{{ __('Log Level') }}</label>
        <select name="loglevel" id="loglevel-{{ $id }}" class="form-control">
            <option value="" disabled @if($loglevel == null) selected @endif>{{ __('Minimum log Level') }}</option>
            <option value="0" @if($loglevel === 0) selected @endif>(0) {{ __('syslog.severity.0') }}</option>
            <option value="1" @if($loglevel == 1) selected @endif>(1) {{ __('syslog.severity.1') }}</option>
            <option value="2" @if($loglevel == 2) selected @endif>(2) {{ __('syslog.severity.2') }}</option>
            <option value="3" @if($loglevel == 3) selected @endif>(3) {{ __('syslog.severity.3') }}</option>
            <option value="4" @if($loglevel == 4) selected @endif>(4) {{ __('syslog.severity.4') }}</option>
            <option value="5" @if($loglevel == 5) selected @endif>(5) {{ __('syslog.severity.5') }}</option>
            <option value="6" @if($loglevel == 6) selected @endif>(6) {{ __('syslog.severity.6') }}</option>
            <option value="7" @if($loglevel == 7) selected @endif>(7) {{ __('syslog.severity.7') }}</option>
        </select>
    </div>


    <div class="form-group">
        <label for="range-{{ $id }}" class="control-label">{{ __('Time Range') }}</label>
        <select name="range" id="range-{{ $id }}" class="form-control">
            <option value="0" @if($range == 0) selected @endif>{{ __('Search all time') }}</option>
            <option value="300" @if($range == 300) selected @endif>{{ __('Search last 5 minutes') }}</option>
            <option value="900" @if($range == 900) selected @endif>{{ __('Search last 15 minutes') }}</option>
            <option value="1800" @if($range == 1800) selected @endif>{{ __('Search last 30 minutes') }}</option>
            <option value="3600" @if($range == 3600) selected @endif>{{ __('Search last 1 hour') }}</option>
            <option value="7200" @if($range == 7200) selected @endif>{{ __('Search last 2 hours') }}</option>
            <option value="28800" @if($range == 28800) selected @endif>{{ __('Search last 8 hours') }}</option>
            <option value="86400" @if($range == 86400) selected @endif>{{ __('Search last 1 day') }}</option>
            <option value="172800" @if($range == 172800) selected @endif>{{ __('Search last 2 days') }}</option>
            <option value="432000" @if($range == 432000) selected @endif>{{ __('Search last 5 days') }}</option>
            <option value="604800" @if($range == 604800) selected @endif>{{ __('Search last 7 days') }}</option>
            <option value="1209600" @if($range == 1209600) selected @endif>{{ __('Search last 14 days') }}</option>
            <option value="2592000" @if($range == 2592000) selected @endif>{{ __('Search last 30 days') }}</option>
        </select>
    </div>
    <div class="form-group">
        <label for="hidenavigation-{{ $id }}" class="control-label">{{ __('Hide Navigation') }}</label>
        <input type="checkbox" class="form-control" name="hidenavigation" id="hidenavigation-{{ $id }}" value="{{ $hidenavigation }}" data-size="normal" @if($hidenavigation) checked @endif>
    </div>
@endsection

@section('javascript')
    <script type="application/javascript">
        init_select2('#device-{{ $id }}', 'device', {limit: 100}, '{{ $device ? $device->device_id : '' }}');
        init_select2('#stream-{{ $id }}', 'graylog-streams', {}, '{{ $stream }}');

        $('#hidenavigation-{{ $id }}')
            .bootstrapSwitch('offColor','danger')
            .on('switchChange.bootstrapSwitch', function (e, data) {
                var hidenav = $(this).is(':checked') ? "1": "0";
                $('#hidenavigation-{{ $id }}').val(hidenav);
            });
    </script>
@endsection
