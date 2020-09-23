@extends('poller.index')

@section('title', __('Poller Log'))

@section('content')

@parent

<table id="poll-log" class="table table-condensed table-hover table-striped">
    <thead>
    <tr>
        <th data-column-id="hostname">@lang('Hostname')</th>
        <th data-column-id="last_polled">@lang('Last Polled')</th>
        <th data-column-id="poller_group">@lang('Poller Group')</th>
        <th data-column-id="location">@lang('Location')</th>
        <th data-column-id="last_polled_timetaken" data-order="desc">@lang('Polling Duration') (@lang('Seconds'))</th>
    </tr>
    </thead>
</table>
@endsection

@section('scripts')
<script>
    searchbar = "<div id=\"\{\{ctx.id\}\}\" class=\"\{\{css.header\}\}\"><div class=\"row\">"+
        "<div class=\"col-sm-8 actionBar\"><span class=\"pull-left\">"+
        "<a href='{{ route('poller.log') }}' class='btn btn-primary btn-sm @if($filter == 'unpolled') 'active' @endif'>All devices</a> "+
        "<a href='{{ route('poller.log') }}?filter=unpolled' class='btn btn-danger btn-sm @if($filter == 'unpolled') 'active' @endif'>Unpolled devices</a>"+
        "</div><div class=\"col-sm-4 actionBar\"><p class=\"\{\{css.search\}\}\"></p><p class=\"\{\{css.actions\}\}\"></p></div>";

    var grid = $("#poll-log").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        columnSelection: false,
        templates: {
            header: searchbar
        },
        post: function ()
        {
            return {
                id: "poll-log",
                type: "{{ $filter }}"
            };
        },
        url: "ajax_table.php"
    });

</script>
@endsection
