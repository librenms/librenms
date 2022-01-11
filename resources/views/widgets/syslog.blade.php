<div class="table-responsive">
    <table id="syslog" class="table table-hover table-condensed table-striped" data-ajax="true">
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
<script type="application/javascript">
    $("#syslog").bootgrid({
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
</script>
