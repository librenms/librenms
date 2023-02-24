@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">{{ __('Widget title') }}</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="{{ __('Custom title') }}" value="{{ $title }}">
    </div>
    <div class="form-group">
        <label for="state-{{ $id }}" class="control-label">{{ __('State') }}</label>
        <select class="form-control" name="state" id="state-{{ $id }}">
            <option value="-1">{{ __('not filtered') }}</option>
            <option value="0" @if($state === '1') selected @endif>{{ __('OK') }}</option>
            <option value="1" @if($state === '0') selected @endif>{{ __('Alert') }}</option>
        </select>
    </div>
    <div class="form-group">
        <label for="min_severity-{{ $id }}" class="control-label">{{ __('Displayed severity') }}</label>
        <select class="form-control" name="min_severity" id="min_severity-{{ $id }}">
            <option value="">{{ __('any severity') }}</option>
            @foreach($severities as $name => $val)
                <option value="{{ $val }}" @if($min_severity == $val) selected @endif>{{ $name }}{{$val > 3 ? '' : ' ' . __('or higher')}}</option>
            @endforeach
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
    <div class="form-group">
        <label for="hidenavigation-{{ $id }}" class="control-label">{{ __('Hide Navigation') }}</label>
        <input type="checkbox" class="form-control" name="hidenavigation" id="hidenavigation-{{ $id }}" value="{{ $hidenavigation }}" data-size="normal" @if($hidenavigation) checked @endif>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        init_select2('#device_group-{{ $id }}', 'device-group', {});
        $('#hidenavigation-{{ $id }}')
            .bootstrapSwitch('offColor','danger')
            .on('switchChange.bootstrapSwitch', function (e, data) {
                var hidenav = $(this).is(':checked') ? "1": "0";
                $('#hidenavigation-{{ $id }}').val(hidenav);
            });
    </script>
@endsection
