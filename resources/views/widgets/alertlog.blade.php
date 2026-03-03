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
                };
            },
            converters: {
                datetime: {
                    to: function (value) {
                        return LibreNMS.Time.format(value, {timeStyle: "short"});
                    }
                }
            }
        }).on("loaded.rs.jquery.bootgrid", function () {
            grid.find(".incident-toggle").each(function () {
                $(this).parent().addClass('incident-toggle-td');
            }).on("click", function (e) {
                var target = $(this).data("target");
                $(target).collapse('toggle');
                $(this).toggleClass('fa-plus fa-minus');
            });
            grid.find(".incident").each(function () {
                $(this).parent().addClass('col-lg-4 col-md-4 col-sm-4 col-xs-4');
                $(this).parent().parent().on("mouseenter", function () {
                    $(this).find(".incident-toggle").fadeIn(200);
                }).on("mouseleave", function () {
                    $(this).find(".incident-toggle").fadeOut(200);
                }).on("click", "td:not(.incident-toggle-td)", function () {
                    var target = $(this).parent().find(".incident-toggle").data("target");
                    if ($(this).parent().find(".incident-toggle").hasClass('fa-plus')) {
                        $(this).parent().find(".incident-toggle").toggleClass('fa-plus fa-minus');
                        $(target).collapse('toggle');
                    }
                });
            });
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
