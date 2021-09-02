<div class="table-responsive">
    <table id="alertlog_{{ $id }}" class="table table-hover table-condensed alerts">
        <thead>
        <tr>
            <th data-column-id="status" data-sortable="false"></th>
            <th data-column-id="time_logged" data-order="desc">Timestamp</th>
            <th data-column-id="details" data-sortable="false">&nbsp;</th>
            <th data-column-id="hostname">Device</th>
            <th data-column-id="alert">Alert</th>
        </tr>
        </thead>
    </table>
</div>
<script>
    var grid = $("#alertlog_{{ $id }}").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        navigation: ! {{ $hidenavigation }},
        post: function () {
            return {
                id: "alertlog",
                device_id: "",
                state: '{{ $state }}',
                min_severity: '{{ $min_severity }}',
            };
        },
        url: "ajax_table.php"
    }).on("loaded.rs.jquery.bootgrid", function () {

        var results = $("div.infos").text().split(" ");
        low = results[1] - 1;
        high = results[3];
        max = high - low;
        search = $('.search-field').val();

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
</script>
