<div id="alertlog_container-{{ $id }}" data-reload="false">
    <div class="table-responsive">
        <table id="alertlog_{{ $id }}" class="table table-hover table-condensed alerts" data-url="{{ route('table.alertlog') }}">
            <thead>
            <tr>
                <th data-column-id="status" data-sortable="false" data-visible-in-selection="false"></th>
                <th data-column-id="time_logged" data-order="desc" data-converter="datetime">{{ __('Timestamp') }}</th>
                <th data-column-id="details" data-sortable="false" data-visible-in-selection="false">&nbsp;</th>
                <th data-column-id="hostname">{{ __('Device') }}</th>
                <th data-column-id="alert_rule">{{ __('Alert') }}</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<script>
    (function () {
        var grid = $("#alertlog_{{ $id }}").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            navigation: ! {{ $hidenavigation }},
            post: function () {
                return {
                    device_group: "{{ $device_group }}",
                    state: '{{ $state }}',
                    severity: @json($severity),
                    from: '{{ $from }}',
                    to: '{{ $to }}',
                };
            },
            converters: {
                datetime: {
                    to: function (value) {
                        return LibreNMS.Date.display(value, {dateStyle: "medium", timeStyle: "short"});
                    }
                }
            }
        }).on("loaded.rs.jquery.bootgrid", function () {
            @include('alerts.partials.init-rows', ['grid' => 'grid', 'expandOnRowClick' => true])
        });

        $('#alertlog_container-{{ $id }}').on('refresh', function (event) {
            grid.bootgrid('reload');
        });
        $('#alertlog_container-{{ $id }}').on('destroy', function (event) {
            grid.bootgrid('destroy');
            delete grid;
        });
    })();
</script>
