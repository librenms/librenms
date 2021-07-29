<?php
use Librenms\Config;

$no_refresh = true;
?>
<table id="routes" class="table table-condensed table-hover table-striped">
    <thead>
        <tr>
            <th data-column-id="context_name" data-width="125px" data-formatter="tooltip">VRF</th>
            <th data-column-id="inetCidrRouteDestType" data-width="70px">Proto</th>
            <th data-column-id="inetCidrRouteDest" data-formatter="tooltip">Destination</th>
            <th data-column-id="inetCidrRoutePfxLen" data-width="80px">Mask</th>
            <th data-column-id="inetCidrRouteNextHop" data-formatter="tooltip">Next hop</th>
            <th data-column-id="inetCidrRouteIfIndex" data-formatter="tooltip">Interface</th>
            <th data-column-id="inetCidrRouteMetric1" data-width="85px">Metric</th>
            <th data-column-id="inetCidrRouteType" data-width="85px">Type</th>
            <th data-column-id="inetCidrRouteProto" data-width="85px">Proto</th>
            <th data-column-id="created_at" data-width="165px" data-formatter="tooltip">First seen</th>
            <th data-column-id="updated_at" data-width="165px" data-formatter="tooltip">Last seen</th>
        </tr>
    </thead>
</table>
<div>Warning: Routing Table is only retrieved during device discovery. Devices are skipped if they have more than <?php echo Config::get('routes_max_number'); ?> routes.</div>
<script>
var grid = $("#routes").bootgrid({
    ajax: true,
    post: function ()
    {
        var check_showAllRoutes = document.getElementById('check_showAllRoutes');
        if (check_showAllRoutes) {
            var showAllRoutes = document.getElementById('check_showAllRoutes').checked;
        } else {
            var showAllRoutes = false;
        }

        var list_showProtocols = document.getElementById('list_showProtocols');
        if (list_showProtocols) {
            var list_showProtocols = document.getElementById('list_showProtocols').value;
        } else {
            var list_showProtocols = 'all';
        }

        return {
            device_id: "<?php echo $device['device_id']; ?>",
            showAllRoutes: showAllRoutes,
            showProtocols: list_showProtocols
        };
    },
    formatters: {
        "tooltip": function (column, row) {
                var value = row[column.id];
                if (value.includes('onmouseover=')) {
                    return value;
                }
                return "<span title=\'" + value + "\' data-toggle=\'tooltip\'>" + value + "</span>";
            },
    },
    url: "ajax/table/routes"
});

var add = $(".actionBar").append(
        '<div class="search form-group pull-left" style="width:auto">' +
        '<?php echo csrf_field() ?>' +
        '<select name="list_showProtocols" id="list_showProtocols" class="input-sm" onChange="updateTable();">' +
        '<option value="all">all Protocols</option>' +
        '<option value="ipv4">IPv4 only</option>' +
        '<option value="ipv6">IPv6 only</option>' +
        '</select>&nbsp;' +
        '<input type="checkbox" name="check_showAllRoutes" data-size="small" id="check_showAllRoutes">' +
        '&nbsp;Include historical routes in the table' +
        '</div>');

$("#check_showAllRoutes").bootstrapSwitch({
    'onSwitchChange': function(event, state){
         updateTable();
    }
});

function updateTable() {
    $('#routes').bootgrid('reload');
};
</script>
