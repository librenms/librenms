@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="interface_count-{{ $id }}" class="control-label col-sm-6">@lang('Number of interfaces'):</label>
        <div class="col-sm-6">
            <input class="form-control" type="number" min="1" step="1" name="interface_count" id="interface_count-{{ $id }}" value="{{ $interface_count }}">
        </div>
    </div>
    <div class="form-group">
        <label for="time_interval-{{ $id }}" class="control-label col-sm-6">@lang('Last polled (minutes)'):</label>
        <div class="col-sm-6">
            <input class="form-control" type="number" min="1" step="1" name="time_interval" id="time_interval-{{ $id }}" value="{{ $time_interval }}">
        </div>
    </div>
    <div class="form-group">
        <label for="interface_filter-{{ $id }}" class="col-sm-6 control-label">@lang('Interface type'):</label>
        <div class="col-sm-6">
            <select class="form-control" id="interface_filter-{{ $id }}" name="interface_filter">
                @if($interface_filter)
                <option value="{{ $interface_filter }}">{{ $interface_filter }}</option>
                @endif
            </select>
        </div>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        var $ifselect = $("#interface_filter-{{ $id }}");
        $ifselect.select2({
            theme: "bootstrap",
            dropdownAutoWidth : true,
            width: "auto",
            allowClear: true,
            placeholder: "All Ports",
            ajax: {
                url: 'ajax/select/port-field',
                delay: 200,
                data: function(params) {
                    return {
                        field: "ifType",
                        term: params.term,
                        page: params.page || 1
                    }
                }
            }
        });
        @if($interface_filter)
            $ifselect.val({{ $interface_filter }}).trigger('change');
        @endif
    </script>
@endsection
