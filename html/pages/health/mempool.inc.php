<div class="table-responsive">
    <table id="mempool" class="table table-hover table-condensed mempool">
        <thead>
            <tr>
                <th data-column-id="hostname">Device</th>
                <th data-column-id="mempool_descr">Memory</th>
                <th data-column-id="graph" data-sortable="false" data-searchable="false"></th>
                <th data-column-id="mempool_used" data-searchable="false">Used</th>
                <th data-column-id="mempool_perc" data-searchable="false">Usage</th>
            </tr>
        </thead>
    </table>
</div>

<script>
    $('#mempool').DataTable( {
        "lengthMenu": [[50, 100, 250, -1], [50, 100, 250, "All"]],
        "serverSide": true,
        "processing": true,
        "scrollX": false,
        "sScrollX": "100%",
        "sScrollXInner": "100%",
        "dom":  "ltip",
        "ajax": {
            "url": "ajax_table.php",
            "type": "POST",
            "data": {
                "id": "mempool",
                "view": '<?php echo $vars['view']; ?>'
            }
        },
        columns: [
            { "data": "hostname" },
            { "data": "mempool_descr" },
            { "data": "graph" },
            { "data": "mempool_used" },
            { "data": "mempool_perc" },
        ],
        "order": [[0, "asc"]]
    } );

    /*
var grid = $("#mempool").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function ()
        {
            return {
                id: "mempool",
                view: '<?php echo $vars['view']; ?>'
            };
        },
        url: "ajax_table.php",
        formatters: {
            "status": function(column,row) {
                return "<h4><span class='label label-"+row.extra+" threeqtr-width'>" + row.msg + "</span></h4>";
            },
            "ack": function(column,row) {
                return "<button type='button' class='btn btn-"+row.ack_col+" btn-sm command-ack-alert' data-target='#ack-alert' data-state='"+row.state+"' data-alert_id='"+row.alert_id+"' name='ack-alert' id='ack-alert' data-extra='"+row.extra+"'><i class='fa fa-"+row.ack_ico+"'aria-hidden='true'></i></button>";
            }
        },
        templates: {
        }
    });
     */
</script>
