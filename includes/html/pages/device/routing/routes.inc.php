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
<div>Warning: Routing Table is only retrieved during device discovery. Devices are skipped if they have more than <?php echo Config::get('routes_max_number');?> routes.</div>
<script>
var grid = $("#routes").bootgrid({
    ajax: true,
    post: function ()
    {
        var check_showAllRoutes = document.getElementById('check_showAllRoutes');
        var check_showIPv4 = document.getElementById('check_showIPv4');
        var check_showIPv6 = document.getElementById('check_showIPv6');
        if (check_showAllRoutes) {
            var showAllRoutes = document.getElementById('check_showAllRoutes').checked;
        } else {
            var showAllRoutes = false;
        }
        if (check_showIPv4) {
            var showIPv4 = document.getElementById('check_showIPv4').checked;
        } else {
            var showIPv4 = false;
        }
        if (check_showIPv6) {
            var showIPv6 = document.getElementById('check_showIPv6').checked;
        } else {
            var showIPv6 = false;
        }

        return {
            device_id: "<?php echo $device['device_id']; ?>",
            showAllRoutes: showAllRoutes,
            showIPv4: showIPv4,
            showIPv6: showIPv6,
        };
    },
    url: "ajax/table/routes"
});

var add = $(".actionBar").append(
        '<div class="search form-group pull-left" style="width:auto">' +
        '<?php echo csrf_field() ?>'+
        '<input type="checkbox" name="check_showAllRoutes" data-size="small" id="check_showAllRoutes">' +
        '&nbsp;Include historical routes in the table&nbsp;' +
        '<input type="checkbox" name="check_showIPv4" data-size="small" id="check_showIPv4">' +
        '&nbsp;Show IPv4 Routes&nbsp;' +
        '<input type="checkbox" name="check_showIPv6" data-size="small" id="check_showIPv6">' +
        '&nbsp;Show IPv6 Routes&nbsp;' +
        '</div>');
$("[type='checkbox']").bootstrapSwitch({
    'onSwitchChange': function(event, state){
         $('#routes').bootgrid('reload');
    }
});

$("#check_showIPv4").bootstrapSwitch('state', true);
$("#check_showIPv6").bootstrapSwitch('state', true);
</script>
