<div id="eventlog_container-{{ $id }}" data-reload="false">
    <div class="table-responsive">
        <table id="eventlog-{{ $id }}" class="table table-hover table-condensed table-striped">
            <thead>
            <tr>
                <th data-column-id="datetime" data-order="desc">{{ __('Timestamp') }}</th>
                <th data-column-id="type">{{ __('Type') }}</th>
                <th data-column-id="device_id">{{ __('Hostname') }}</th>
                <th data-column-id="message">{{ __('Message') }}</th>
                <th data-column-id="username">{{ __('User') }}</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<script>
    $(function () {
        var grid = $("#eventlog-{{ $id }}").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            navigation: ! {{ $hidenavigation }},
            post: function ()
            {
                return {
                    device: "{{ $device }}",
                    device_group: "{{ $device_group }}",
                    eventtype: "{{ $eventtype }}"
                };
            },
            url: "{{ url('/ajax/table/eventlog') }}"
        });

        $('#eventlog_container-{{ $id }}').on('refresh', function (event) {
            grid.bootgrid('reload');
        });
        $('#eventlog_container-{{ $id }}').on('destroy', function (event) {
            grid.bootgrid('destroy');
            delete grid;
        });
    });
</script>
