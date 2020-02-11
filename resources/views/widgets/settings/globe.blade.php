@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">@lang('Widget title')</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Default Title')" value="{{ $title }}">
    </div>
    <div class="form-group">
        <label for="markers-{{ $id }}" class="control-label">@lang('Markers')</label>
        <select class="form-control" id="markers-{{ $id }}" name="markers">
            <option value="devices" @if($markers == 'devices') selected @endif>@lang('Devices')</option>
            <option value="ports" @if($markers == 'ports') selected @endif>@lang('Ports')</option>
        </select>
    </div>
    <div class="form-group">
        <label for="resolution-{{ $id }}-{{ $id }}" class="control-label">@lang('Resolution')</label>
        <select class="form-control" id="resolution-{{ $id }}" name="resolution">
            <option value="countries" @if($resolution == 'countries') selected @endif>@lang('Countries')</option>
            <option value="provinces" @if($resolution == 'provinces') selected @endif>@lang('Provinces')</option>
            <option value="metros" @if($resolution == 'metros') selected @endif>@lang('Metros')</option>
        </select>
    </div>
    <div class="form-group">
        <label for="region-{{ $id }}" class="control-label">@lang('Region') <a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/geochart#configuration-options">@lang('Help')</a></label>
        <input type="text" class="form-control" name="region" id="region-{{ $id }}" value="{{ $region }}">
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
        init_select2('#device_group-{{ $id }}', 'device-group', {});
    </script>
@endsection
