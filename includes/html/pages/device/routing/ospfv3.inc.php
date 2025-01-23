<?php

$i = 0;

echo '
<div>
  <div class="panel panel-default">
    <div class="panel-body">
      <table class="table table-condensed" style="border-collapse:collapse;">
        <thead>
          <tr>
            <th>&nbsp;</th>
            <th>Router ID</th>
            <th>Status</th>
            <th>ABR</th>
            <th>ASBR</th>
            <th>Areas</th>
            <th>Ports(Enabled)</th>
            <th>Neighbours</th>
          </tr>
        </thead>';
foreach (dbFetchRows('SELECT * FROM `ospfv3_instances` WHERE `device_id` = ?', [$device['device_id']]) as $instance) {
    $i++;
    $area_count = dbFetchCell('SELECT COUNT(*) FROM `ospfv3_areas` WHERE `device_id` = ?', [$device['device_id']]);
    $port_count = dbFetchCell('SELECT COUNT(*) FROM `ospfv3_ports` WHERE `device_id` = ?', [$device['device_id']]);
    $port_count_enabled = dbFetchCell("SELECT COUNT(*) FROM `ospfv3_ports` WHERE `ospfv3IfAdminStatus` = 'enabled' AND `device_id` = ?", [$device['device_id']]);
    $nbr_count = dbFetchCell('SELECT COUNT(*) FROM `ospfv3_nbrs` WHERE `device_id` = ?', [$device['device_id']]);

    $status_color = $abr_status_color = $asbr_status_color = 'default';

    if ($instance['ospfv3AdminStatus'] == 'enabled') {
        $status_color = 'success';
    }

    if ($instance['ospfv3AreaBdrRtrStatus'] == 'true') {
        $abr_status_color = 'success';
    }

    if ($instance['ospfv3ASBdrRtrStatus'] == 'true') {
        $asbr_status_color = 'success';
    }

    echo '
        <tbody>
          <tr data-toggle="collapse" data-target="#ospf-panel' . $i . '" class="accordion-toggle">
            <td><button id="ospf-panel_button' . $i . '" class="btn btn-default btn-xs"><span id="ospf-panel_span' . $i . '" class="fa fa-plus"></span></button></td>
            <td>' . long2ip($instance['ospfv3RouterId']) . '</td>
            <td><span class="label label-' . $status_color . '">' . $instance['ospfv3AdminStatus'] . '</span></td>
            <td><span class="label label-' . $abr_status_color . '">' . $instance['ospfv3AreaBdrRtrStatus'] . '</span></td>
            <td><span class="label label-' . $asbr_status_color . '">' . $instance['ospfv3ASBdrRtrStatus'] . '</span></td>
            <td>' . $area_count . '</td>
            <td>' . $port_count . '(' . $port_count_enabled . ')</td>
            <td>' . $nbr_count . '</td>
          </tr>
          <script type="text/javascript">
          $("#ospf-panel_button' . $i . '").on("click", function(){
              $("#ospf-panel_span' . $i . '").toggleClass("fa-minus");
          });
          </script>
          <tr>
            <td colspan="12" class="hiddenRow">
            <div class="accordian-body collapse" id="ospf-panel' . $i . '">
                <br>
                <div class="col-xs-4">
                  <div class="table-responsive">
                    <table class="table table-striped table-hover">
                      <thead>
                        <h4><span class="label label-primary">Areas</span></h4>
                        <tr>
                          <th>Area ID</th>
                          <th>Ports(Enabled)</th>
                          <th>Status</th>
                        </tr>
                      </thead>';
    foreach (dbFetchRows('SELECT * FROM `ospfv3_areas` WHERE `device_id` = ?', [$device['device_id']]) as $area) {
        $area_port_count = dbFetchCell('SELECT COUNT(*) FROM `ospfv3_ports` WHERE `device_id` = ? AND `ospfv3IfAreaId` = ?', [$device['device_id'], $area['ospfv3AreaId']]);
        $area_port_count_enabled = dbFetchCell("SELECT COUNT(*) FROM `ospfv3_ports` WHERE `ospfv3IfAdminStatus` = 'enabled' AND `device_id` = ? AND `ospfv3IfAreaId` = ?", [$device['device_id'], $area['ospfv3AreaId']]);

        echo '
                      <tbody>
                        <tr>
                          <td>' . $area['ospfv3AreaId'] . '</td>
                          <td>' . $area_port_count . '(' . $area_port_count_enabled . ')</td>
                          <td><span class="label label-' . $status_color . '">' . $instance['ospfv3AdminStatus'] . '</span></td>
                        </tr>
                      </tbody>';
    }
    echo '
                    </table>
                  </div>
                </div>
                <div class="col-xs-4">
                  <div class="table-responsive">
                    <table class="table table-striped table-hover">
                      <thead>
                        <h4><span class="label label-primary">Ports</span></h4>
                        <tr>
                          <th>Port</th>
                          <th>Port Type</th>
                          <th>Port State</th>
                          <th>Cost</th>
                          <th>Status</th>
                          <th>Area ID</th>
                        </tr>
                      </thead>
                  </div>';
	// P.port_id does not match up with O.port_id, resulting in empty query.
    foreach (dbFetchRows("SELECT * FROM `ospfv3_ports` AS O, `ports` AS P WHERE O.`ospfv3IfAdminStatus` = 'enabled' AND O.`device_id` = ? AND P.port_id = O.port_id ORDER BY O.`ospfv3IfAreaId`", [$device['device_id']]) as $ospfport) {
        $ospfport = cleanPort($ospfport);
        $port_status_color = 'default';

        if ($ospfport['ospfv3IfAdminStatus'] == 'enabled') {
            $port_status_color = 'success';
        }

        echo '
                  <tbody>
                    <tr>
                      <td>' . generate_port_link($ospfport) . '</td>
                      <td>' . $ospfport['ospfv3IfType'] . '</td>
                      <td>' . $ospfport['ospfv3IfState'] . '</td>
                      <td>' . $ospfport['ospfv3IfMetricValue'] . '</td>
                      <td><span class="label label-' . $port_status_color . '">' . $ospfport['ospfv3IfAdminStatus'] . '</span></td>
                      <td>' . $ospfport['ospfv3IfAreaId'] . '</td>
                    </tr>
                  </tbody>';
    }
    echo '
                  </table>
                </div>
                </div>
                <div class="col-xs-4">
                  <div class="table-responsive">
                    <table class="table table-striped table-hover">
                      <thead>
                        <h4><span class="label label-primary">Neighbours</span></h4>
                        <tr>
                          <th>Router ID</th>
                          <th>Device</th>
                          <th>IP Address</th>
                          <th>Status</th>
                        </tr>
                      </thead>';
    foreach (dbFetchRows('SELECT * FROM `ospfv3_nbrs` WHERE `device_id` = ?', [$device['device_id']]) as $nbr) {
        $host = @dbFetchRow('SELECT * FROM `ipv6_addresses` AS A, `ports` AS I, `devices` AS D WHERE A.ipv6_address = ? AND I.port_id = A.port_id AND D.device_id = I.device_id', [$nbr['ospfv3NbrRtrId']]);

        $rtr_id = 'unknown';
        $ospfnbr_status_color = 'default';

        if (is_array($host)) {
            $rtr_id = generate_device_link($host);
        }

        if ($nbr['ospfv3NbrState'] == 'full') {
            $ospfnbr_status_color = 'success';
        } elseif ($nbr['ospfv3NbrState'] == 'down') {
            $ospfnbr_status_color = 'danger';
        }

        echo '
                    <tbody>
                      <tr>
                        <td>' . long2ip($nbr['ospfv3NbrRtrId']) . '</td>
                        <td>' . $rtr_id . '</td>
                        <td>' . $nbr['ospfv3NbrAddress'] . '</td>
                        <td><span class="label label-' . $ospfnbr_status_color . '">' . $nbr['ospfv3NbrState'] . '</span></td>
                      </tr>
                    </tbody>';
    }
    echo '
                    </table>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        </tbody>';
}
echo '
      </table>
    </div>
  </div>
</div>';
