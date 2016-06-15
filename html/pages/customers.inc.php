<?php

$pagetitle[] = 'Customers';

?>

<div class="table-responsive">
    <table id="customers" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="port_descr_descr" data-order="asc">Customer</th>
                <th data-column-id="device_id">Device</th>
                <th data-column-id="ifDescr">Interface</th>
                <th data-column-id="port_descr_speed">Speed</th>
                <th data-column-id="port_descr_circuit">Circuit</th>
                <th data-column-id="port_descr_notes">Notes</th>
            </tr>
        </thead>
    </table>
</div>

<script>

    var grid = $("#customers").bootgrid({
        ajax: true,
        post: function ()
        {
            return {
                id: "customers",
            };
        },
        url: "ajax_table.php"
    });
</script>
