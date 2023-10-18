<div class="table-responsive">
    <table id="graylog-{{ $id }}" class="table table-hover table-condensed graylog" data-ajax="true">
        <thead>
        <tr>
            <th data-column-id="severity" data-sortable="false"></th>
            <th data-column-id="origin">{{ __('Origin') }}</th>
            <th data-column-id="timestamp" data-formatter="browserTime">{{ __('Timestamp') }}</th>
            <th data-column-id="level" data-sortable="false">{{ __('Level') }}</th>
            <th data-column-id="source">{{ __('Source') }}</th>
            <th data-column-id="message" data-sortable="false">{{ __('Message') }}</th>
            <th data-column-id="facility" data-sortable="false">{{ __('Facility') }}</th>
        </tr>
        </thead>
    </table>
</div>

<script>
    $("#graylog-{{ $id }}").bootgrid({
        ajax: true,
        rowCount: ['{{ $limit }}', 25,50,100,250,-1],
        navigation: ! {{ $hidenavigation }},
        formatters: {
            "browserTime": function(column, row) {
                @config('graylog.timezone')
                    return row.timestamp;
                @else
                    return moment.parseZone(row.timestamp).local().format("YYYY-MM-DD HH:MM:SS");
                @endconfig
            }
        },
        post: function ()
        {
            return {
                stream: "{{ $stream }}",
                device: "{{ $device }}",
                range: "{{ $range }}",
                loglevel: "{{ $loglevel }}"
            };
        },
        url: "{{ url('/ajax/table/graylog') }}"
    });
</script>
