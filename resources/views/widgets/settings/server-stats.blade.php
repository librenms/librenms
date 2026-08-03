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
        <label for="columnsize-{{ $id }}" class="control-label">{{ __('Columns') }}</label>
        <select name="columnsize" id="columnsize-{{ $id }}" class="form-control">
            <option value="1" @if($columnsize == 1) selected @endif>1</option>
            <option value="2" @if($columnsize == 2) selected @endif>2</option>
            <option value="3" @if($columnsize == 3) selected @endif>3</option>
            <option value="4" @if($columnsize == 4) selected @endif>4</option>
            <option value="5" @if($columnsize == 5) selected @endif>5</option>
            <option value="6" @if($columnsize == 6) selected @endif>6</option>
            <option value="12" @if($columnsize == 12) selected @endif>12</option>
        </select>
    </div>

    <div class="form-group">
        <label for="gauges-{{ $id }}" class="control-label">{{ __('widgets.server-stats.hidden_gauges') }}</label>
        <select class="form-control" name="gauges[]" id="gauges-{{ $id }}" multiple data-placeholder="{{ __('widgets.server-stats.select_gauges') }}">
            @foreach($gaugeOptions as $key => $description)
                <option value="{{ $key }}" @selected(in_array($key, (array) $gauges, true))>{{ $description }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ __('widgets.server-stats.hidden_gauges_help') }}</span>
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        init_select2('#device-{{ $id }}', 'device', {}, @json($device ? ['id' => $device->device_id, 'text' => $device->displayName()] : ''));
        $('#gauges-{{ $id }}').select2({width: '100%'});
    </script>
@endsection
