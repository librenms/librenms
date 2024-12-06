<?php

$pagetitle[] = 'Customers';
$no_refresh = true;

?>

<div class="table-responsive">
    <table id="customers" class="table table-hover table-condensed">
        <thead>
            <tr>
                <th data-column-id="port_descr_descr" data-order="asc" data-formatter="customer">Customer</th>
                <th data-column-id="hostname">Device</th>
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
        rowCount: [50, 100, 250, -1],
        url: "<?php echo url('/ajax/table/customers'); ?>",
        formatters: {
            customer: function (column, row) {
                return '<strong>' + row[column.id] + '</strong>';
            }
        }
    });
</script>
