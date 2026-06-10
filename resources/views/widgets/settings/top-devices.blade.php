@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">{{ __('Widget title') }}</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="{{ __('Default Title') }}" value="{{ $title }}">
    </div>
    <div class="form-group">
        <label for="top_query-{{ $id }}" class="control-label">{{ __('Top query') }}</label>
        <select class="form-control" name="top_query" id="top_query-{{ $id }}">
            <option value="traffic" @if($top_query == 'traffic') selected @endif>{{ __('Traffic') }}</option>
            <option value="uptime" @if($top_query == 'uptime') selected @endif>{{ __('Uptime') }}</option>
            <option value="ping" @if($top_query == 'ping') selected @endif>{{ __('Response time') }}</option>
            <option value="poller" @if($top_query == 'poller') selected @endif>{{ __('Poller duration') }}</option>
            <option value="cpu" @if($top_query == 'cpu') selected @endif>{{ __('Processor load') }}</option>
            <option value="ram" @if($top_query == 'ram') selected @endif>{{ __('Memory usage') }}</option>
            <option value="storage" @if($top_query == 'storage') selected @endif>{{ __('Disk usage') }}</option>
        </select>
    </div>
    <div class="form-group">
        <label for="sort_order-{{ $id }}" class="control-label">{{ __('Sort order') }}</label>
        <select class="form-control" name="sort_order" id="sort_order-{{ $id }}">
            <option value="asc" @if($sort_order == 'asc') selected @endif>{{ __('Ascending') }}</option>
            <option value="desc" @if($sort_order == 'desc') selected @endif>{{ __('Descending') }}</option>
        </select>
    </div>
    <div class="form-group">
        <label for="device_count-{{ $id }}" class="control-label">{{ __('Number of Devices') }}</label>
        <input class="form-control" name="device_count" id="device_count-{{ $id }}" value="{{ $device_count }}">
    </div>
    <div class="form-group">
        <label for="time_interval-{{ $id }}" class="control-label">{{ __('Last Polled (minutes)') }}</label>
        <input class="form-control" name="time_interval" id="time_interval-{{ $id }}" value="{{ $time_interval }}">
    </div>
    <div class="form-group">
        <label for="device_group-{{ $id }}" class="control-label">{{ __('Device group') }}</label>
        <select class="form-control" name="device_group" id="device_group-{{ $id }}" data-placeholder="{{ __('All Devices') }}">
            @if($device_group)
                <option value="{{ $device_group->id }}" selected>{{ $device_group->name }}</option>
            @endif
        </select>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        init_select2('#device_group-{{ $id }}', 'device-group', {});
    </script>
@endsection
