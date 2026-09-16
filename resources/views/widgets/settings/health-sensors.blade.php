@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">{{ __('Widget title') }}</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="{{ __('Default Title') }}" value="{{ $title }}">
    </div>
    <div class="form-group">
        <label class="control-label">{{ __('Device scope') }}</label>
        <div class="tw:mt-1 tw:space-y-1">
            <label class="tw:font-normal tw:flex tw:items-center tw:gap-2 tw:m-0">
                <input class="tw:m-0" type="radio" name="device_scope" value="device" @if(($device_scope ?? 'device') === 'device') checked @endif>
                <span>{{ __('Single device') }}</span>
            </label>
            <label class="tw:font-normal tw:flex tw:items-center tw:gap-2 tw:m-0">
                <input class="tw:m-0" type="radio" name="device_scope" value="device_group" @if(($device_scope ?? 'device') === 'device_group') checked @endif>
                <span>{{ __('Device group') }}</span>
            </label>
            <label class="tw:font-normal tw:flex tw:items-center tw:gap-2 tw:m-0">
                <input class="tw:m-0" type="radio" name="device_scope" value="device_regex" @if(($device_scope ?? 'device') === 'device_regex') checked @endif>
                <span>{{ __('Device match (regex)') }}</span>
            </label>
        </div>
    </div>
    <div class="form-group" id="health-sensors-device-{{ $id }}">
        <label for="device-{{ $id }}" class="control-label">{{ __('Device') }}</label>
        <select class="form-control" id="device-{{ $id }}">
            @if($device)
                <option value="{{ $device->device_id }}">{{ $device->displayName() }}</option>
            @endif
        </select>
    </div>
    <div class="form-group" id="health-sensors-device-group-{{ $id }}" style="display:none;">
        <label for="device_group-{{ $id }}" class="control-label">{{ __('Device group') }}</label>
        <select class="form-control" id="device_group-{{ $id }}" data-placeholder="{{ __('Select a device group') }}">
            @if($device_group)
                <option value="{{ $device_group->id }}" selected>{{ $device_group->name }}</option>
            @endif
        </select>
    </div>
    <div class="form-group" id="health-sensors-device-regex-{{ $id }}" style="display:none;">
        <label for="device_regex-{{ $id }}" class="control-label">{{ __('Device match (regex)') }}</label>
        <input type="text" class="form-control" id="device_regex-{{ $id }}" value="{{ $device_regex }}" placeholder="^router">
    </div>
    <div class="form-group">
        <label for="sensor_class_regex-{{ $id }}" class="control-label">{{ __('Sensor class filter (regex)') }}</label>
        <input type="text" class="form-control" name="sensor_class_regex" id="sensor_class_regex-{{ $id }}" value="{{ $sensor_class_regex }}" placeholder=".*">
    </div>
    <div class="form-group">
        <label for="descr_regex-{{ $id }}" class="control-label">{{ __('Description filter (regex)') }}</label>
        <input type="text" class="form-control" name="descr_regex" id="descr_regex-{{ $id }}" value="{{ $descr_regex }}"
            placeholder=".*">
    </div>
    <div class="form-group">
        <label for="display_mode-{{ $id }}" class="control-label">{{ __('Display') }}</label>
        <select class="form-control" name="display_mode" id="display_mode-{{ $id }}">
            <option value="number" @if ($display_mode === 'number') selected @endif>{{ __('Numbers') }}</option>
            <option value="progress-bar" @if ($display_mode === 'progress-bar') selected @endif>{{ __('Progress Bar') }}</option>
            <option value="gauge" @if ($display_mode === 'gauge') selected @endif>{{ __('Gauges') }}</option>
            <option value="graph" @if ($display_mode === 'graph') selected @endif>{{ __('Graph (24h)') }}</option>
        </select>
    </div>
    <div class="form-group">
        <label for="rows-{{ $id }}" class="control-label">{{ __('Rows') }}</label>
        <input type="number" step="1" min="1" max="50" class="form-control" name="rows" id="rows-{{ $id }}" value="{{ $rows }}">
    </div>
    <div class="form-group">
        <label for="cols-{{ $id }}" class="control-label">{{ __('Columns') }}</label>
        <input type="number" step="1" min="1" max="12" class="form-control" name="cols" id="cols-{{ $id }}" value="{{ $cols }}">
    </div>
    <div class="form-group">
        <label for="warning-{{ $id }}" class="control-label">{{ __('Warning at or above') }}</label>
        <input type="number" step="any" class="form-control" name="warning" id="warning-{{ $id }}" value="{{ $warning }}" placeholder="{{ __('Optional') }}">
    </div>
    <div class="form-group">
        <label for="critical-{{ $id }}" class="control-label">{{ __('Critical at or above') }}</label>
        <input type="number" step="any" class="form-control" name="critical" id="critical-{{ $id }}" value="{{ $critical }}" placeholder="{{ __('Optional') }}">
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        function healthSensorsApplyNames{{ $id }}(scope) {
            var $device = $('#device-{{ $id }}');
            var $group = $('#device_group-{{ $id }}');
            var $regex = $('#device_regex-{{ $id }}');

            $device.removeAttr('name');
            $group.removeAttr('name');
            $regex.removeAttr('name');

            if (scope === 'device') {
                $device.attr('name', 'device');
            } else if (scope === 'device_group') {
                $group.attr('name', 'device_group');
            } else if (scope === 'device_regex') {
                $regex.attr('name', 'device_regex');
            }
        }

        function healthSensorsToggleDeviceScope{{ $id }}() {
            var $form = $('#health-sensors-device-{{ $id }}').closest('form');
            var scope = $form.find('input[name="device_scope"]:checked').val();
            $('#health-sensors-device-{{ $id }}').toggle(scope === 'device');
            $('#health-sensors-device-group-{{ $id }}').toggle(scope === 'device_group');
            $('#health-sensors-device-regex-{{ $id }}').toggle(scope === 'device_regex');

            healthSensorsApplyNames{{ $id }}(scope);
        }

        (function () {
            var $form = $('#health-sensors-device-{{ $id }}').closest('form');
            $form.on('change', 'input[name=\"device_scope\"]', healthSensorsToggleDeviceScope{{ $id }});
            healthSensorsToggleDeviceScope{{ $id }}();

            init_select2('#device-{{ $id }}', 'device', {}, @json($device ? ['id' => $device->device_id, 'text' => $device->displayName()] : ''));
            init_select2(
                '#device_group-{{ $id }}',
                'device-group',
                {},
                @json($device_group ? ['id' => $device_group->id, 'text' => $device_group->name] : '')
            );
        })();
    </script>
@endsection
