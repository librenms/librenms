<?php

$no_refresh = true;

$link_array = array(
               'page'   => 'device',
               'device' => $device['device_id'],
               'tab'    => 'nac',
              );
$pagetitle[] = 'NAC';
echo"<br>";

echo "<table id='grid' data-toggle='bootgrid' class='table table-condensed table-responsive table-striped'>
    <thead>
        <tr>
            <th data-column-id='nac_port'>Port</th>
            <th data-column-id='nac_mac'>MAC Address</th>
            <th data-column-id='nac_ip'>IP Address</th>
            <th data-column-id='nac_authz' data-searchable='false' data-formatter='nac_authz'>AuthZ</th>
            <th data-column-id='nac_domain' data-searchable='false' data-formatter='nac_domain'>Domain</th>
            <th data-column-id='nac_mode' data-searchable='false' data-formatter='nac_mode'>Mode</th>
            <th data-column-id='nac_username'>Username</th>
            <th data-column-id='nac_timeout' data-searchable='false'>Time Out</th>
            <th data-column-id='nac_timeleft' data-searchable='false'>Time Left</th>
            <th data-column-id='nac_authc' data-searchable='false' data-formatter='nac_authc'>AuthC</th>
            <th data-column-id='nac_method' data-formatter='nac_method'>Method</th>
        </tr>
    </thead>";

foreach (\App\Models\PortsNac::where('device_id', $device['device_id'])->with('port')->get() as $nac) {
    echo '<td>' . generate_port_link($nac->port->toArray(), $nac->port->getLabel()) . '</td>';
    echo '<td>' . strtoupper($nac['PortAuthSessionMacAddress']) . '</td>';
    echo '<td>' . $nac['PortAuthSessionIPAddress'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionAuthzStatus'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionDomain'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionHostMode'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionUserName'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionTimeOut'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionTimeLeft'] . '</td>';
    echo '<td>' . $nac['PortAuthSessionAuthcStatus'] . '</td>';
    echo '<td>' . $nac['PortSessionMethod'] . '</td>';
    echo '</tr>';
}
echo '</table>';
?>
<script type="text/javascript">
    $("#grid").bootgrid({
        caseSensitive: false,
        rowCount: [50, 100, 250, -1],
        formatters:{
            "nac_authz": function (column, row){
                if (row.nac_authz == 'authorizationSuccess'){
                    return "<i class=\"fa fa-check-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:green;\"></i>";
                    }
                if (row.nac_authz == 'authorizationFailed'){
                    return "<i class=\"fa fa-times-circle fa-lg icon-theme\" aria-hidden=\"true\" style=\"color:red;\"></i>";
                    }
                else{
                    return "<span class=\'label label-default\'>" + row.nac_authz + "</span>";
                    }
                },
            "nac_domain": function (column, row){
                if (row.nac_domain == 'voice'){
                    return "<i class=\"fa fa-phone fa-lg icon-theme\"  aria-hidden=\"true\"></i>";
                    }
                if (row.nac_domain == 'data'){
                    return "<i class=\"fa fa-desktop fa-lg icon-theme\"  aria-hidden=\"true\"></i>";
                    }
                if (row.nac_domain == 'Disabled'){
                    return "<i class=\"fa fa-desktop fa-lg icon-theme\"  aria-hidden=\"true\"></i>";
                    }
                else{
                    return "<span class=\'label label-default\'>" + row.nac_domain + "</span>";
                    }
                },
            "nac_authc": function (column, row){
                if (row.nac_authc == 'notRun'){
                    return "<span class=\"label label-primary\">notRun</span>";
                    }
                if (row.nac_authc == 'running'){
                    return "<span class=\"label label-primary\">running</span>";
                    }
                if (row.nac_authc == 'failedOver'){
                    return "<i class=\"fa fa-times-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:red;\"></i>";
                    }
                if (row.nac_authc == 'authcSuccess'){
                    return "<i class=\"fa fa-check-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:green;\">";
                    }
                if (row.nac_authc == 'authcFailed'){
                    return "<i class=\"fa fa-times-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:red;\"></i>";
                    }
                if (row.nac_authc == '6'){
                    return "<i class=\"fa fa-times-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:red;\"></i>";
                    }
                else{
                    return "<span class=\'label label-default\'>" + row.nac_authc + "</span>";
                    }
                },
            "nac_method": function (column, row){
                if (row.nac_method == 'dot1x'){
                    return "<span class=\"label label-success\">802.1x</span>";
                    }
                if (row.nac_method == 'macAuthBypass'){
                    return "<span class=\"label label-primary\">MAB</span>";
                    }
                if (row.nac_method == 'other'){
                    return "<span class=\"label label-danger\">Disabled</span>";
                    }
                else{
                    return "<span class=\'label label-default\'>" + row.nac_method + "</span>";
                    }
                },
        }
    });
</script>
