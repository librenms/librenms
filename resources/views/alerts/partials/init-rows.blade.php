{{--
    Row behaviour shared by the alert bootgrid tables, include in the grid's loaded.rs.jquery.bootgrid handler.
    grid: js variable holding the grid, expandOnRowClick: clicking a collapsed row expands its incident details
--}}
(function (grid) {
    function toggleIncident($toggle) {
        $($toggle.data("target")).collapse('toggle');
        $toggle.toggleClass('fa-plus fa-minus');
    }

    grid.find(".incident-toggle").each(function () {
        $(this).parent().addClass('incident-toggle-td');
    }).on("click", function () {
        toggleIncident($(this));
    });

    grid.find(".incident").each(function () {
        var $row = $(this).parent().parent();
        $(this).parent().addClass('col-lg-4 col-md-4 col-sm-4 col-xs-4');

        if ($row.find(".alert-status").hasClass('label-danger')) {
            $row.find(".verbose-alert-details").fadeIn(0);
        }

        $row.on("mouseenter", function () {
            $row.find(".incident-toggle").fadeIn(200);
        }).on("mouseleave", function () {
            $row.find(".incident-toggle").fadeOut(200);
        });
        @if($expandOnRowClick ?? false)

        $row.on("click", "td:not(.incident-toggle-td)", function () {
            var $toggle = $row.find(".incident-toggle");
            if ($toggle.hasClass('fa-plus')) {
                toggleIncident($toggle);
            }
        });
        @endif
    });

    grid.find(".command-ack-alert").on("click", function (e) {
        e.preventDefault();
        $('#ack_alert_id').val($(this).data('alert_id'));
        $('#ack_alert_state').val($(this).data('alert_state'));
        $('#ack_msg').val('');
        $("#alert_ack_modal").modal('show');
    });

    grid.find(".command-alert-note").on("click", function (e) {
        e.preventDefault();
        $('#alert_id').val($(this).data('alert_id'));
        $("#alert_notes_modal").modal('show');
    });

    grid.find(".command-alert-details, .verbose-alert-details").on("click", function (e) {
        e.preventDefault();
        e.stopPropagation(); // don't also expand the row
        $('#alert_log_id').val($(this).data('alert_log_id'));
        $("#alert_details_modal").modal('show');
    });
})({{ $grid }});
