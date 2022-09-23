@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">{{ __('Widget title') }}</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="{{ __('Custom title') }}" value="{{ $title }}">
    </div>

    <div class="form-group">
        <label for="init_lat-{{ $id }}" class="control-label">{{ __('Initial Latitude') }}</label>
        <input class="form-control" name="init_lat" id="init_lat-{{ $id }}"  type="number" min="-90" max="90" step="any" value="{{ $init_lat }}" placeholder="{{ __('ie. 51.4800 for Greenwich') }}">
    </div>

    <div class="form-group">
        <label for="init_lng-{{ $id }}" class="control-label">{{ __('Initial Longitude') }}</label>
        <input class="form-control" name="init_lng" id="init_lng-{{ $id }}" type="number" min="-180" max="180" step="any" value="{{ $init_lng }}" placeholder="{{ __('ie. 0 for Greenwich') }}">

    </div>

    <div class="form-group">
        <label for="init_zoom-{{ $id }}" class="control-label">{{ __('Initial Zoom') }}</label>
        <input class="form-control" name="init_zoom" id="init_zoom-{{ $id }}" type="number" min="0" max="18" step="0.1" value="{{ $init_zoom }}" placeholder="{{ __('ie. 5.8') }}">
    </div>

    <div class="form-group">
        <label for="group_radius-{{ $id }}" class="control-label">{{ __('Grouping radius') }}</label>
        <input class="form-control" name="group_radius" id="group_radius-{{ $id }}" type="number" value="{{ $group_radius }}" placeholder="{{ __('default 80') }}">
    </div>

    <div class="form-group">
        <label for="status-{{ $id }}" class="control-label">{{ __('Show devices') }}</label>
        <select class="form-control" name="status" id="status-{{ $id }}">
            <option value="0,1" @if($status == '0,1') selected @endif>{{ __('Up + Down') }}</option>
            <option value="1" @if($status == '1') selected @endif>{{ __('Up') }}</option>
            <option value="0" @if($status == '0') selected @endif>{{ __('Down') }}</option>
        </select>
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
