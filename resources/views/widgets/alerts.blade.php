<div class="table-responsive">
    <table id="alerts_{{ $id }}" class="table table-hover table-condensed alerts">
        <thead>
        <tr>
            <th data-column-id="severity"></th>
            <th data-column-id="timestamp">Timestamp</th>
            <th data-column-id="rule">Rule</th>
            <th data-column-id="details" data-sortable="false"></th>
            <th data-column-id="hostname">Hostname</th>
            <th data-column-id="location" data-visible="{{ $location ? 'true' : 'false' }}">Location</th>
            <th data-column-id="ack_ico" data-sortable="false">ACK</th>
            <th data-column-id="notes" data-sortable="false">Notes</th>
            <th data-column-id="proc" data-sortable="false" data-visible="{{ $proc ? 'true' : 'false' }}">URL</th>
        </tr>
        </thead>
    </table>
</div>
<script>
    var alerts_grid = $("#alerts_{{ $id }}").bootgrid({
        ajax: true,
        post: function ()
        {
            return {
                id: "alerts",
                acknowledged: '{{ $acknowledged }}',
                fired: '{{ $fired }}',
                min_severity: '{{ $min_severity }}',
                group: '{{ $device_group }}',
                proc: '{{ $proc }}',
                sort: '{{ $sort }}',
                device_id: '{{ $device }}'
            }
        },
        url: "ajax_table.php",
        navigation: ! {{ $hidenavigation }},
        rowCount: [50, 100, 250, -1]
    }).on("loaded.rs.jquery.bootgrid", function() {
        alerts_grid = $(this);
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
</script>
