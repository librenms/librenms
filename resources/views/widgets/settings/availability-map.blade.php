<form class="form" onsubmit="widget_settings(this); return false;">
    <div class="form-group">
        <label for="title-{{ $id }}" class="col-sm-4 control-label availability-map-widget-header">@lang('Widget title')</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Custom title for widget')"
                   value="{{ $title }}">
        </div>
    </div>

    @config('webui.availability_map_compact')

    <div class="form-group">
        <label for="tile_size-{{ $id }}" class="col-sm-4 control-label availability-map-widget-header">@lang('Tile size')</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="tile_size" id="tile_size-{{ $id }}" placeholder="@lang('Tile size')"
                   value="{{ $tile_size }}">
        </div>
    </div>

    @endconfig

    @notconfig('webui.availability_map_compact')

    <div class="form-group">
        <label for="color_only_select-{{ $id }}"
               class="col-sm-4 control-label availability-map-widget-header">@lang('Uniform Tiles')</label>
        <div class="col-sm-8">
            <select class="form-control" name="color_only_select" id="color_only_select-{{ $id }}">
                <option value="1" {{ $color_only_select ? 'selected' : ''}}>@lang('yes')</option>
                <option value="0" {{ $color_only_select ? '' : 'selected'}}>@lang('no')</option>
            </select>
        </div>
    </div>

    @endconfig

    <div class="form-group">
        <label for="show_disabled_and_ignored-{{ $id }}"
               class="col-sm-4 control-label availability-map-widget-header">@lang('Disabled/ignored')</label>
        <div class="col-sm-8">
            <select class="form-control" name="show_disabled_and_ignored" id="show_disabled_and_ignored-{{ $id }}">
                <option value="1" {{ $show_disabled_and_ignored ? 'selected' : ''}}>@lang('yes')</option>
                <option value="0" {{ $show_disabled_and_ignored ? '' : 'selected'}}>@lang('no')</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="mode_select-{{ $id }}"
               class="col-sm-4 control-label availability-map-widget-header">@lang('Mode select')</label>
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
        <label for="device_group-{{ $id }}"
               class="col-sm-4 control-label availability-map-widget-header">@lang('Device group')</label>
        <div class="col-sm-8">
            <select class="form-control" name="device_group" id="device_group-{{ $id }}">
                @if($device_group)
                    <option value="{{ $device_group->id }}" selected> {{ $device_group->name }} </option>
                @endif
            </select>
        </div>
    </div>


    <br style="clear:both;" />
    <div class="form-group">
        <div class="col-sm-2 col-sm-offset-2">
            <button type="submit" class="btn btn-default">Set</button>
        </div>
    </div>
</form>
