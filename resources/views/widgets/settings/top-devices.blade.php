@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label col-sm-6 availability-map-widget-header">@lang('Widget title')</label>
        <div class="col-sm-6">
            <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Custom title')" value="{{ $title }}">
        </div>
    </div>
    <div class="form-group">
        <label for="top_query-{{ $id }}" class="control-label col-sm-6 availability-map-widget-header">@lang('Top query')</label>
        <div class="col-sm-6">
            <select class="form-control" name="top_query" id="top_query-{{ $id }}">
                <option value="traffic" {{ $top_query == 'traffic' ? 'selected' : '' }}>@lang('Traffic')</option>
                <option value="uptime" {{ $top_query == 'uptime' ? 'selected' : '' }}>@lang('Uptime')</option>
                <option value="ping" {{ $top_query == 'ping' ? 'selected' : '' }}>@lang('Response time')</option>
                <option value="poller" {{ $top_query == 'poller' ? 'selected' : '' }}>@lang('Poller duration')</option>
                <option value="cpu" {{ $top_query == 'cpu' ? 'selected' : '' }}>@lang('Processor load')</option>
                <option value="ram" {{ $top_query == 'ram' ? 'selected' : '' }}>@lang('Memory usage')</option>
                <option value="storage" {{ $top_query == 'storage' ? 'selected' : '' }}>@lang('Disk usage')</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="sort_order-{{ $id }}" class="control-label col-sm-6 availability-map-widget-header">@lang('Sort order')</label>
        <div class="col-sm-6">
            <select class="form-control" name="sort_order" id="sort_order-{{ $id }}">
                <option value="asc" {{ $top_query == 'asc' ? 'selected' : '' }}>@lang('Ascending')</option>
                <option value="desc" {{ $top_query == 'desc' ? 'selected' : '' }}>@lang('Descending')</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="device_count-{{ $id }}" class="control-label col-sm-6 availability-map-widget-header">@lang('Number of Devices')</label>
        <div class="col-sm-6">
            <input class="form-control" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode >= 48 && event.charCode <= 57" name="device_count" id="device_count-{{ $id }}" value="{{ $device_count }}">
        </div>
    </div>
    <div class="form-group">
        <label for="time_interval-{{ $id }}" class="control-label col-sm-6 availability-map-widget-header">@lang('Time interval (minutes)')</label>
        <div class="col-sm-6">
            <input class="form-control" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode >= 48 && event.charCode <= 57" name="time_interval" id="time_interval-{{ $id }}" value="{{ $time_interval }}">
        </div>
    </div>
@endsection
