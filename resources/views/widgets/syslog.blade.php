<div class="table-responsive">
    <table id="syslog" class="table table-hover table-condensed table-striped">
        <thead>
        <tr>
            <th data-column-id="label"></th>
            <th data-column-id="timestamp" data-order="desc">@lang('Timestamp')</th>
            <th data-column-id="level">@lang('Level')</th>
            <th data-column-id="device_id">@lang('Hostname')</th>
            <th data-column-id="program">@lang('Program')</th>
            <th data-column-id="msg">@lang('Message')</th>
            <th data-column-id="priority">@lang('Priority')</th>
        </tr>
        </thead>
    </table>
</div>
<script type="application/javascript">
    $("#syslog").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function ()
        {
            return {
                device: '{{ $device ?: '' }}',
                device_group: '{{ $device_group }}'
            };
        },
        url: "{{ url('/ajax/table/syslog') }}"
    });
</script>
