@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">{{ __('Widget title') }}</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="{{ __('Custom title') }}" value="{{ $title }}">
    </div>
    <div class="form-group">
        <label for="device-{{ $id }}" class="control-label">{{ __('Device') }}</label>
        <select class="form-control" id="device-{{ $id }}" name="device" data-placeholder="{{ __('All devices') }}">
            @if($device)
                <option value="{{ $device->device_id }}">{{ $device->displayName() }}</option>
            @endif
        </select>
    </div>
    <div class="form-group">
        <label for="eventtype-{{ $id }}" class="control-label">{{ __('Event type') }}</label>
        <select class="form-control" id="eventtype-{{ $id }}" name="eventtype" data-placeholder="{{ __('All types') }}" data-tags="true">
            @if($eventtype)
                <option value="{{ $eventtype }}">{{ $eventtype }}</option>
            @endif
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
        <label for="message_filter-{{ $id }}" class="control-label">{{ __('Only show messages that contain') }}</label>
        <input type="text" class="form-control" name="message_filter" id="message_filter-{{ $id }}" placeholder="{{ __('Search') }}" value="{{ $message_filter }}">
    </div>
    <div class="form-group">
        <label for="age-{{ $id }}" class="control-label">{{ __('Hide messages older than') }}</label>
        <input type="text" class="form-control" name="age" id="age-{{ $id }}" placeholder="{{ __('24h') }}" value="{{ $age }}">
    </div>
    <div class="form-group">
        <label for="hidenavigation-{{ $id }}" class="control-label">{{ __('Hide Navigation') }}</label>
        <input type="checkbox" class="form-control" name="hidenavigation" id="hidenavigation-{{ $id }}" value="{{ $hidenavigation }}" data-size="normal" @if($hidenavigation) checked @endif>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        init_select2('#device-{{ $id }}', 'device', {}, '{{ $device ? $device->device_id : '' }}');
        init_select2('#device_group-{{ $id }}', 'device-group', {});
        init_select2('#eventtype-{{ $id }}', 'eventlog', function(params) {
            return {
                field: "type",
                device: $('#device-{{ $id }}').val(),
                term: params.term,
                page: params.page || 1
            }
        }, '{{ $eventtype ?: "" }}');

        $('#hidenavigation-{{ $id }}')
            .bootstrapSwitch('offColor','danger')
            .on('switchChange.bootstrapSwitch', function (e, data) {
                var hidenav = $(this).is(':checked') ? "1": "0";
                $('#hidenavigation-{{ $id }}').val(hidenav);
            });
    </script>
@endsection
