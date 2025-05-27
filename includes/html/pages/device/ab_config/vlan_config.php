<?php
use Librenms\Config;

$no_refresh = true;
?>
<table id="routes" class="table table-condensed table-hover table-striped">
    <thead>
        <tr>
            <th data-column-id="context_name" data-width="125px" data-formatter="tooltip"> </th>
            <th data-column-id="inetCidrRouteDestType" data-width="70px">VLAN ID</th>
            <th data-column-id="inetCidrRouteDest" data-formatter="tooltip">VLAN Name</th>
            <th data-column-id="inetCidrRoutePfxLen" data-width="80px">Operate</th>
        </tr>
    </thead>
</table>
<div style="padding: 12px;">
● The default VLAN cannot be deleted.
<br>

● Click 'Edit' to browse or reset the VLAN settings.

<br>

● By default, up to 100 VLAN records can be displayed on the web. If you need to query more VLANs, you can enter CMD 'show vlan' on the CMD line.</div>
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
        '</div>');



function updateTable() {
    $('#routes').bootgrid('reload');
};
</script>
