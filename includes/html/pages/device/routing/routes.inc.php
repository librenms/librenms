<?php
use Librenms\Config;

$no_refresh = true;
?>
<table id="routes" class="table table-condensed table-hover table-striped">
    <thead>
        <tr>
            <th data-column-id="context_name" data-width="125px">VRF</th>
            <th data-column-id="inetCidrRouteDestType" data-width="70px">Proto</th>
            <th data-column-id="inetCidrRouteDest">Destination</th>
            <th data-column-id="inetCidrRoutePfxLen" data-width="80px">Mask</th>
            <th data-column-id="inetCidrRouteNextHop">Next hop</th>
            <th data-column-id="inetCidrRouteIfIndex">Interface</th>
            <th data-column-id="inetCidrRouteMetric1" data-width="85px">Metric</th>
            <th data-column-id="inetCidrRouteType" data-width="85px">Type</th>
            <th data-column-id="inetCidrRouteProto" data-width="85px">Proto</th>
            <th data-column-id="created_at" data-width="165px">First seen</th>
            <th data-column-id="updated_at" data-width="165px">Last seen</th>
        </tr>
    </thead>
</table>
<div>Warning: Routing Table is only retrieved during device discovery. Devices are skipped if they have more than <?php echo Config::get('routes.max_number');?> routes.</div>
<script>

var grid = $("#routes").bootgrid({
    ajax: true,
    post: function ()
    {
        return {
            device_id: "<?php echo $device['device_id']; ?>",
        };
    },
    url: "ajax/table/routes"
});

</script>
