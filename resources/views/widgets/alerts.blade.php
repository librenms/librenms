<div id="alerts_container-{{ $id }}" data-reload="false">
    <div class="table-responsive">
        <table id="alerts-{{ $id }}" class="table table-hover table-condensed alerts">
            <thead>
            <tr>
                <th data-column-id="severity"></th>
                <th data-column-id="timestamp">{{ __('Timestamp') }}</th>
                <th data-column-id="rule">{{ __('Rule') }}</th>
                <th data-column-id="details" data-sortable="false"></th>
                <th data-column-id="hostname">{{ __('Hostname') }}</th>
                <th data-column-id="location" data-visible="{{ $location ? 'true' : 'false' }}">{{ __('Location') }}</th>
                <th data-column-id="ack_ico" data-sortable="false">{{ __('ACK') }}</th>
                <th data-column-id="notes" data-sortable="false">{{ __('Notes') }}</th>
                <th data-column-id="proc" data-sortable="false" data-visible="{{ $proc ? 'true' : 'false' }}">URL</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<script>
    (function () {
        var widgetDefaultMinSeverity = @json($min_severity);
        var severityQuickFilter = null;
        var alerts_grid;

        var $alertsContainer = $('#alerts_container-{{ $id }}');

        function updateSeverityQuickFilterButtons() {
            $alertsContainer.find('.alerts-widget-sev-btn').each(function () {
                var $btn = $(this);
                var sev = $btn.data('targetSev');
                var active = (sev === 'warning' && severityQuickFilter === 5)
                    || (sev === 'critical' && severityQuickFilter === 6);
                $btn.toggleClass('btn-default', !active)
                    .toggleClass('btn-warning', active && sev === 'warning')
                    .toggleClass('btn-danger', active && sev === 'critical')
                    .attr('aria-pressed', active ? 'true' : 'false');
            });
        }

        $alertsContainer.on('click', '.alerts-widget-sev-btn', function () {
            var target = $(this).data('targetSev');
            if (target === 'warning') {
                severityQuickFilter = severityQuickFilter === 5 ? null : 5;
            } else if (target === 'critical') {
                severityQuickFilter = severityQuickFilter === 6 ? null : 6;
            }
            updateSeverityQuickFilterButtons();
            alerts_grid.bootgrid('reload');
        });

        alerts_grid = $("#alerts-{{ $id }}").bootgrid({
            ajax: true,
            requestHandler: request => ({
                ...request,
                id: "alerts",
                acknowledged: '{{ $acknowledged }}',
                unreachable: '{{ $unreachable }}',
                fired: '{{ $fired }}',
                min_severity: severityQuickFilter !== null ? severityQuickFilter : (widgetDefaultMinSeverity ?? ''),
                group: '{{ $device_group }}',
                proc: '{{ $proc }}',
                sort: '{{ $sort }}',
                uncollapse_key_count: '{{ $uncollapse_key_count }}',
                device_id: '{{ $device }}'
            }),
            responseHandler: response => {
                $("#widget_title_counter_{{ $id }}").text(response.total ? ` (${response.total})` : '')

                return response
            },
            url: "ajax_table.php",
            navigation: ! {{ $hidenavigation }},
            rowCount: [50, 100, 250, -1],
            templates: {
                header: "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}}\"><div class=\"row\">" +
                    "<div class=\"col-sm-4 actionBar\">" +
                    "<div class=\"btn-group btn-group-sm\" role=\"group\" aria-label=\"" + @json(__('Alert severity')) + "\">" +
                    "<button type=\"button\" class=\"btn btn-default alerts-widget-sev-btn\" data-target-sev=\"warning\" aria-pressed=\"false\">" + @json(__('Warning')) + "</button>" +
                    "<button type=\"button\" class=\"btn btn-default alerts-widget-sev-btn\" data-target-sev=\"critical\" aria-pressed=\"false\">" + @json(__('Critical')) + "</button>" +
                    "</div></div>" +
                    "<div class=\"col-sm-8 actionBar\"><p class=\"@{{css.search}}\"></p><p class=\"@{{css.actions}}\"></p></div></div></div>"
            }
        }).on("loaded.rs.jquery.bootgrid", function() {
            alerts_grid = $(this);
            updateSeverityQuickFilterButtons();
            alerts_grid.find(".incident-toggle").each( function() {
                $(this).parent().addClass('incident-toggle-td');
            }).on("click", function(e) {
                var target = $(this).data("target");
                $(target).collapse('toggle');
                $(this).toggleClass('fa-plus fa-minus');
            });
            alerts_grid.find(".incident").each( function() {
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
        });

        $('#alerts_container-{{ $id }}').on('refresh', function (event) {
            alerts_grid.bootgrid('reload');
        });
        $('#alerts_container-{{ $id }}').on('destroy', function (event) {
            alerts_grid.bootgrid('destroy');
            delete alerts_grid;
        });
    })();
</script>
