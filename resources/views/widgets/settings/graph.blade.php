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
            <select class="form-control" id="graph_type-{{ $id }}" name="graph_type" required>
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
    <div class="form-group">
        <label for="graph_port-{{ $id }}" class="col-sm-4 control-label">@lang('Port')</label>
        <div class="col-sm-8">
            <select class="form-control" id="graph_port-{{ $id }}" name="graph_port" required>
                @if($graph_port)
                    <option value="{{ $graph_port }}">{{ 'FIXME' }}</option>
                @endif
            </select>
        </div>
    </div>


@endsection

@section('javascript')
    <script type="text/javascript">
        init_select2('#graph_type-{{ $id }}', 'graph', 'Select a graph', {}, '{{ $graph_type ?: "" }}');
        init_select2('#graph_port-{{ $id }}', 'port', 'Select a port', {}, {{ $graph_port ?: 0 }});
    </script>
@endsection
