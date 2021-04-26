@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">@lang('Widget title')</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Custom title for widget')" value="{{ $title }}">
    </div>

    <div class="form-group">
        <label for="type-{{ $id }}" class="control-label">@lang('Display type')</label>
        <select class="form-control" name="type" id="type-{{ $id }}" onchange="toggle_availability_type(this, '{{ $id }}');">
            <option value="0" @if($type == 0) selected @endif>@lang('boxes')</option>
            <option value="1" @if($type == 1) selected @endif>@lang('compact')</option>
        </select>
    </div>

    <div class="form-group" id="color_only_select-group-{{ $id }}" style="display: {{ $type == 0 ? 'block' : 'none' }};">
        <label for="color_only_select-{{ $id }}" class="control-label">@lang('Display Text')</label>
        <select class="form-control" name="color_only_select" id="color_only_select-{{ $id }}">
            <option value="1" @if($color_only_select == 1) selected @endif>@lang('empty')</option>
            <option value="2" @if($color_only_select == 2) selected @endunless>@lang('Hostname')</option>
            <option value="3" @if($color_only_select == 3) selected @endunless>@lang('Sysname')</option>
            <option value="0" @unless($color_only_select) selected @endunless>@lang('Device Status')</option>
        </select>
    </div>

    <div class="form-group" id="tile_size-group-{{ $id }}" style="display: {{ $type == 1 ? 'block' : 'none' }};">
        <label for="tile_size-{{ $id }}" class="control-label">@lang('Tile size')</label>
        <input type="text" class="form-control" name="tile_size" id="tile_size-{{ $id }}" placeholder="@lang('Tile size')" value="{{ $tile_size }}">
    </div>

    <div class="form-group">
        <label for="show_disabled_and_ignored-{{ $id }}" class="control-label">@lang('Disabled polling/alerting')</label>
        <select class="form-control" name="show_disabled_and_ignored" id="show_disabled_and_ignored-{{ $id }}">
            <option value="1" @if($show_disabled_and_ignored) selected @endif>@lang('Show')</option>
            <option value="0" @unless($show_disabled_and_ignored) selected @endunless>@lang('Hide')</option>
        </select>
    </div>

    <div class="form-group">
        <label for="mode_select-{{ $id }}" class="control-label">@lang('Mode select')</label>
        <select class="form-control" name="mode_select" id="mode_select-{{ $id }}">
            <option value="0" @if($mode_select == 0) selected @endif>@lang('only devices')</option>
            @config('show_services')
            <option value="1" @if($mode_select == 1) selected @endif>@lang('only services')</option>
            <option value="2" @if($mode_select == 2) selected @endif>@lang('devices and services')</option>
            @endconfig
        </select>
    </div>

    <div class="form-group">
        <label for="order_by-{{ $id }}" class="control-label">@lang('Order By')</label>
        <select class="form-control" name="order_by" id="order_by-{{ $id }}">
            <option value="hostname" @if($order_by == 'hostname') selected @endif>@lang('Hostname')</option>
            <option value="status" @if($order_by == 'status') selected @endif>@lang('Status')</option>
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
@endsection

@section('javascript')
    <script type="text/javascript">
        init_select2('#device_group-{{ $id }}', 'device-group', {});

        function toggle_availability_type(el, id) {
            console.log(el.value);
            if (el.value === '0') {
                $('#tile_size-group-' + id).hide();
                $('#color_only_select-group-' + id).show();
            } else {
                $('#tile_size-group-' + id).show();
                $('#color_only_select-group-' + id).hide();
            }
        }
    </script>
@endsection
