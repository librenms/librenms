@extends('widgets.settings.base')

@section('form')
    <div class="form-group row">
        <label for="title-{{ $id }}" class="control-label">@lang('Widget title')</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Custom title')" value="{{ $title }}">
    </div>
    <div class="form-group row">
        <label for="acknowledged-{{ $id }}" class="control-label">@lang('Show acknowledged'):</label>
        <select class="form-control" name="acknowledged" id="acknowledged-{{ $id }}">
            <option value="">@lang('not filtered')</option>
            <option value="1" @if($acknowledged === '1') selected @endif>@lang('show only acknowledged')</option>
            <option value="0" @if($acknowledged === '0') selected @endif>@lang('hide acknowledged')</option>
        </select>
    </div>
    <div class="form-group row">
        <label for="fired-{{ $id }}" class="control-label">@lang('Show only fired'):</label>
        <select class="form-control" name="fired" id="fired-{{ $id }}">
            <option value="">@lang('not filtered')</option>
            <option value="1" @if($fired === '1') selected @endif>@lang('show only fired alerts')</option>
        </select>
    </div>
    <div class="form-group row">
        <label for="min_severity-{{ $id }}" class="control-label">@lang('Displayed severity'):</label>
        <select class="form-control" name="min_severity" id="min_severity-{{ $id }}">
            <option value="">@lang('any severity')</option>
            @foreach($severities as $name => $val)
                <option value="{{ $val }}" @if($min_severity == $val) selected @endif>{{ $name }}{{$val > 3 ? '' : ' ' . __('or higher')}}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group row">
        <label for="state-{{ $id }}" class="control-label">@lang('State'):</label>
        <select class="form-control" name="state" id="state-{{ $id }}">
            <option value="">@lang('any state')</option>
            @foreach($states as $name => $val)
                <option value="{{ $val }}" @if($state === $val) selected @endif>{{ $name }}</option>
            @endforeach
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
    <div class="form-group row">
        <label for="proc-{{ $id }}" class="control-label">@lang('Show Procedure field'):</label>
        <select class="form-control" name="proc" id="proc-{{ $id }}">
            <option value="1" @if($proc == 1) selected @endif>@lang('show')</option>
            <option value="0" @if($proc == 0) selected @endif>@lang('hide')</option>
        </select>
    </div>
    <div class="form-group row">
        <label for="location-{{ $id }}" class="control-label">@lang('Show Location field'):</label>
        <select class="form-control" name="location" id="location-{{ $id }}">
            <option value="1" @if($location == 1) selected @endif>@lang('show')</option>
            <option value="0" @if($location == 0) selected @endif>@lang('hide')</option>
        </select>
    </div>
    <div class="form-group row">
        <label for="sort-{{ $id }}" class="control-label">@lang('Sort alerts by'):</label>
        <select class="form-control" name="sort" id="sort-{{ $id }}">
            <option value="" @if($sort == 1) selected @endif>@lang('timestamp, descending')</option>
            <option value="severity" @if($sort == 0) selected @endif>@lang('severity, descending')</option>
        </select>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        init_select2('#device_group-{{ $id }}', 'device-group', {});
    </script>
@endsection
