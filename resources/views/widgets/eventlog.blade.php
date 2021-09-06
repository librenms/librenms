<div class="table-responsive">
    <table id="eventlog" class="table table-hover table-condensed table-striped" data-ajax="true">
        <thead>
        <tr>
            <th data-column-id="datetime" data-order="desc">Timestamp</th>
            <th data-column-id="type">Type</th>
            <th data-column-id="device_id">Hostname</th>
            <th data-column-id="message">Message</th>
            <th data-column-id="username">User</th>
        </tr>
        </thead>
    </table>
</div>
<script>
    $("#eventlog").bootgrid({
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
</script>
