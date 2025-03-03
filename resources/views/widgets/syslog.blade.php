<div id="syslog_container-{{ $id }}" data-reload="false">
    <div class="table-responsive">
        <table id="syslog-{{ $id }}" class="table table-hover table-condensed table-striped">
            <thead>
            <tr>
                <th data-column-id="label"></th>
                <th data-column-id="timestamp" data-order="desc">{{ __('Timestamp') }}</th>
                <th data-column-id="level">{{ __('Level') }}</th>
                <th data-column-id="device_id">{{ __('Hostname') }}</th>
                <th data-column-id="program">{{ __('Program') }}</th>
                <th data-column-id="msg">{{ __('Message') }}</th>
                <th data-column-id="priority">{{ __('Priority') }}</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<script type="application/javascript">
    (function () {
        var grid = $("#syslog-{{ $id }}").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            navigation: ! {{ $hidenavigation }},
            post: function ()
            {
                return {
                    device: '{{ $device ?: '' }}',
                    device_group: '{{ $device_group }}',
                    level: '{{ $level }}'
                };
            },
            url: "{{ url('/ajax/table/syslog') }}"
        });

        $('#syslog_container-{{ $id }}').on('refresh', function (event) {
            grid.bootgrid('reload');
        });
        $('#syslog_container-{{ $id }}').on('destroy', function (event) {
            grid.bootgrid('destroy');
            delete grid;
        });
    })();
</script>
