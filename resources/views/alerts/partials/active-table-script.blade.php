<script type="text/javascript">
    @isset($filterName)
    var alerts_filter = @js($filter ?? []);
    @endisset

    var alerts_grid = $("#alerts_{{ $unique_id }}").bootgrid({
        ajax: true,
        @isset($filterName)
        templates: {
            header: "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}}\"><div class=\"alerts-actionbar tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2\"><p class=\"@{{css.actions}}\"></p></div></div>",
            search: "" // replaced by the filter bar
        },
        @endisset
        post: function() {
            return {
                @isset($filterName)
                filter: alerts_filter,
                @endisset
                rule_id: '{{ $rule_id }}',
                alert_id: '{{ $alert_id }}',
                acknowledged: '{{ $acknowledged }}',
                fired: '{{ $fired }}',
                state: '{{ $state }}',
                min_severity: '{{ $min_severity }}',
                group: '{{ $group }}',
                proc: '{{ $proc }}',
                device_id: '{{ $device_id }}',
            };
        },
        url: "{{ route('table.alerts') }}",
        sort: {
            @if($sort === 'severity' || $sort == 1)
            severity: 'desc',
            @else
            timestamp: 'desc',
            @endif
        },
        rowCount: [50, 100, 250, -1],
    }).on("loaded.rs.jquery.bootgrid", function() {
        alerts_grid = $(this);
        alerts_grid.find(".incident-toggle").each(function() {
            $(this).parent().addClass('incident-toggle-td');
        }).on("click", function() {
            var target = $(this).data("target");
            $(target).collapse('toggle');
            $(this).toggleClass('fa-plus fa-minus');
        });
        alerts_grid.find(".incident").each(function() {
            $(this).parent().addClass('col-lg-4 col-md-4 col-sm-4 col-xs-4');
            $(this).parent().parent().on("mouseenter", function() {
                $(this).find(".incident-toggle").fadeIn(200);
            }).on("mouseleave", function() {
                $(this).find(".incident-toggle").fadeOut(200);
            });
        });
        alerts_grid.find(".command-ack-alert").on("click", function(e) {
            e.preventDefault();
            var alert_state = $(this).data("alert_state");
            var alert_id = $(this).data('alert_id');
            $('#ack_alert_id').val(alert_id);
            $('#ack_alert_state').val(alert_state);
            $('#ack_msg').val('');
            $("#alert_ack_modal").modal('show');
        });
        alerts_grid.find(".command-alert-note").on("click", function(e) {
            e.preventDefault();
            var alert_id = $(this).data('alert_id');
            $('#alert_id').val(alert_id);
            $("#alert_notes_modal").modal('show');
        });
        alerts_grid.find(".command-alert-details").on("click", function(e) {
            e.preventDefault();
            var alert_log_id = $(this).data('alert_log_id');
            $('#alert_log_id').val(alert_log_id);
            $("#alert_details_modal").modal('show');
        });
    });

    @isset($filterName)
    const $alertsFilterTemplate = $('#alerts-filter-template');
    if ($alertsFilterTemplate.length) {
        $(".alerts-actionbar").prepend($($alertsFilterTemplate[0].content.cloneNode(true)));
    }

    $(window).on('filter:apply', function (event) {
        if (event.originalEvent.detail.name === @js($filterName)) {
            alerts_filter = event.originalEvent.detail.filters;
            alerts_grid.bootgrid('reload');
        }
    });
    @endisset
</script>
