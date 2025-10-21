<div id="alertlog_container-{{ $id }}" data-reload="false">
    <div class="table-responsive">
        <table id="alertlog_{{ $id }}" class="table table-hover table-condensed alerts">
            <thead>
            <tr>
                <th data-column-id="status" data-sortable="false"></th>
                <th data-column-id="time_logged" data-order="desc">{{ __('Timestamp') }}</th>
                <th data-column-id="details" data-sortable="false">&nbsp;</th>
                <th data-column-id="hostname">{{ __('Device') }}</th>
                <th data-column-id="alert">{{ __('Alert') }}</th>
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
                    id: "alertlog",
                    device_id: "",
                    device_group: "{{ $device_group }}",
                    state: '{{ $state }}',
                    min_severity: '{{ $min_severity }}',
                };
            },
            url: "ajax_table.php"
        }).on("loaded.rs.jquery.bootgrid", function () {
            grid.find(".incident-toggleInput").each(function () {
                $(this).parent().addClass('incident-toggleInput-td');
            }).on("click", function (e) {
                var target = $(this).data("target");
                $(target).collapse('toggle');
                $(this).toggleClass('fa-plus fa-minus');
            });
            grid.find(".incident").each(function () {
                $(this).parent().addClass('col-lg-4 col-md-4 col-sm-4 col-xs-4');
                $(this).parent().parent().on("mouseenter", function () {
                    $(this).find(".incident-toggleInput").fadeIn(200);
                }).on("mouseleave", function () {
                    $(this).find(".incident-toggleInput").fadeOut(200);
                }).on("click", "td:not(.incident-toggleInput-td)", function () {
                    var target = $(this).parent().find(".incident-toggleInput").data("target");
                    if ($(this).parent().find(".incident-toggleInput").hasClass('fa-plus')) {
                        $(this).parent().find(".incident-toggleInput").toggleClass('fa-plus fa-minus');
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
