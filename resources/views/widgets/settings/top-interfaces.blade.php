@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="interface_count-{{ $id }}" class="control-label">@lang('Number of interfaces'):</label>
        <input class="form-control" type="number" min="1" step="1" name="interface_count" id="interface_count-{{ $id }}" value="{{ $interface_count }}">
    </div>
    <div class="form-group">
        <label for="time_interval-{{ $id }}" class="control-label">@lang('Last polled (minutes)'):</label>
        <input class="form-control" type="number" min="1" step="1" name="time_interval" id="time_interval-{{ $id }}" value="{{ $time_interval }}">
    </div>
    <div class="form-group">
        <label for="interface_filter-{{ $id }}" class="control-label">@lang('Interface type'):</label>
        <select class="form-control" id="interface_filter-{{ $id }}" name="interface_filter"  data-placeholder="@lang('All Ports')">
        @if($interface_filter)
            <option value="{{ $interface_filter }}">{{ $interface_filter }}</option>
        @endif
        </select>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        init_select2('#interface_filter-{{ $id }}', 'port-field', {limit: 100, field: 'ifType'}, '{{ $interface_filter ?: '' }}');
    </script>
@endsection
