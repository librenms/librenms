@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">{{ __('Widget title') }}</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="{{ __('Custom title for widget') }}" value="{{ $title }}">
    </div>

    <div class="form-group">
        <label for="type-{{ $id }}" class="control-label">{{ __('Display type') }}</label>
        <select class="form-control" name="type" id="type-{{ $id }}" onchange="toggle_alert_type(this, '{{ $id }}');">
            <option value="0" @if($type == 0) selected @endif>{{ __('boxes') }}</option>
            <option value="1" @if($type == 1) selected @endif>{{ __('compact') }}</option>
        </select>
    </div>

    <div class="form-group" id="display_label-group-{{ $id }}" style="display: {{ $type == 0 ? 'block' : 'none' }};">
        <label for="display_label-{{ $id }}" class="control-label">{{ __('Label') }}</label>
        <select class="form-control" name="display_label" id="display_label-{{ $id }}">
            <option value="1" @if($display_label == 1) selected @endif>{{ __('empty') }}</option>
            <option value="4" @if($display_label == 4) selected @endunless>{{ __('Display Name') }}</option>
            <option value="2" @if($display_label == 2) selected @endunless>{{ __('Hostname') }}</option>
            <option value="3" @if($display_label == 3) selected @endunless>{{ __('SNMP sysName') }}</option>
            <option value="0" @unless($display_label) selected @endunless>{{ __('Severity') }}</option>
        </select>
    </div>

    <div class="form-group" id="tile_size-group-{{ $id }}" style="display: {{ $type == 1 ? 'block' : 'none' }};">
        <label for="tile_size-{{ $id }}" class="control-label">{{ __('Tile size') }}</label>
        <input type="text" class="form-control" name="tile_size" id="tile_size-{{ $id }}" placeholder="{{ __('Tile size') }}" value="{{ $tile_size }}">
    </div>

    <div class="form-group">
        <label for="show_disabled_and_ignored-{{ $id }}" class="control-label">{{ __('Disabled polling/alerting') }}</label>
        <select class="form-control" name="show_disabled_and_ignored" id="show_disabled_and_ignored-{{ $id }}">
            <option value="1" @if($show_disabled_and_ignored) selected @endif>{{ __('Show') }}</option>
            <option value="0" @unless($show_disabled_and_ignored) selected @endunless>{{ __('Hide') }}</option>
        </select>
    </div>

    <div class="form-group">
        <label for="show_ok_devices-{{ $id }}" class="control-label">{{ __('OK Devices') }}</label>
        <select class="form-control" name="show_ok_devices" id="show_ok_devices-{{ $id }}">
            <option value="1" @if($show_ok_devices) selected @endif>{{ __('Show') }}</option>
            <option value="0" @unless($show_ok_devices) selected @endunless>{{ __('Hide') }}</option>
        </select>
    </div>

    <div class="form-group">
        <label for="order_by-{{ $id }}" class="control-label">{{ __('Order By') }}</label>
        <select class="form-control" name="order_by" id="order_by-{{ $id }}">
            <option value="label" @if($order_by == 'label') selected @endif>{{ __('Label') }}</option>
            <option value="severity" @if($order_by == 'severity') selected @endif>{{ __('Severity') }}</option>
            <option value="display-name" @if($order_by == 'display-name') selected @endif>{{ __('Display Name') }}</option>
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

        function toggle_alert_type(el, id) {
            if (el.value === '0') {
                $('#tile_size-group-' + id).hide();
                $('#display_label-group-' + id).show();
            } else {
                $('#tile_size-group-' + id).show();
                $('#display_label-group-' + id).hide();
            }
        }
    </script>
@endsection
