@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="col-sm-4 control-label">@lang('Widget title')</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Automatic Title')" value="{{ $title }}">
        </div>
    </div>
    <div class="form-group">
        <label for="graph_type-{{ $id }}" class="col-sm-4 control-label">@lang('Graph type')</label>
        <div class="col-sm-8">
            <select class="form-control" id="graph_type-{{ $id }}" name="graph_type" required data-placeholder="@lang('Select a graph')" onchange="switch_graph_type{{ $id }}(this.value);">
                @if($graph_type)
                    <option value="{{ $graph_type }}">{{ $graph_text }}</option>
                @endif
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="graph_legend-{{ $id }}" class="col-sm-4 control-label">@lang('Show legend')</label>
        <div class="col-sm-8">
            <select class="form-control" id="graph_legend-{{ $id }}" name="graph_legend">
                <option value="yes" {{ $graph_legend == 'yes' ? 'selected' : '' }}>@lang('yes')</option>
                <option value="no" {{ $graph_legend == 'no' ? 'selected' : '' }}>@lang('no')</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="graph_range-{{ $id }}" class="col-sm-4 control-label">@lang('Date range')</label>
        <div class="col-sm-8">
        <select class="form-control" id="graph_range-{{ $id }}" name="graph_range">
            <option value="onehour" {{ $graph_range == 'onehour' ? 'selected' : '' }}>@lang('One Hour')</option>
            <option value="fourhour" {{ $graph_range == 'fourhour' ? 'selected' : '' }}>@lang('Four Hours')</option>
            <option value="sixhour" {{ $graph_range == 'sixhour' ? 'selected' : '' }}>@lang('Six Hours')</option>
            <option value="twelvehour" {{ $graph_range == 'twelvehour' ? 'selected' : '' }}>@lang('Twelve Hours')</option>
            <option value="day" {{ $graph_range == 'day' ? 'selected' : '' }}>@lang('One Day')</option>
            <option value="week" {{ $graph_range == 'week' ? 'selected' : '' }}>@lang('One Week')</option>
            <option value="twoweek" {{ $graph_range == 'twoweek' ? 'selected' : '' }}>@lang('Two Weeks')</option>
            <option value="month" {{ $graph_range == 'month' ? 'selected' : '' }}>@lang('One Month')</option>
            <option value="twomonth" {{ $graph_range == 'twomonth' ? 'selected' : '' }}>@lang('Two Months')</option>
            <option value="threemonth" {{ $graph_range == 'threemonth' ? 'selected' : '' }}>@lang('Three Months')</option>
            <option value="year" {{ $graph_range == 'year' ? 'selected' : '' }}>@lang('One Year')</option>
            <option value="twoyear" {{ $graph_range == 'twoyear' ? 'selected' : '' }}>@lang('Two Years')</option>
        </select>
        </div>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_device-{{ $id }}" style="display: none;">
        <label for="graph_device-{{ $id }}" class="col-sm-4 control-label">@lang('Device')</label>
        <div class="col-sm-8">
            <select class="form-control" id="graph_device-{{ $id }}" name="graph_device" data-placeholder="@lang('Select a device')">
                @if($graph_device)
                    <option value="{{ $graph_device }}">{{ $device_text }}</option>
                @endif
            </select>
        </div>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_port-{{ $id }}" style="display: none;">
        <label for="graph_port-{{ $id }}" class="col-sm-4 control-label">@lang('Port')</label>
        <div class="col-sm-8">
            <select class="form-control" id="graph_port-{{ $id }}" name="graph_port" data-placeholder="@lang('Select a port')">
            @if($graph_port)
                    <option value="{{ $graph_port }}">{{ $port_text }}</option>
                @endif
            </select>
        </div>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_application-{{ $id }}" style="display: none;">
        <label for="graph_application-{{ $id }}" class="col-sm-4 control-label">@lang('Application')</label>
        <div class="col-sm-8">
            <select class="form-control" id="graph_application-{{ $id }}" name="graph_application" data-placeholder="@lang('Select an application')">
            @if($graph_application)
                    <option value="{{ $graph_application }}">{{ $application_text }}</option>
                @endif
            </select>
        </div>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_munin-{{ $id }}" style="display: none;">
        <label for="graph_munin-{{ $id }}" class="col-sm-4 control-label">@lang('Munin plugin')</label>
        <div class="col-sm-8">
            <select class="form-control" id="graph_munin-{{ $id }}" name="graph_munin" data-placeholder="@lang('Select a Munin plugin')">
            @if($graph_munin)
                    <option value="{{ $graph_munin }}">{{ $munin_text }}</option>
                @endif
            </select>
        </div>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_bill-{{ $id }}" style="display: none;">
        <label for="graph_bill-{{ $id }}" class="col-sm-4 control-label">@lang('Bill')</label>
        <div class="col-sm-8">
            <select class="form-control" id="graph_bill-{{ $id }}" name="graph_bill" data-placeholder="@lang('Select a bill')">
            @if($graph_bill)
                    <option value="{{ $graph_bill }}">{{ $bill_text }}</option>
                @endif
            </select>
        </div>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_custom-{{ $id }}" style="display: none;">
        <label for="graph_custom-{{ $id }}" class="col-sm-4 control-label">@lang('Custom Aggregator(s)')</label>
        <div class="col-sm-8">
            <select class="form-control" id="graph_custom-{{ $id }}" name="graph_custom[]" data-tags="true" multiple="multiple" data-placeholder="@lang('Select or add one or more')">
                @foreach($graph_custom as $custom)
                    <option value="{{ $custom }}" selected>{{ ucwords($custom) }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_ports-{{ $id }}" style="display: none;">
        <label for="graph_ports-{{ $id }}" class="col-sm-4 control-label">@lang('Ports')</label>
        <div class="col-sm-8">
            <select class="form-control" id="graph_ports-{{ $id }}" name="graph_ports[]" multiple="multiple" data-placeholder="@lang('Select one or more')">
                @foreach($graph_ports as $port)
                    <option value="{{ $port->port_id }}" selected>{{ $port->device->shortDisplayName() . ' - ' . $port->getShortLabel() }}</option>
                @endforeach
            </select>
        </div>
    </div>

@endsection

@section('javascript')
    <script type="text/javascript">
        init_select2('#graph_type-{{ $id }}', 'graph', {}, '{{ $graph_type ?: "" }}');
        init_select2('#graph_device-{{ $id }}', 'device', {limit: 100}, {{ $graph_device ?: 0 }});
        init_select2('#graph_port-{{ $id }}', 'port', {limit: 100}, {{ $graph_port ?: 0 }});
        init_select2('#graph_application-{{ $id }}', 'application', function (params) {
            var graph_type = $('#graph_type-{{ $id }}').val().split('_');
            graph_type.shift();
            return {
                type: graph_type.shift(),
                limit: 100,
                term: params.term,
                page: params.page || 1
            };
        }, {{ $graph_application ?: 0 }});
        init_select2('#graph_munin-{{ $id }}', 'munin', {limit: 100}, {{ $graph_munin ?: 0 }});
        init_select2('#graph_bill-{{ $id }}', 'bill', {limit: 100}, {{ $graph_bill ?: 0 }});
        init_select2('#graph_custom-{{ $id }}', 'graph-aggregate', {}, false);
        init_select2('#graph_ports-{{ $id }}', 'port', {limit: 100}, {{ $graph_port_ids }});

        function switch_graph_type{{ $id }}(data) {
            $('.graph_select_extra-{{ $id }}').hide();
            if (data !== undefined && data !== '') {
                var type = data.split('_').shift();
                $('#graph_select_' + type + '-{{ $id }}').show();
            }
        }
    </script>
@endsection
