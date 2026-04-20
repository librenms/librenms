@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">{{ __('Widget title') }}</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="{{ __('Default Title') }}" value="{{ $title }}">
    </div>

    <div class="form-group">
        <label for="device-{{ $id }}" class="control-label">{{ __('Device') }}</label>
        <select class="form-control" id="device-{{ $id }}" name="device" required>
            @if($device)
                <option value="{{ $device->device_id }}">{{ $device->displayName() }}</option>
            @endif
        </select>
    </div>

    <div class="form-group">
        <label for="sensor_class-{{ $id }}" class="control-label">{{ __('Sensor type') }}</label>
        <select class="form-control" name="sensor_class" id="sensor_class-{{ $id }}">
            @foreach ($sensor_classes as $class)
                <option value="{{ $class->value }}" @if ($sensor_class === $class->value) selected @endif>{{ $class->label() }}</option>
            @endforeach
        </select>
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
        <label for="descr_regex-{{ $id }}" class="control-label">{{ __('Description filter (regex)') }}</label>
        <input type="text" class="form-control" name="descr_regex" id="descr_regex-{{ $id }}" value="{{ $descr_regex }}" placeholder=".*">
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
        init_select2('#device-{{ $id }}', 'device', {}, @json($device ? ['id' => $device->device_id, 'text' => $device->displayName()] : ''));
    </script>
@endsection
