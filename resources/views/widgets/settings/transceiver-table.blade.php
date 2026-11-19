@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="xcvr-title-{{ $id }}">{{ __('widgets.transceiver-table.widget_title') }}</label>
        <input class="form-control" type="text" name="title" id="xcvr-title-{{ $id }}" value="{{ $title ?? '' }}" placeholder="{{ __('widgets.transceiver-table.title') }}" maxlength="255">
        <p class="help-block">{{ __('widgets.transceiver-table.title_help') }}</p>
    </div>
    <div class="form-group">
        <label for="xcvr-device-group-{{ $id }}">{{ __('widgets.transceiver-table.device_group') }}</label>
        <select class="form-control" name="selected_device_group" id="xcvr-device-group-{{ $id }}" data-placeholder="{{ __('widgets.transceiver-table.all_devices') }}">
            @if($deviceGroup)
                <option value="{{ $deviceGroup->id }}" selected>{{ $deviceGroup->name }}</option>
            @endif
        </select>
    </div>
    <div class="form-group">
        <label for="xcvr-port-group-{{ $id }}">{{ __('widgets.transceiver-table.port_groups') }}</label>
        <input type="hidden" name="selected_port_group[]" value="">
        <select class="form-control" name="selected_port_group[]" id="xcvr-port-group-{{ $id }}" data-placeholder="{{ __('widgets.transceiver-table.all_ports') }}" multiple>
            @foreach($portGroups as $portGroup)
                <option value="{{ $portGroup->id }}" selected>{{ $portGroup->name }}</option>
            @endforeach
        </select>
        <p class="help-block">{{ __('widgets.transceiver-table.groups_help') }}</p>
    </div>
@endsection

@section('javascript')
    <script>
        init_select2('#xcvr-device-group-{{ $id }}', 'device-group', {});
        init_select2('#xcvr-port-group-{{ $id }}', 'port-group', {});
    </script>
@endsection
