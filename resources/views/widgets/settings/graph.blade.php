@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">@lang('Widget title')</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Automatic Title')" value="{{ $title }}">
    </div>
    <div class="form-group">
        <label for="graph_type-{{ $id }}" class="control-label">@lang('Graph type')</label>
        <select class="form-control" id="graph_type-{{ $id }}" name="graph_type" required data-placeholder="@lang('Select a graph')" onchange="switch_graph_type{{ $id }}(this.value);">
            @if($graph_type)
                <option value="{{ $graph_type }}">{{ $graph_text }}</option>
            @endif
        </select>
    </div>
    <div class="form-group">
        <label for="graph_legend-{{ $id }}" class="control-label">@lang('Show legend')</label>
        <select class="form-control" id="graph_legend-{{ $id }}" name="graph_legend">
            <option value="yes" @if($graph_legend == 'yes') selected @endif>@lang('yes')</option>
            <option value="no" @if($graph_legend == 'no') selected @endif>@lang('no')</option>
        </select>
    </div>
    <div class="form-group">
        <label for="graph_range-{{ $id }}" class="control-label">@lang('Date range')</label>
        <select class="form-control" id="graph_range-{{ $id }}" name="graph_range">
            <option value="onehour" @if($graph_range == 'onehour') selected @endif>@lang('One Hour')</option>
            <option value="fourhour" @if($graph_range == 'fourhour') selected @endif>@lang('Four Hours')</option>
            <option value="sixhour" @if($graph_range == 'sixhour') selected @endif>@lang('Six Hours')</option>
            <option value="twelvehour" @if($graph_range == 'twelvehour') selected @endif>@lang('Twelve Hours')</option>
            <option value="day" @if($graph_range == 'day') selected @endif>@lang('One Day')</option>
            <option value="twoday" @if($graph_range == 'twoday') selected @endif>@lang('Two Days')</option>
            <option value="week" @if($graph_range == 'week') selected @endif>@lang('One Week')</option>
            <option value="twoweek" @if($graph_range == 'twoweek') selected @endif>@lang('Two Weeks')</option>
            <option value="month" @if($graph_range == 'month') selected @endif>@lang('One Month')</option>
            <option value="twomonth" @if($graph_range == 'twomonth') selected @endif>@lang('Two Months')</option>
            <option value="threemonth" @if($graph_range == 'threemonth') selected @endif>@lang('Three Months')</option>
            <option value="year" @if($graph_range == 'year') selected @endif>@lang('One Year')</option>
            <option value="twoyear" @if($graph_range == 'twoyear') selected @endif>@lang('Two Years')</option>
        </select>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_device-{{ $id }}" style="display: none;">
        <label for="graph_device-{{ $id }}" class="control-label">@lang('Device')</label>
        <select class="form-control" id="graph_device-{{ $id }}" name="graph_device" data-placeholder="@lang('Select a device')">
            @if($graph_device)
                <option value="{{ $graph_device }}">{{ $device_text }}</option>
            @endif
        </select>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_port-{{ $id }}" style="display: none;">
        <label for="graph_port-{{ $id }}" class="control-label">@lang('Port')</label>
        <select class="form-control" id="graph_port-{{ $id }}" name="graph_port" data-placeholder="@lang('Select a port')">
        @if($graph_port)
            <option value="{{ $graph_port }}">{{ $port_text }}</option>
        @endif
        </select>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_application-{{ $id }}" style="display: none;">
        <label for="graph_application-{{ $id }}" class="control-label">@lang('Application')</label>
        <select class="form-control" id="graph_application-{{ $id }}" name="graph_application" data-placeholder="@lang('Select an application')">
        @if($graph_application)
            <option value="{{ $graph_application }}">{{ $application_text }}</option>
        @endif
        </select>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_munin-{{ $id }}" style="display: none;">
        <label for="graph_munin-{{ $id }}" class="control-label">@lang('Munin plugin')</label>
        <select class="form-control" id="graph_munin-{{ $id }}" name="graph_munin" data-placeholder="@lang('Select a Munin plugin')">
        @if($graph_munin)
            <option value="{{ $graph_munin }}">{{ $munin_text }}</option>
        @endif
        </select>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_service-{{ $id }}" style="display: none;">
        <label for="graph_service-{{ $id }}" class="control-label">@lang('Service')</label>
        <select class="form-control" id="graph_service-{{ $id }}" name="graph_service" data-placeholder="@lang('Select a service')">
        @if($graph_service)
            <option value="{{ $graph_service }}">{{ $service_text }}</option>
        @endif
        </select>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_bill-{{ $id }}" style="display: none;">
        <label for="graph_bill-{{ $id }}" class="control-label">@lang('Bill')</label>
        <select class="form-control" id="graph_bill-{{ $id }}" name="graph_bill" data-placeholder="@lang('Select a bill')">
        @if($graph_bill)
            <option value="{{ $graph_bill }}">{{ $bill_text }}</option>
        @endif
        </select>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_custom-{{ $id }}" style="display: none;">
        <label for="graph_custom-{{ $id }}" class="control-label">@lang('Custom Aggregator(s)')</label>
        <select class="form-control" id="graph_custom-{{ $id }}" name="graph_custom[]" data-tags="true" multiple="multiple" data-placeholder="@lang('Select or add one or more')">
            @foreach($graph_custom as $custom)
                <option value="{{ $custom }}" selected>{{ ucwords($custom) }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group graph_select_extra-{{ $id }}" id="graph_select_ports-{{ $id }}" style="display: none;">
        <label for="graph_ports-{{ $id }}" class="control-label">@lang('Ports')</label>
        <select class="form-control" id="graph_ports-{{ $id }}" name="graph_ports[]" multiple="multiple" data-placeholder="@lang('Select one or more')">
            @foreach($graph_ports as $port)
                <option value="{{ $port->port_id }}" selected>{{ $port->device->shortDisplayName() . ' - ' . $port->getShortLabel() }}</option>
            @endforeach
        </select>
    </div>
@endsection

@section('javascript')
    <script>
        init_select2('#graph_type-{{ $id }}', 'graph', {}, '{{ $graph_type ?: '' }}');
        init_select2('#graph_device-{{ $id }}', 'device', {limit: 100}, '{{ $graph_device ?: '' }}');
        init_select2('#graph_port-{{ $id }}', 'port', {limit: 100}, '{{ $graph_port ?: '' }}');
        init_select2('#graph_application-{{ $id }}', 'application', function (params) {
            var graph_type = $('#graph_type-{{ $id }}').val().split('_');
            graph_type.shift();
            return {
                type: graph_type.shift(),
                limit: 100,
                term: params.term,
                page: params.page || 1
            };
        }, '{{ $graph_application ?: '' }}');
        init_select2('#graph_munin-{{ $id }}', 'munin', {limit: 100}, '{{ $graph_munin ?: '' }}');
        init_select2('#graph_service-{{ $id }}', 'service', {limit: 100}, '{{ $graph_service ?: '' }}');
        init_select2('#graph_bill-{{ $id }}', 'bill', {limit: 100}, '{{ $graph_bill ?: '' }}');
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
