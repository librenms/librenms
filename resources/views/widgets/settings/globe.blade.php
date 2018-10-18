@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">@lang('Widget title')</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Default Title')" value="{{ $title }}">
    </div>
    <div class="form-group">
        <label for="markers-{{ $id }}" class="control-label">@lang('Markers')</label>
        <select class="form-control" id="markers-{{ $id }}" name="markers">
            <option value="devices" {{ $markers == 'devices' ? 'selected' : '' }}>@lang('Devices')</option>
            <option value="ports" {{ $markers == 'ports' ? 'selected' : '' }}>@lang('Ports')</option>
        </select>
    </div>
    <div class="form-group">
        <label for="region-{{ $id }}" class="control-label">@lang('Region') <a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/geochart#configuration-options">@lang('Help')</a></label>
        <input type="text" class="form-control" name="region" id="region-{{ $id }}" value="{{ $region }}">
    </div>
    <div class="form-group">
        <label for="resolution-{{ $id }}-{{ $id }}" class="control-label">@lang('Resolution')</label>
        <select class="form-control" id="resolution-{{ $id }}" name="resolution">
            <option value="countries" {{ $resolution == 'countries' ? 'selected' : '' }}>@lang('Contries')</option>
            <option value="provinces" {{ $resolution == 'provinces' ? 'selected' : '' }}>@lang('Provinces')</option>
            <option value="metros" {{ $resolution == 'metros' ? 'selected' : '' }}>@lang('Metros')</option>
        </select>
    </div>
@endsection
