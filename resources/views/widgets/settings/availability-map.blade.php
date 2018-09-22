@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="col-sm-4 control-label availability-map-widget-header">@lang('Widget title')</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Custom title for widget')"
                   value="{{ $title }}">
        </div>
    </div>

    <div class="form-group">
        <label for="type-{{ $id }}" class="col-sm-4 control-label availability-map-widget-header">@lang('Display type')</label>
        <div class="col-sm-8">
            <select class="form-control" name="type" id="type-{{ $id }}" onchange="toggle_availability_type(this, {{ $id }});">
                <option value="0" {{ $type == 0 ? 'selected' : ''}}>@lang('boxes')</option>
                <option value="1" {{ $type == 1 ? 'selected' : ''}}>@lang('compact')</option>
            </select>
        </div>
    </div>

    <div class="form-group" id="color_only_select-group-{{ $id }}" style="display: {{ $type == 0 ? 'block' : 'none' }};">
        <label for="color_only_select-{{ $id }}" class="col-sm-4 control-label availability-map-widget-header">@lang('Uniform Tiles')</label>
        <div class="col-sm-8">
            <select class="form-control" name="color_only_select" id="color_only_select-{{ $id }}">
                <option value="1" {{ $color_only_select ? 'selected' : ''}}>@lang('yes')</option>
                <option value="0" {{ $color_only_select ? '' : 'selected'}}>@lang('no')</option>
            </select>
        </div>
    </div>

    <div class="form-group" id="tile_size-group-{{ $id }}" style="display: {{ $type == 1 ? 'block' : 'none' }};">
        <label for="tile_size-{{ $id }}" class="col-sm-4 control-label availability-map-widget-header">@lang('Tile size')</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="tile_size" id="tile_size-{{ $id }}" placeholder="@lang('Tile size')" value="{{ $tile_size }}">
        </div>
    </div>

    <div class="form-group">
        <label for="show_disabled_and_ignored-{{ $id }}" class="col-sm-4 control-label availability-map-widget-header">@lang('Disabled/ignored')</label>
        <div class="col-sm-8">
            <select class="form-control" name="show_disabled_and_ignored" id="show_disabled_and_ignored-{{ $id }}">
                <option value="1" {{ $show_disabled_and_ignored ? 'selected' : ''}}>@lang('yes')</option>
                <option value="0" {{ $show_disabled_and_ignored ? '' : 'selected'}}>@lang('no')</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="mode_select-{{ $id }}" class="col-sm-4 control-label availability-map-widget-header">@lang('Mode select')</label>
        <div class="col-sm-8">
            <select class="form-control" name="mode_select" id="mode_select-{{ $id }}">
                <option value="0" {{ $mode_select == 0 ? 'selected' : '' }}>@lang('only devices')</option>
                @config('show_services')
                <option value="1" {{ $mode_select == 1 ? 'selected' : '' }}>@lang('only services')</option>
                <option value="2" {{ $mode_select == 2 ? 'selected' : '' }}>@lang('devices and services')</option>
                @endconfig
            </select>
        </div>
    </div>


    <div class="form-group">
        <label for="device_group-{{ $id }}" class="col-sm-4 control-label availability-map-widget-header">@lang('Device group')</label>
        <div class="col-sm-8">
            <select class="form-control" name="device_group" id="device_group-{{ $id }}">
                @if($device_group)
                    <option value="{{ $device_group->id }}" selected> {{ $device_group->name }} </option>
                @endif
            </select>
        </div>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        function toggle_availability_type(el, id) {
            if (el.value == 0) {
                $('#tile_size-group-' + id).hide();
                $('#color_only_select-group-' + id).show();
            } else {
                $('#tile_size-group-' + id).show();
                $('#color_only_select-group-' + id).hide();
            }
        }
    </script>
@endsection
